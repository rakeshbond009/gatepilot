# GatePilot Mobile App

This is a Flutter application that wraps the GatePilot web portal (`http://192.168.10.250/gatepilot/`) in a native WebView for Android and iOS.

## Features
- **WebView Integration**: Fullscreen native WebView.
- **Camera & Microphone**: Supports capturing photos and videos for uploads.
- **File Uploads**: Native file chooser integration.
- **Permissions**: Automatically handles Camera, Microphone, Storage, and Location permissions.
- **External Links**: Opens phone numbers (`tel:`), SMS (`sms:`), emails (`mailto:`), and other schemes in external apps.

## Setup & Configuration

### 1. Web URL Configuration
The app is currently configured to point to `http://192.168.10.250/gatepilot/`.
If your server IP changes or if you are using an emulator:
- **Emulator**: Use `http://10.0.2.2/gatepilot/` (references the host machine's localhost).
- **Physical Device**: Ensure your phone is on the same WiFi as the server and use the server's LAN IP (e.g., `192.168.x.x`).

To change the URL, edit `lib/main.dart`:
```dart
final String initialUrl = "http://YOUR_IP_ADDRESS/gatepilot/";
```

### 2. Building for Android
Run the following command in the `truck_movement_mobile` directory:
```bash
flutter build apk --debug
```
The APK will be generated at `build/app/outputs/flutter-apk/app-debug.apk`.

### 3. Building for iOS
(Requires macOS)
Run:
```bash
flutter build ios
```

## Troubleshooting
- **Network Error / White Screen**: 
    - Verify the IP address in `lib/main.dart` is correct and accessible from the device.
    - Ensure both devices are on the same network.
    - Cleartext traffic (HTTP) is enabled by default in this app to support local servers.
- **SSL Errors**: The project is configured to allow non-secure connections and use Aliyun mirrors to bypass some network restrictions during build.
