#include <WiFi.h>
#include <HTTPClient.h>

#include <Arduino.h>
#include <stdlib.h>
#include <string.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <ArduinoJson.h>

// Define screen dimensions
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define TEXT_SIZE 1

void setOledDisplay(String displayText);

int sda_pin = 19;  // GPIO19 as I2C SDA
int scl_pin = 18;  // GPIO18 as I2C SCL

// Create an instance of the SSD1306 display
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, -1);

const char* ssid = "TELUS6486";
const char* password = "mNzM4K96GCTc";

// Replace with your server URL
const char* serverUrl = "https://myrandomproject.online/server_link.php";

String payload = "";
String message = "";
String name = "";

unsigned long restartInterval = 60000; 
unsigned long previousMillis = 0;

void setup() {
  Serial.begin(115200);
  previousMillis = millis();  // Store the start time

  WiFi.begin(ssid, password);

  Wire.setPins(sda_pin, scl_pin);  // Set the I2C pins before begin
  Wire.begin();                    // join i2c bus (address optional for master)

  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {  // Change 0x3C to your OLED's I2C address if different
    Serial.println(F("SSD1306 allocation failed"));
    for (;;)
      ;
  }
  display.clearDisplay();               // Clear the buffer
  display.setTextSize(TEXT_SIZE);       // Set text size
  display.setTextColor(SSD1306_WHITE);  // Set text color
  display.setCursor(0, 0);             // Set cursor position

  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");

}

void loop() {
  // Your loop code here

  unsigned long currentMillis = millis();

  // Check if the time interval has passed
  if (currentMillis - previousMillis >= restartInterval) {
    Serial.println("Restarting ESP32...");
    esp_restart();  // Restart the ESP32
  }

  refreshText();
  setOledDisplay();
  delay(5000);
}

void refreshText(void){
    // HTTP GET request

    if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverUrl);
    int httpCode = http.GET();  // Send GET request

    if (httpCode > 0) {
      payload = http.getString();
      Serial.println("Received data:");

      // Parse JSON data
      DynamicJsonDocument doc(2048);
      DeserializationError error = deserializeJson(doc, payload);

      if (error) {
        Serial.println("Failed to parse JSON");
        Serial.println(error.c_str());  // Print the error message
        return;
      }

      message = doc["message"].as<String>();
      name = doc["name"].as<String>();

      Serial.println("Message: " + message);
      Serial.println("Name " + name);

    } else {
      Serial.println("Error on HTTP request");
      Serial.println(httpCode);  // Print the HTTP error code
    }

    http.end();  // Close connection
  }
}

void setOledDisplay() {
  display.clearDisplay();  
  display.setCursor(0, 0);  // Reset cursor to the top-left corner
  display.print(message);
  newLine(message);
  display.print("~"+name);
  display.display();  // Display the text
}

void newLine(String displayMessage) {
  int n = displayMessage.length();
  int size = TEXT_SIZE;
  int y;

  if (size == 1){
    y = 21 - (n % 21);
  }
  else {
    y = 10 - (n % 10);
  }

  String str = "";

  for(int i=0; i<y; i++){
    str += " ";
  }

  display.print(str);
}

