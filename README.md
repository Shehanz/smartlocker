# BlueVault Smart Locker — Starter Kit

Starter integration for an existing Laravel project + Firebase Realtime Database + ESP32/RC522 in Wokwi. Admin UI theme: black / white / blue.

> This is a development prototype. Before using real lockers, replace the simple `.env` admin login with standard hashed-user authentication + MFA, use HTTPS with certificate validation on the ESP32, use unique credentials per physical device, and review logging/rate limits.

## 1. What is included

- `app/Http/Controllers/LockerAdminController.php` — admin session login, student CRUD (create/edit), card UID registration/transfer/disable, locker/device create/edit, dashboard data.
- `app/Http/Controllers/LockerApiController.php` — backend-side permission checks for a UID and a device.
- `app/Http/Middleware/LockerAdminSession.php` — protects admin pages and mutation routes.
- `config/locker.php` — admin and prototype device config.
- `resources/views/locker/login.blade.php` — login page.
- `resources/views/locker/dashboard.blade.php` — responsive black/white/blue admin dashboard.
- `routes/locker.php` — routes for the admin website.
- `routes/api.php` — ESP32 verification API route (merge into your existing file if one already exists).
- `firmware/wokwi/sketch.ino` and `libraries.txt` — ESP32 sketch and Wokwi library list.

## 2. Copy files to your existing Laravel project

Extract this ZIP and copy the contents of this kit into the root of the existing project, preserving folders. Do not overwrite your existing `routes/web.php`, `routes/api.php`, `bootstrap/app.php`, or `.env` blindly.

Expected project location from the current setup:

`C:\E\Database\laragon\www\smartlocker`

### Register admin web routes

At the **bottom** of `routes/web.php`, add:

```php
require __DIR__.'/locker.php';
```

Keep your existing `/test-firebase` route for now; remove or disable it before any production deployment.

### Register API routes

Open `bootstrap/app.php`. In the existing `->withRouting(...)` call, make sure the API routes file is registered. A typical Laravel 11+ setup looks like:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

If `api: __DIR__.'/../routes/api.php'` is already present, do not add it twice. If `routes/api.php` already has routes, merge this kit's route into that file instead of replacing your current routes:

```php
use App\Http\Controllers\LockerApiController;
use Illuminate\Support\Facades\Route;

Route::post('/locker/verify', [LockerApiController::class, 'verify'])
    ->middleware('throttle:30,1');
```

Because this is in Laravel's API route group, the final URL is `/api/locker/verify` and it does not use browser CSRF tokens. The route itself requires a device API key.

## 3. Add local configuration to `.env`

Add these lines without deleting your existing Firebase settings:

```dotenv
LOCKER_ADMIN_USERNAME=admin
LOCKER_ADMIN_PASSWORD=change-this-to-a-long-unique-password
LOCKER_DEVICE_ID=ESP32_01
LOCKER_DEVICE_KEY=replace-with-a-long-random-alphanumeric-key
```

- Keep `FIREBASE_DATABASE_URL` and `FIREBASE_CREDENTIALS` as already configured for your successful Firebase connection.
- Replace both example secrets. Use a long random alphanumeric key to avoid `.env` quoting issues.
- Copy the exact value of `LOCKER_DEVICE_KEY` into `DEVICE_API_KEY` in the sketch. Never commit `.env` or the Firebase service-account JSON.
- This kit's simple admin username/password is for a local prototype only. Do not expose it as a production admin system.

Then run:

```bat
php artisan optimize:clear
php artisan route:list
```

Check that you can see `/admin/login`, `/admin`, and `api/locker/verify`. If the API route isn't listed, re-check `bootstrap/app.php` and `routes/api.php`.

## 4. Firebase database and access rules

Laravel accesses Firebase using the server service account. Keep Realtime Database rules closed to unauthenticated web/mobile clients. A locked baseline is:

```json
{
  "rules": {
    ".read": false,
    ".write": false
  }
}
```

The Firebase Admin SDK on the Laravel server is authenticated separately; the browser and ESP32 do not receive the service-account private key. If you previously opened public `.read`/`.write` rules for a direct ESP32 connection test, revert them now.

The admin dashboard creates the initial records. Start in this order:

1. Login at `http://127.0.0.1:8000/admin/login` using `LOCKER_ADMIN_USERNAME` / `LOCKER_ADMIN_PASSWORD`.
2. Open **Manajemen Loker**, add locker `L01`, device ID `ESP32_01`, active.
3. Open **Data Siswa**, add student `S001`, name/class, choose `L01`, active.
4. Open **Kartu RFID**, enter a real card UID (hexadecimal; spaces/colons/dashes are accepted), select `S001`, active.

The records are written to these paths:

```text
students/{studentId}
cards/{NORMALIZED_UID}
lockers/{lockerId}
devices/{deviceId}
accessLogs/{autoId}
```

An unlock is granted only if the device is active and mapped to the locker, the locker is active, the card is active, the student is active, and the student and locker ownership fields agree.

## 5. Launch Laravel locally

```bat
php artisan serve
```

The admin website will be at `http://127.0.0.1:8000/admin/login`.

## 6. Connect Wokwi to your Laravel API

Wokwi runs in the cloud and cannot call your Windows `localhost` directly. For a temporary test, start an HTTPS development tunnel that forwards to `http://127.0.0.1:8000` (for example, using a tunnel tool such as ngrok or Cloudflare Tunnel). Use its HTTPS public URL in `API_BASE_URL`, with no trailing slash:

```cpp
const char* API_BASE_URL = "https://YOUR-TEMPORARY-HTTPS-DOMAIN";
const char* DEVICE_ID = "ESP32_01";
const char* DEVICE_API_KEY = "EXACT_VALUE_FROM_LOCKER_DEVICE_KEY";
```

Do not use `http://localhost:8000` in the Wokwi sketch. The tunnel URL can change; update `API_BASE_URL` when that happens. Keep the tunnel active during the simulation.

### Preserve your existing Wokwi wiring

| Part | ESP32 pin |
| --- | ---: |
| Servo signal | GPIO 4 |
| LCD SDA / SCL | GPIO 17 / GPIO 16 |
| RC522 RST | GPIO 23 |
| RC522 MISO | GPIO 22 |
| RC522 MOSI | GPIO 21 |
| RC522 SCK | GPIO 19 |
| RC522 SDA/SS | GPIO 18 |

RC522 power must be 3.3V. All components share GND. In Wokwi, add the listed libraries from `firmware/wokwi/libraries.txt` (or copy that file into the project).

**TLS note:** the sketch calls `tlsClient.setInsecure()` to make a temporary Wokwi/tunnel test easier. This disables server-certificate validation and is not appropriate for a real physical locker. In production, install and validate the HTTPS server/tunnel CA certificate.

## 7. Test matrix

- Registered active card + active student + correct assigned locker/device → API returns `allowed: true`, servo opens briefly.
- Unknown UID or inactive card → denied, servo stays locked.
- Card assigned to a different student than the locker owner → denied.
- Inactive student, inactive locker, inactive/mismatched device, wrong device key, Firebase/network failure → no unlock.

## Troubleshooting

- `404` at `/api/locker/verify`: check API route registration and URL prefix.
- `401` from API: `DEVICE_API_KEY` in sketch does not match `LOCKER_DEVICE_KEY` in `.env`, or the environment key is empty.
- `403` from API: check `LOCKER_DEVICE_ID`, and the `devices/{deviceId}` / `lockers/{lockerId}` entries.
- `503` from API: check `storage/logs/laravel.log`, service account path, Firebase URL, and network access.
- Dashboard redirects repeatedly to login: clear cookies/session and confirm `APP_KEY` exists; also check that Laravel's session driver is configured correctly.
- Admin says a locker is unavailable: add the locker first, make sure it is active, and verify no other student owns it.

## Security / production checklist

1. Replace `.env` admin login with regular Laravel authentication, hashed passwords, and optional MFA.
2. Use HTTPS with certificate validation from the ESP32; do not use `setInsecure()` in production.
3. Issue a unique credential per device and support revocation/rotation. A key embedded in firmware can be extracted.
4. Keep Firebase service-account JSON and `.env` out of the web root and source control.
5. Add proper pagination/retention for `accessLogs`, backups, request signing/replay protection, monitoring, and physical fail-secure testing.
6. Ensure a server/API failure always leaves the latch locked.
