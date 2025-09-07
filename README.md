# attendance-monitoring-sms
A PHP-based attendance monitoring system with SMS notifications for late arrivals, absences, and time logs. Includes QR-based logging, timezone handling, and monthly reporting.

# Attendance Monitoring System with SMS Notification

A PHP-based attendance monitoring system that allows students or employees to log attendance using QR codes. The system tracks `time-in` and `time-out`, calculates lateness, and sends SMS notifications for absences or late arrivals. It also provides monthly attendance reports with export options.

## ✨ Features
- QR code-based attendance logging  
- Automatic lateness detection (timezone aware)  
- SMS notification for presents and absences 
- Daily and Monthly attendance reports (CSV export)  
- Admin dashboard with filters (grade level, section, strand)  

## 🛠️ Tech Stack
- **Backend**: PHP 8+, MySQL  
- **Frontend**: HTML, CSS, JavaScript  
- **Libraries**: PHP QR Code, SMS API (IPROGRAM)  

## 📂 Project Structure

attendance-monitoring-sms/
│── index.php # QR scanner + attendance logging
│── process_qr_code.php # Handles attendance logic
│── config.php # Database and API configuration
│── sms_api.php # SMS sending logic
│── assets/ # CSS, JS, images
│── exports/ # Generated CSV reports
│── README.md
