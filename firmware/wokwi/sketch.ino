#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <ESP32Servo.h>

// ===== EDIT THESE SETTINGS =====
const char* WIFI_SSID = "Wokwi-GUEST";
const char* WIFI_PASSWORD = "";

// Use an HTTPS tunnel URL to your local Laravel server, without a trailing slash.
// Example: https://your-temporary-domain.ngrok-free.app
const char* API_BASE_URL = "https://REPLACE-WITH-YOUR-HTTPS-TUNNEL";

// Must match .env and Firebase: devices/ESP32_01
const char* DEVICE_ID = "ESP32_01";
// Must exactly match LOCKER_DEVICE_KEY in Laravel .env. Test-only shared key.
const char* DEVICE_API_KEY = "REPLACE-WITH-A-LONG-RANDOM-KEY";

// ===== EXISTING WIRING =====
constexpr uint8_t SERVO_PIN = 4;
constexpr uint8_t LCD_SDA_PIN = 17;
constexpr uint8_t LCD_SCL_PIN = 16;
constexpr uint8_t RFID_SS_PIN = 18;    // RC522 SDA/SS
constexpr uint8_t RFID_RST_PIN = 23;
constexpr uint8_t RFID_SCK_PIN = 19;
constexpr uint8_t RFID_MISO_PIN = 22;
constexpr uint8_t RFID_MOSI_PIN = 21;

constexpr int SERVO_LOCKED_ANGLE = 0;
constexpr int SERVO_UNLOCKED_ANGLE = 90;
constexpr unsigned long UNLOCK_DURATION_MS = 3500;
constexpr unsigned long SAME_CARD_COOLDOWN_MS = 2500;

MFRC522 rfid(RFID_SS_PIN, RFID_RST_PIN);
LiquidCrystal_I2C lcd(0x27, 16, 2);
Servo lockServo;

String lastUid = "";
unsigned long lastTapMs = 0;

void showMessage(const String& line1, const String& line2 = "") {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(line1.substring(0, 16));
  lcd.setCursor(0, 1);
  lcd.print(line2.substring(0, 16));
}

String readUidNormalized() {
  String uid;
  uid.reserve(rfid.uid.size * 2);
  for (byte i = 0; i < rfid.uid.size; i++) {
    char part[3];
    snprintf(part, sizeof(part), "%02X", rfid.uid.uidByte[i]);
    uid += part;
  }
  return uid;
}

bool connectWiFi(unsigned long timeoutMs = 15000) {
  if (WiFi.status() == WL_CONNECTED) return true;
  showMessage("Menghubungkan", "WiFi Wokwi...");
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD, 6);
  const unsigned long started = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - started < timeoutMs) {
    delay(250);
  }
  if (WiFi.status() == WL_CONNECTED) {
    Serial.print("WiFi connected, IP: ");
    Serial.println(WiFi.localIP());
    return true;
  }
  Serial.println("WiFi connection failed");
  return false;
}

void unlockBriefly() {
  lockServo.write(SERVO_UNLOCKED_ANGLE);
  showMessage("AKSES DITERIMA", "Loker terbuka");
  Serial.println("Access granted: servo unlocked");
  delay(UNLOCK_DURATION_MS);
  lockServo.write(SERVO_LOCKED_ANGLE);
  showMessage("LOKER TERKUNCI", "Tap kartu RFID");
}

void verifyUidWithServer(const String& uid) {
  if (String(API_BASE_URL).indexOf("REPLACE-WITH-YOUR") >= 0 ||
      String(DEVICE_API_KEY).indexOf("REPLACE-WITH-A-LONG") >= 0) {
    showMessage("Konfigurasi API", "Belum diisi");
    Serial.println("Set API_BASE_URL and DEVICE_API_KEY in sketch. Do not leave placeholders.");
    delay(1800);
    showMessage("LOKER TERKUNCI", "Tap kartu RFID");
    return;
  }

  if (!connectWiFi()) {
    showMessage("WiFi gagal", "Akses ditolak");
    delay(1800);
    showMessage("LOKER TERKUNCI", "Tap kartu RFID");
    return;
  }

  // DEMO ONLY: this encrypts transport but does not validate the server certificate.
  // For a real deployment, validate the tunnel/server CA certificate instead of setInsecure().
  WiFiClientSecure tlsClient;
  tlsClient.setInsecure();

  HTTPClient http;
  const String url = String(API_BASE_URL) + "/api/locker/verify";
  http.setTimeout(8000);
  if (!http.begin(tlsClient, url)) {
    Serial.println("Could not start HTTPS request");
    showMessage("Server error", "Akses ditolak");
    delay(1800);
    showMessage("LOKER TERKUNCI", "Tap kartu RFID");
    return;
  }

  StaticJsonDocument<160> requestBody;
  requestBody["deviceId"] = DEVICE_ID;
  requestBody["uid"] = uid;
  String payload;
  serializeJson(requestBody, payload);

  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");
  http.addHeader("X-Device-Key", DEVICE_API_KEY);
  showMessage("Memeriksa kartu", uid);
  Serial.print("POST "); Serial.println(url);
  Serial.print("UID: "); Serial.println(uid);

  const int httpCode = http.POST(payload);
  const String responseBody = http.getString();
  http.end();

  Serial.print("HTTP status: "); Serial.println(httpCode);
  Serial.print("API response: "); Serial.println(responseBody);

  if (httpCode <= 0) {
    showMessage("Server tidak", "terjangkau");
    delay(1800);
    showMessage("LOKER TERKUNCI", "Tap kartu RFID");
    return;
  }

  StaticJsonDocument<512> responseJson;
  const DeserializationError parseError = deserializeJson(responseJson, responseBody);
  if (parseError) {
    Serial.print("JSON parse error: "); Serial.println(parseError.c_str());
    showMessage("Respons error", "Akses ditolak");
    delay(1800);
    showMessage("LOKER TERKUNCI", "Tap kartu RFID");
    return;
  }

  const bool allowed = responseJson["allowed"] | false;
  if (httpCode >= 200 && httpCode < 300 && allowed) {
    unlockBriefly();
  } else {
    const char* apiMessage = responseJson["message"] | "Akses ditolak";
    showMessage("AKSES DITOLAK", String(apiMessage));
    Serial.println("Access denied; servo stays locked");
    delay(2200);
    showMessage("LOKER TERKUNCI", "Tap kartu RFID");
  }
}

void setup() {
  Serial.begin(115200);
  delay(300);

  Wire.begin(LCD_SDA_PIN, LCD_SCL_PIN);
  lcd.init();
  lcd.backlight();
  showMessage("BlueVault Locker", "Starting...");

  lockServo.setPeriodHertz(50);
  lockServo.attach(SERVO_PIN, 500, 2400);
  lockServo.write(SERVO_LOCKED_ANGLE);

  SPI.begin(RFID_SCK_PIN, RFID_MISO_PIN, RFID_MOSI_PIN, RFID_SS_PIN);
  rfid.PCD_Init();
  delay(100);
  Serial.println("RC522 initialized");

  connectWiFi();
  showMessage("LOKER TERKUNCI", "Tap kartu RFID");
}

void loop() {
  if (!rfid.PICC_IsNewCardPresent()) {
    delay(25);
    return;
  }
  if (!rfid.PICC_ReadCardSerial()) {
    delay(25);
    return;
  }

  const String uid = readUidNormalized();
  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();

  const unsigned long nowMs = millis();
  if (uid == lastUid && nowMs - lastTapMs < SAME_CARD_COOLDOWN_MS) {
    delay(100);
    return;
  }
  lastUid = uid;
  lastTapMs = nowMs;

  verifyUidWithServer(uid);
  delay(150);
}
