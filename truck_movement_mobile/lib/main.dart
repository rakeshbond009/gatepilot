import 'package:flutter/material.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:flutter/foundation.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const TruckMovementApp());
}

class TruckMovementApp extends StatelessWidget {
  const TruckMovementApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Gatepilot',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF4F46E5), // Indigo Primary
          brightness: Brightness.light,
        ),
        scaffoldBackgroundColor: const Color(0xFFF8FAFC), // bg-light
        useMaterial3: true,
      ),
      home: const WebViewScreen(),
      debugShowCheckedModeBanner: false,
    );
  }
}

class WebViewScreen extends StatefulWidget {
  const WebViewScreen({super.key});

  @override
  State<WebViewScreen> createState() => _WebViewScreenState();
}

class _WebViewScreenState extends State<WebViewScreen> {
  final GlobalKey webViewKey = GlobalKey();
  final String initialUrl = "https://gatemanagement.codepilotx.com/";

  InAppWebViewController? webViewController;
  InAppWebViewSettings settings = InAppWebViewSettings(
      isInspectable: kDebugMode,
      mediaPlaybackRequiresUserGesture: false,
      allowsInlineMediaPlayback: true,
      iframeAllow: "camera; microphone",
      iframeAllowFullscreen: true,
      useHybridComposition: false, // Set to false to prevent emulator crashes
      allowFileAccess: true,
      allowContentAccess: true,
      javaScriptCanOpenWindowsAutomatically: true,
      supportZoom: true,
      thirdPartyCookiesEnabled: true,
      cacheMode: CacheMode.LOAD_DEFAULT,
  );

  @override
  void initState() {
    super.initState();
    _requestPermissions();
  }

  Future<void> _requestPermissions() async {
    if (!kIsWeb) {
      // Small delay to allow UI to settle before showing popups
      await Future.delayed(const Duration(milliseconds: 1500));
      try {
        await [
          Permission.camera,
          Permission.microphone,
          Permission.location,
        ].request();
        
        if (await Permission.storage.isDenied) {
          await Permission.storage.request();
        }
      } catch (e) {
        debugPrint("Permission Error: $e");
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF1E293B), // Professional Slate
      body: SafeArea(
        bottom: false,
        child: PopScope(
          canPop: false,
          onPopInvokedWithResult: (didPop, result) async {
            if (didPop) return;
            if (webViewController != null && await webViewController!.canGoBack()) {
              webViewController!.goBack();
            }
          },
          child: InAppWebView(
            key: webViewKey,
            initialUrlRequest: URLRequest(url: WebUri(initialUrl)),
            initialSettings: settings,
            onWebViewCreated: (controller) {
              webViewController = controller;
            },
            onPermissionRequest: (controller, request) async {
              return PermissionResponse(
                  resources: request.resources,
                  action: PermissionResponseAction.GRANT);
            },
            shouldOverrideUrlLoading: (controller, navigationAction) async {
              var uri = navigationAction.request.url!;

              if (![ "http", "https", "file", "chrome", "data", "javascript", "about"].contains(uri.scheme)) {
                if (await canLaunchUrl(uri)) {
                  // Launch the App
                  await launchUrl(
                    uri,
                  );
                  // and cancel the request
                  return NavigationActionPolicy.CANCEL;
                }
              }

              return NavigationActionPolicy.ALLOW;
            },
            onGeolocationPermissionsShowPrompt: (controller, origin) async {
              return GeolocationPermissionShowPromptResponse(origin: origin, allow: true, retain: true);
            },
            onConsoleMessage: (controller, consoleMessage) {
              if (kDebugMode) {
                print(consoleMessage);
              }
            },
            onDownloadStartRequest: (controller, downloadStartRequest) async {
               // Launch the URL to handle download via system browser
               final validUri = Uri.parse(downloadStartRequest.url.toString());
               if (await canLaunchUrl(validUri)) {
                 await launchUrl(validUri, mode: LaunchMode.externalApplication);
               }
            },
          ),
        ),
      ),
    );
  }
}
