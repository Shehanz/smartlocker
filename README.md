# Smart Locker Firebase Bridge (polling prototype)

This add-on changes the data path to:

**ESP32/Wokwi → Firebase `accessRequests` → Laravel polling worker → Firebase `accessResponses/{requestId}` → ESP32 polls its own response.**

The existing admin website can continue managing `students`, `cards`, `lockers`, and `devices` in Firebase using the Firebase Admin SDK. The worker uses those same paths.

## Files

- `app/Console/Commands/PollAccessRequests.php` — Artisan worker.
- `firmware/wokwi/sketch.ino` — ESP32 + RC522 + LCD I2C + servo sketch; preserves GPIO wiring from the existing project.
- `firmware/wokwi/libraries.txt` — Wokwi libraries.
- `firebase/database.rules.test-only.json` — intentionally limited, **test-only** RTDB rules example.

## 1. Install into existing Laravel project

Copy `app/Console/Commands/PollAccessRequests.php` into:

`C:\E\Database\laragon\www\smartlocker\app\Console\Commands\PollAccessRequests.php`

Laravel 11/12 normally auto-discovers commands in `app/Console/Commands`. Check with:

```bat
php artisan list --raw | findstr locker:poll-requests
```

If not listed, register the command in `routes/console.php` or the app's command discovery setup according to your Laravel version. Do not overwrite existing files.

Keep the working Firebase env settings already in `.env`:

```dotenv
FIREBASE_DATABASE_URL=https://YOUR-REAL-DATABASE-URL
FIREBASE_CREDENTIALS=C:/secure/firebase-service-account.json
```

The worker uses the same Firebase Admin SDK connection that was already tested.

## 2. Data model expected in Firebase

```json
{
  "students": {
    "S001": { "name": "Budi", "class": "XII RPL 1", "lockerId": "L01", "active": true }
  },
  "cards": {
    "01020304": { "studentId": "S001", "active": true }
  },
  "lockers": {
    "L01": { "studentId": "S001", "deviceId": "ESP32_01", "active": true }
  },
  "devices": {
    "ESP32_01": { "lockerId": "L01", "active": true }
  },
  "accessRequests": {},
  "accessResponses": {},
  "accessLogs": {}
}
```

Use the actual card UID, normalized uppercase hex with no spaces. The admin dashboard already writes these `students`, `cards`, `lockers`, and `devices` fields.

## 3. Start the Laravel polling worker

In terminal A, run Laravel as usual:

```bat
php artisan serve
```

In terminal B, run the worker:

```bat
php artisan optimize:clear
php artisan locker:poll-requests --interval=2
```

Keep terminal B running. This prototype intentionally uses one worker instance; do not run multiple copies without adding atomic claim/transaction logic. The worker only processes `status: pending`, marks it `processing`, validates the device/locker/card/student, writes `accessResponses/{requestId}`, then marks the request completed. It writes access logs for granted decisions. Denied outcomes are returned in the response; consider adding denial logs/retention before production.

## 4. Firebase Rules — IMPORTANT

`database.rules.test-only.json` is a **demo-only** rules example for unauthenticated Wokwi REST access. It allows anonymous writes under `accessRequests/{requestId}` and anonymous reads under `accessResponses`; this is not secure for a real locker and must not be deployed to production. Attackers could forge requests or read response data. The rules cannot validate a secret API header.

For the first local simulation, you can temporarily configure narrowly scoped rules for only these two paths while keeping `students`, `cards`, `lockers`, `devices`, and `accessLogs` closed. Laravel's service-account Admin SDK bypasses RTDB rules. Do not use broad root `.read: true` or `.write: true` rules. Revert test rules after demo.

**Production:** authenticate each ESP32 using Firebase Authentication/custom tokens or route device operations through a trusted service with per-device authentication; add request signatures/replay protection, least-privilege rules, response privacy, TLS certificate validation, log retention, and monitoring. A shared unauthenticated public RTDB path is only for throwaway Wokwi tests.

## 5. Configure Wokwi sketch

Open `firmware/wokwi/sketch.ino` and set:

```cpp
const char* FIREBASE_DATABASE_URL = "https://YOUR-REAL-DATABASE-URL";
const char* DEVICE_ID = "ESP32_01";
```

Use the exact Firebase database URL from Console, with no trailing slash. This sketch calls `setInsecure()` to simplify the Wokwi-only HTTPS demo; this disables server certificate verification and is not suitable for physical deployment. For production, validate the TLS certificate.

Libraries in Wokwi:

- MFRC522
- LiquidCrystal I2C
- ESP32Servo
- ArduinoJson

### Existing wiring

| Component | ESP32 GPIO |
|---|---:|
| Servo signal | 4 |
| LCD SDA | 17 |
| LCD SCL | 16 |
| RC522 RST | 23 |
| RC522 MISO | 22 |
| RC522 MOSI | 21 |
| RC522 SCK | 19 |
| RC522 SDA/SS | 18 |

RC522 VCC is 3.3V. All GNDs must be common.

## 6. Request/response flow

The ESP32 generates a fresh request ID for each tap and writes:

`accessRequests/{requestId}` with `deviceId`, `uid`, `status: pending`, and `createdAt`.

Laravel writes a response at the exact matching key:

`accessResponses/{requestId}` with `requestId`, `decision: granted|denied`, `lockerId`, `message`, and `processedAt`.

The ESP32 polls only `accessResponses/{itsRequestId}` and unlocks only when the response's `requestId` matches and `decision` equals `granted`. Network errors and timeout are fail-closed. The sketch syncs its clock using NTP after connecting to Wi-Fi. If NTP fails, it refuses to publish the request so it cannot accidentally send a stale timestamp.

### Timestamp note for Wokwi

The sketch initializes NTP and requires a valid epoch clock before publishing requests. If the Wokwi network cannot reach NTP, the Serial Monitor reports the issue and the servo stays locked. Do not remove freshness checks in the worker to work around a clock problem.

## 7. Troubleshooting

- `401/403` while ESP32 writes or reads Firebase: rules don't allow the unauthenticated demo request, or URL is wrong.
- Requests appear as `pending` forever: the Artisan worker is not running, the command was not discovered, or Laravel can't access Firebase.
- Response is `denied` with expired message: ESP32 clock isn't synced; add NTP sync as described in the timestamp note.
- Worker reports missing device: create `devices/ESP32_01` and ensure `active: true`, `lockerId: L01`; `lockers/L01` must also have `deviceId: ESP32_01` and `active: true`.
- Card denied: `cards/{UID}` must be active; `students/{studentId}` active; student's `lockerId` and `lockers/{lockerId}.studentId` must match.
