#include <SoftwareSerial.h>
#include <Wire.h> 
#include <LiquidCrystal_I2C.h>//thu vien lcd_i2c
#include "DHT.h"

LiquidCrystal_I2C lcd(0x27, 16, 2); 
SoftwareSerial esp(2, 3);// RX, TX

const int DHTPIN = 2;       //Đọc dữ liệu từ DHT11 ở chân 2 trên mạch Arduino
const int DHTTYPE = DHT11;  //Khai báo loại cảm biến, có 2 loại là DHT11 và DHT22  
DHT dht(DHTPIN, DHTTYPE);
int inputPin = 3;
int LED_CONNECTED = 13;
int val_hc = 0; 
int Ledstatus = 0;
int HC_Status = 0;
String Data = "";

String ssid ="jester";
String password="123456789";

String server = "smarthome.cf";
String uri = "/index.php"; 

void setup() {
  delay(5000);// delay 5s de ghim chan RX và TX  
  Serial.begin(9600);//khoi tao cong Serial Arduino 
  esp.begin(115200);//khoi tao cong serial de gui tap len AT tu arduino sang esp-v1
  pinMode(LED_CONNECTED, OUTPUT);
  pinMode(inputPin, INPUT); 
  
  lcd.init();    //khoi dong lcd                
  lcd.backlight(); //bat den nen lcd  
  lcd.setCursor(5,0);
  lcd.print("Hello");
  while(i<3)
  {
    lcd.print(".");
    i++;
    delay(500);
  }
  dht.begin();         // Khởi động cảm biến
  
  connectWifi();
}
void loop() {
      float h = dht.readHumidity();    //Đọc độ ẩm
      float t = dht.readTemperature(); //Đọc nhiệt độ
      
      //dht11 cam bien nhiet do
      if(isnan(t) || isnan(h)){
        lcd.print("khong lay duoc ");
      }else{
      lcd.clear();
      lcd.setCursor(0,0);
      lcd.print("Nhiet do: ");
      lcd.print(t);
      lcd.print("'");
      lcd.print("C");//Xuất nhiệt độ
      lcd.setCursor(0,1);
      lcd.print("Do am: ");
      lcd.print(h);   
      lcd.print(" %");//Xuất độ ẩm
      }
      //gui gia tri nhiet do do am trong phong len host de co the hien thi len app
      //http://smarthome.cf/index.php?hum=h&tem=t
  getmethod();
  if(HC_Status==1){
        val_hc = digitalRead(inputPin);  //hc-sr501
        if (val_hc == HIGH) {            
          digitalWrite(LED_CONNECTED, HIGH);
          Serial.println("co chuyen dong");  
          delay(15000); 
        }
        else{
          digitalWrite(LED_CONNECTED, HIGH);
        }
  }else{
        if(Ledstatus==1){
            digitalWrite(LED_CONNECTED, HIGH);
        }else{
            digitalWrite(LED_CONNECTED, LOW);
        }
  }
  
  delay(1000);
}
void connectWifi(){
  
  deliverMessage("AT+RST", 1000);
  deliverMessage("AT+CWMODE=1", 1000);
  deliverMessage("AT+CWJAP=\"" + ssid + "\",\"" + password + "\"",3000);
   if(esp.find("OK")){
      Serial.println("Connected!");
//      digitalWrite(LED_CONNECTED, HIGH);
    }
   else {
    Serial.println("Cannot connect to wifi ! Connecting again..."); 
//    digitalWrite(LED_CONNECTED, LOW);
    connectWifi();
  }
}
void deliverMessage(const String& msg, int dt){
  esp.println(msg);
  delay(dt);
}
void getmethod(){
  connectTCP();
  
  String getRequest =
    "GET" + uri + " HTTP/1.0\r\n" +
    "Host: " + server + "\r\n" +
    "Accept: *" + "/" + "*\r\n" +
    "Content-Type: application/json\r\n" +
    "\r\n"; 

    
    String sendCmd = "AT+CIPSEND=";
    
     esp.print(sendCmd);
     esp.println(getRequest.length());
    
     if (esp.find(">")) {
         Serial.println("Sending..");
          esp.print(getRequest);
          delay(3000);
      if ( esp.find("SEND OK")) {
              Serial.println("Packet sent");
              esp.println("+IPD,12:");//received data http
              while(esp.available()){
                Data = esp.readString();
                Serial.write(esp.read());
                delay(1500);
              }
               HC_Status = Data.indexOf("hc_on")>0?1:0;
               Ledstatus = Data.indexOf("ledon")>0?1:0;
               
    //       close the connection
            esp.println("AT+CIPCLOSE");
      }
      else{
        Serial.println("khong gui dc request");
      }
     }
}
void connectTCP(){
  deliverMessage("AT+CIPSTART=\"TCP\",\"" + server + "\",80",1000);//start a TCP connection.
  if(esp.find("OK")) {
    Serial.println("TCP connection ready");
  }
}
