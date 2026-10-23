<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Throwable;

class LockerAdminController extends Controller
{
    public function loginPage()
    {
        if (session('locker_admin_authenticated') === true) {
            return redirect()->route('locker.dashboard');
        }

        return view('locker.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $input = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $expectedUsername = (string) config('locker.admin_username');
        $expectedPassword = (string) config('locker.admin_password');

        if ($expectedPassword === '' ||
            !hash_equals($expectedUsername, (string) $input['username']) ||
            !hash_equals($expectedPassword, (string) $input['password'])) {
            return back()->withInput($request->only('username'))
                ->withErrors(['login' => 'Username atau password salah. Periksa konfigurasi .env.']);
        }

        $request->session()->regenerate();
        $request->session()->put('locker_admin_authenticated', true);
        $request->session()->put('locker_admin_username', $expectedUsername);

        return redirect()->route('locker.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['locker_admin_authenticated', 'locker_admin_username']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('locker.login');
    }

    public function dashboard()
    {
        return view('locker.dashboard');
    }

    public function data(): JsonResponse
    {
        try {
            $db = Firebase::database();

            return response()->json([
                'success' => true,
                'students' => $db->getReference('students')->getValue() ?: new \stdClass(),
                'cards' => $db->getReference('cards')->getValue() ?: new \stdClass(),
                'lockers' => $db->getReference('lockers')->getValue() ?: new \stdClass(),
                'devices' => $db->getReference('devices')->getValue() ?: new \stdClass(),
                'accessLogs' => $db->getReference('accessLogs')->getValue() ?: new \stdClass(),
            ]);
        } catch (Throwable $e) {
            Log::error('Smart Locker admin data read failed', ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca Firebase. Periksa konfigurasi dan log Laravel.',
            ], 503);
        }
    }

    public function storeStudent(Request $request): JsonResponse
    {
        return $this->persistStudent($request, null);
    }

    public function updateStudent(Request $request, string $id): JsonResponse
    {
        return $this->persistStudent($request, $id);
    }

    private function persistStudent(Request $request, ?string $routeId): JsonResponse
    {
        $input = $request->validate([
            'id' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/'],
            'name' => ['required', 'string', 'max:120'],
            'class' => ['nullable', 'string', 'max:80'],
            'lockerId' => ['nullable', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]*$/'],
            'active' => ['required', 'boolean'],
        ]);

        $id = (string) $input['id'];
        if ($routeId !== null && $routeId !== $id) {
            throw ValidationException::withMessages(['id' => 'ID siswa tidak dapat diubah. Buat ID siswa baru jika diperlukan.']);
        }

        $db = Firebase::database();
        $studentRef = $db->getReference("students/{$id}");
        $existing = $studentRef->getValue();

        if ($routeId === null && is_array($existing)) {
            throw ValidationException::withMessages(['id' => 'ID siswa sudah ada. Gunakan tombol Edit pada tabel siswa.']);
        }
        if ($routeId !== null && !is_array($existing)) {
            return response()->json(['message' => 'Data siswa tidak ditemukan.'], 404);
        }

        $newLockerId = trim((string) ($input['lockerId'] ?? ''));
        $newLockerId = $newLockerId !== '' ? $newLockerId : null;
        $oldLockerId = is_array($existing) ? ($existing['lockerId'] ?? null) : null;
        $updates = [];

        if ($newLockerId !== null) {
            $targetLocker = $db->getReference("lockers/{$newLockerId}")->getValue();
            if (!is_array($targetLocker) || (($targetLocker['active'] ?? false) !== true && $newLockerId !== $oldLockerId)) {
                throw ValidationException::withMessages(['lockerId' => 'Loker tidak ditemukan atau sedang nonaktif. Buat/aktifkan loker terlebih dahulu.']);
            }

            $owner = $targetLocker['studentId'] ?? null;
            if ($owner !== null && $owner !== '' && $owner !== $id) {
                throw ValidationException::withMessages(['lockerId' => "Loker {$newLockerId} sudah dimiliki siswa {$owner}."]);
            }
        }

        if ($oldLockerId && $oldLockerId !== $newLockerId) {
            $oldLocker = $db->getReference("lockers/{$oldLockerId}")->getValue();
            if (is_array($oldLocker) && ($oldLocker['studentId'] ?? null) === $id) {
                $updates["lockers/{$oldLockerId}/studentId"] = null;
            }
        }

        $student = [
            'name' => trim($input['name']),
            'class' => trim((string) ($input['class'] ?? '')),
            'lockerId' => $newLockerId,
            'active' => (bool) $input['active'],
            'updatedAt' => $this->nowMs(),
        ];
        if ($routeId === null) {
            $student['createdAt'] = $this->nowMs();
        } else {
            $student['createdAt'] = $existing['createdAt'] ?? $this->nowMs();
        }

        $updates["students/{$id}"] = $student;
        if ($newLockerId !== null) {
            $updates["lockers/{$newLockerId}/studentId"] = $id;
        }

        $db->getReference()->update($updates);

        return response()->json(['success' => true, 'message' => 'Data siswa berhasil disimpan.']);
    }

    public function storeCard(Request $request): JsonResponse
    {
        $input = $request->validate([
            'uid' => ['required', 'string', 'max:80'],
            'studentId' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/'],
            'active' => ['required', 'boolean'],
        ]);

        $uid = strtoupper((string) preg_replace('/[\s:-]+/', '', $input['uid']));
        if (!preg_match('/^(?:[A-F0-9]{2}){4,20}$/', $uid)) {
            throw ValidationException::withMessages(['uid' => 'Format UID tidak valid. Masukkan angka heksadesimal, misalnya 04A1B2C3.']);
        }

        $db = Firebase::database();
        $student = $db->getReference('students/' . $input['studentId'])->getValue();
        if (!is_array($student)) {
            throw ValidationException::withMessages(['studentId' => 'Siswa tidak ditemukan. Tambahkan siswa terlebih dahulu.']);
        }

        $ref = $db->getReference("cards/{$uid}");
        $old = $ref->getValue();
        $now = $this->nowMs();
        $record = [
            'studentId' => $input['studentId'],
            'active' => (bool) $input['active'],
            'updatedAt' => $now,
            'createdAt' => is_array($old) ? ($old['createdAt'] ?? $now) : $now,
        ];
        if (is_array($old) && ($old['studentId'] ?? null) !== $input['studentId']) {
            $record['previousStudentId'] = $old['studentId'] ?? null;
        } elseif (is_array($old) && isset($old['previousStudentId'])) {
            $record['previousStudentId'] = $old['previousStudentId'];
        }

        $ref->set($record);

        return response()->json(['success' => true, 'message' => "UID {$uid} berhasil ditautkan ke {$input['studentId']}.", 'uid' => $uid]);
    }

    public function updateCardStatus(Request $request, string $uid): JsonResponse
    {
        $input = $request->validate(['active' => ['required', 'boolean']]);
        $uid = strtoupper($uid);
        if (!preg_match('/^(?:[A-F0-9]{2}){4,20}$/', $uid)) {
            return response()->json(['message' => 'UID tidak valid.'], 422);
        }

        $ref = Firebase::database()->getReference("cards/{$uid}");
        $card = $ref->getValue();
        if (!is_array($card)) {
            return response()->json(['message' => 'Kartu tidak ditemukan.'], 404);
        }
        $card['active'] = (bool) $input['active'];
        $card['updatedAt'] = $this->nowMs();
        $ref->set($card);

        return response()->json(['success' => true, 'message' => 'Status kartu berhasil diperbarui.']);
    }

    public function storeLocker(Request $request): JsonResponse
    {
        $input = $request->validate([
            'id' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/'],
            'deviceId' => ['required', 'string', 'max:60', 'regex:/^[A-Za-z0-9_-]+$/'],
            'active' => ['required', 'boolean'],
        ]);

        $db = Firebase::database();
        $id = $input['id'];
        $deviceId = $input['deviceId'];
        $lockerRef = $db->getReference("lockers/{$id}");
        $existing = $lockerRef->getValue();
        if (is_array($existing)) {
            throw ValidationException::withMessages(['id' => 'ID loker sudah ada. Gunakan Edit pada tabel loker.']);
        }

        $device = $db->getReference("devices/{$deviceId}")->getValue();
        if (is_array($device) && ($device['active'] ?? false) === true && ($device['lockerId'] ?? null) !== $id) {
            throw ValidationException::withMessages(['deviceId' => 'Perangkat ini sudah terhubung ke loker lain.']);
        }

        $now = $this->nowMs();
        $db->getReference()->update([
            "lockers/{$id}" => ['studentId' => null, 'deviceId' => $deviceId, 'active' => (bool) $input['active'], 'createdAt' => $now, 'updatedAt' => $now],
            "devices/{$deviceId}" => ['lockerId' => $id, 'active' => (bool) $input['active'], 'updatedAt' => $now],
        ]);

        return response()->json(['success' => true, 'message' => 'Loker berhasil dibuat.']);
    }

    public function updateLocker(Request $request, string $id): JsonResponse
    {
        $input = $request->validate([
            'deviceId' => ['required', 'string', 'max:60', 'regex:/^[A-Za-z0-9_-]+$/'],
            'active' => ['required', 'boolean'],
        ]);

        $db = Firebase::database();
        $lockerRef = $db->getReference("lockers/{$id}");
        $locker = $lockerRef->getValue();
        if (!is_array($locker)) {
            return response()->json(['message' => 'Loker tidak ditemukan.'], 404);
        }

        $deviceId = $input['deviceId'];
        $oldDeviceId = $locker['deviceId'] ?? null;
        $otherDevice = $db->getReference("devices/{$deviceId}")->getValue();
        if (is_array($otherDevice) && ($otherDevice['active'] ?? false) === true && ($otherDevice['lockerId'] ?? null) !== $id) {
            throw ValidationException::withMessages(['deviceId' => 'Perangkat ini sudah terhubung ke loker lain.']);
        }

        $now = $this->nowMs();
        $locker['deviceId'] = $deviceId;
        $locker['active'] = (bool) $input['active'];
        $locker['updatedAt'] = $now;
        $updates = [
            "lockers/{$id}" => $locker,
            "devices/{$deviceId}" => ['lockerId' => $id, 'active' => (bool) $input['active'], 'updatedAt' => $now],
        ];
        if ($oldDeviceId && $oldDeviceId !== $deviceId) {
            $updates["devices/{$oldDeviceId}/active"] = false;
            $updates["devices/{$oldDeviceId}/updatedAt"] = $now;
        }
        $db->getReference()->update($updates);

        return response()->json(['success' => true, 'message' => 'Pengaturan loker berhasil diperbarui.']);
    }

    private function nowMs(): int
    {
        return (int) floor(microtime(true) * 1000);
    }
}
