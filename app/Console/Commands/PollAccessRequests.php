<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Throwable;

class PollAccessRequests extends Command
{
    protected $signature = 'locker:poll-requests {--interval=2 : Seconds between Firebase polls}';
    protected $description = 'Poll Firebase accessRequests and write accessResponses for ESP32 devices';

    public function handle(): int
    {
        $interval = max(1, min(30, (int) $this->option('interval')));
        $this->info("Polling Firebase accessRequests every {$interval}s. Press Ctrl+C to stop.");

        while (true) {
            try {
                $this->pollOnce();
            } catch (Throwable $e) {
                Log::error('Smart Locker polling worker error', ['exception' => $e]);
                $this->error('Polling error: ' . $e->getMessage());
            }
            sleep($interval);
        }

        // Unreachable; loop intentionally runs until Ctrl+C.
        return self::SUCCESS;
    }

    private function pollOnce(): void
    {
        $db = Firebase::database();
        $requests = $db->getReference('accessRequests')->getValue();
        if (!is_array($requests)) {
            return;
        }

        foreach ($requests as $requestId => $request) {
            if (!is_string($requestId) || !preg_match('/^[A-Za-z0-9_-]{8,80}$/', $requestId)) {
                continue;
            }
            if (!is_array($request) || ($request['status'] ?? null) !== 'pending') {
                continue;
            }

            // Single worker instance is expected for this prototype. Mark processing first
            // so this worker does not handle the same item twice on the next poll.
            $requestRef = $db->getReference("accessRequests/{$requestId}");
            $requestRef->update([
                'status' => 'processing',
                'processingAt' => $this->nowMs(),
            ]);

            try {
                $response = $this->decide($db, $requestId, $request);
                $db->getReference("accessResponses/{$requestId}")->set($response);
                $requestRef->update([
                    'status' => 'completed',
                    'completedAt' => $this->nowMs(),
                ]);
                $this->line(sprintf('%s => %s (%s)', $requestId, $response['decision'], $response['message']));
            } catch (Throwable $e) {
                Log::error('Smart Locker request processing failed', [
                    'requestId' => $requestId,
                    'exception' => $e,
                ]);
                // Fail closed: publish a denial if possible. ESP32 must never unlock on timeout.
                try {
                    $db->getReference("accessResponses/{$requestId}")->set([
                        'requestId' => $requestId,
                        'decision' => 'denied',
                        'lockerId' => null,
                        'message' => 'Verifikasi server gagal; loker tetap terkunci.',
                        'processedAt' => $this->nowMs(),
                    ]);
                    $requestRef->update(['status' => 'failed', 'completedAt' => $this->nowMs()]);
                } catch (Throwable $writeError) {
                    Log::error('Could not publish fail-closed response', ['requestId' => $requestId, 'exception' => $writeError]);
                }
            }
        }
    }

    private function decide($db, string $requestId, array $request): array
    {
        $deny = fn (string $message, ?string $lockerId = null) => [
            'requestId' => $requestId,
            'decision' => 'denied',
            'lockerId' => $lockerId,
            'message' => $message,
            'processedAt' => $this->nowMs(),
        ];

        $deviceId = (string) ($request['deviceId'] ?? '');
        $uid = strtoupper((string) preg_replace('/[\s:-]+/', '', (string) ($request['uid'] ?? '')));
        $createdAt = (int) ($request['createdAt'] ?? 0);

        if (!preg_match('/^[A-Za-z0-9_-]{1,60}$/', $deviceId)) {
            return $deny('ID perangkat tidak valid.');
        }
        if (!preg_match('/^(?:[A-F0-9]{2}){4,20}$/', $uid)) {
            return $deny('Format UID tidak valid.');
        }
        // Ignore stale requests, including requests replayed long after the tap.
        $now = $this->nowMs();
        if ($createdAt <= 0 || $createdAt > $now + 10000 || ($now - $createdAt) > 30000) {
            return $deny('Permintaan kedaluwarsa. Tap kartu lagi.');
        }

        $device = $db->getReference("devices/{$deviceId}")->getValue();
        if (!is_array($device) || ($device['active'] ?? false) !== true || empty($device['lockerId'])) {
            return $deny('Perangkat tidak terdaftar atau nonaktif.');
        }
        $lockerId = (string) $device['lockerId'];
        if (!preg_match('/^[A-Za-z0-9_-]{1,40}$/', $lockerId)) {
            return $deny('Konfigurasi loker tidak valid.');
        }

        $locker = $db->getReference("lockers/{$lockerId}")->getValue();
        if (!is_array($locker) || ($locker['active'] ?? false) !== true || ($locker['deviceId'] ?? null) !== $deviceId) {
            return $deny('Loker tidak aktif atau perangkat tidak cocok.', $lockerId);
        }

        $card = $db->getReference("cards/{$uid}")->getValue();
        if (!is_array($card) || ($card['active'] ?? false) !== true || empty($card['studentId'])) {
            return $deny('Kartu tidak terdaftar atau nonaktif.', $lockerId);
        }

        $studentId = (string) $card['studentId'];
        if (!preg_match('/^[A-Za-z0-9_-]{1,40}$/', $studentId)) {
            return $deny('Data pemilik kartu tidak valid.', $lockerId);
        }
        $student = $db->getReference("students/{$studentId}")->getValue();
        if (!is_array($student) || ($student['active'] ?? false) !== true) {
            return $deny('Data siswa tidak aktif atau tidak ditemukan.', $lockerId);
        }
        if (($student['lockerId'] ?? null) !== $lockerId || ($locker['studentId'] ?? null) !== $studentId) {
            return $deny('Kartu tidak memiliki akses ke loker ini.', $lockerId);
        }

        $db->getReference('accessLogs')->push([
            'requestId' => $requestId,
            'uid' => $uid,
            'studentId' => $studentId,
            'deviceId' => $deviceId,
            'lockerId' => $lockerId,
            'result' => 'granted',
            'reason' => 'authorized',
            'timestamp' => $now,
        ]);

        return [
            'requestId' => $requestId,
            'decision' => 'granted',
            'lockerId' => $lockerId,
            'message' => 'Akses diterima.',
            'processedAt' => $now,
        ];
    }

    private function nowMs(): int
    {
        return (int) floor(microtime(true) * 1000);
    }
}
