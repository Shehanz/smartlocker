<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Throwable;

class LockerApiController extends Controller
{
    public function verify(Request $request): JsonResponse
    {
        $deviceKey = (string) $request->header('X-Device-Key', '');
        $expectedKey = (string) config('locker.device_key', '');
        if ($expectedKey === '' || !hash_equals($expectedKey, $deviceKey)) {
            return response()->json(['allowed' => false, 'message' => 'Perangkat tidak terautentikasi.'], 401);
        }

        $input = $request->validate([
            'deviceId' => ['required', 'string', 'max:60', 'regex:/^[A-Za-z0-9_-]+$/'],
            'uid' => ['required', 'string', 'regex:/^(?:[A-Fa-f0-9]{2}){4,20}$/'],
        ]);

        $deviceId = (string) $input['deviceId'];
        $configuredDeviceId = (string) config('locker.device_id', 'ESP32_01');
        if (!hash_equals($configuredDeviceId, $deviceId)) {
            return response()->json(['allowed' => false, 'message' => 'ID perangkat tidak cocok.'], 403);
        }

        $uid = strtoupper($input['uid']);
        $lockerId = null;

        try {
            $db = Firebase::database();
            $device = $db->getReference("devices/{$deviceId}")->getValue();
            if (!is_array($device) || ($device['active'] ?? false) !== true || empty($device['lockerId'])) {
                $reason = 'device_inactive_or_unconfigured';
                return $this->logAndRespond($db, $uid, $deviceId, null, false, $reason, 'Perangkat belum terdaftar atau nonaktif.', 403);
            }

            $lockerId = (string) $device['lockerId'];
            $locker = $db->getReference("lockers/{$lockerId}")->getValue();
            if (!is_array($locker) || ($locker['active'] ?? false) !== true || ($locker['deviceId'] ?? null) !== $deviceId) {
                $reason = 'locker_inactive_or_mismatched';
                return $this->logAndRespond($db, $uid, $deviceId, $lockerId, false, $reason, 'Loker tidak aktif atau konfigurasi perangkat tidak cocok.', 200);
            }

            $card = $db->getReference("cards/{$uid}")->getValue();
            if (!is_array($card) || ($card['active'] ?? false) !== true || empty($card['studentId'])) {
                $reason = is_array($card) ? 'card_inactive' : 'card_not_registered';
                return $this->logAndRespond($db, $uid, $deviceId, $lockerId, false, $reason, 'Kartu tidak terdaftar atau nonaktif.', 200);
            }

            $studentId = (string) $card['studentId'];
            $student = $db->getReference("students/{$studentId}")->getValue();
            if (!is_array($student) || ($student['active'] ?? false) !== true) {
                $reason = 'student_inactive_or_missing';
                return $this->logAndRespond($db, $uid, $deviceId, $lockerId, false, $reason, 'Data siswa tidak aktif atau tidak ditemukan.', 200);
            }

            if (($student['lockerId'] ?? null) !== $lockerId || ($locker['studentId'] ?? null) !== $studentId) {
                $reason = 'locker_not_owned_by_student';
                return $this->logAndRespond($db, $uid, $deviceId, $lockerId, false, $reason, 'Kartu tidak memiliki akses ke loker ini.', 200);
            }

            $reason = 'authorized';
            return $this->logAndRespond($db, $uid, $deviceId, $lockerId, true, $reason, 'Akses diterima.', 200);
        } catch (Throwable $e) {
            Log::error('Smart Locker RFID verification failed', ['exception' => $e, 'deviceId' => $deviceId]);
            return response()->json([
                'allowed' => false,
                'message' => 'Verifikasi sedang tidak tersedia. Loker tetap terkunci.',
            ], 503);
        }
    }

    private function logAndRespond($db, string $uid, string $deviceId, ?string $lockerId, bool $allowed, string $reason, string $message, int $status): JsonResponse
    {
        try {
            $db->getReference('accessLogs')->push([
                'uid' => $uid,
                'deviceId' => $deviceId,
                'lockerId' => $lockerId,
                'result' => $allowed ? 'granted' : 'denied',
                'reason' => $reason,
                'timestamp' => (int) floor(microtime(true) * 1000),
            ]);
        } catch (Throwable $e) {
            // Access decision must fail closed if verification/logging infrastructure is unhealthy.
            Log::error('Smart Locker access log write failed', ['exception' => $e]);
            return response()->json(['allowed' => false, 'message' => 'Tidak dapat mencatat akses. Loker tetap terkunci.'], 503);
        }

        return response()->json([
            'allowed' => $allowed,
            'message' => $message,
            'lockerId' => $lockerId,
        ], $status);
    }
}
