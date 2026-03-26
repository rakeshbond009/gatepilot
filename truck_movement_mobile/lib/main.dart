import 'package:flutter/material.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:flutter/foundation.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Ensure WebView cookie storage is enabled and persisted across app restarts
  if (!kIsWeb) {
    await InAppWebViewController.setWebContentsDebuggingEnabled(kDebugMode);
  }

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
          seedColor: const Color(0xFF4F46E5),
          brightness: Brightness.light,
        ),
        scaffoldBackgroundColor: const Color(0xFFF8FAFC),
        useMaterial3: true,
      ),
      home: const SplashScreen(),
      debugShowCheckedModeBanner: false,
    );
  }
}

/// Splash screen that checks for a persisted login cookie
/// before deciding which URL to open
class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  static const String baseUrl = "https://gatemanagement.codepilotx.com/";

  @override
  void initState() {
    super.initState();
    _checkSessionAndNavigate();
  }

  Future<void> _checkSessionAndNavigate() async {
    String startUrl = baseUrl; // Default: home (will redirect to login if needed)

    try {
      // Check if a persistent login cookie already exists in the WebView cookie store
      final cookieManager = CookieManager.instance();
      final cookie = await cookieManager.getCookie(
        url: WebUri(baseUrl),
        name: "GATEPILOT_REMEMBER",
      );

      if (cookie != null && cookie.value.toString().isNotEmpty) {
        // Cookie exists → go directly to dashboard so PHP can auto-login via token
        startUrl = "${baseUrl}?page=dashboard";
        debugPrint("Persistent cookie found → loading dashboard");
      } else {
        debugPrint("No persistent cookie → loading home (will show login)");
      }
    } catch (e) {
      debugPrint("Cookie check error: $e");
    }

    if (!mounted) return;
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(
        builder: (_) => WebViewScreen(startUrl: startUrl),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      backgroundColor: Color(0xFF1E293B),
      body: Center(
        child: CircularProgressIndicator(color: Color(0xFF4F46E5)),
      ),
    );
  }
}

class WebViewScreen extends StatefulWidget {
  final String startUrl;
  const WebViewScreen({super.key, required this.startUrl});

  @override
  State<WebViewScreen> createState() => _WebViewScreenState();
}

class _WebViewScreenState extends State<WebViewScreen> {
  final GlobalKey webViewKey = GlobalKey();

  InAppWebViewController? webViewController;

  InAppWebViewSettings settings = InAppWebViewSettings(
    isInspectable: kDebugMode,
    mediaPlaybackRequiresUserGesture: false,
    allowsInlineMediaPlayback: true,
    iframeAllow: "camera; microphone",
    iframeAllowFullscreen: true,
    useHybridComposition: false,
    allowFileAccess: true,
    allowContentAccess: true,
    javaScriptCanOpenWindowsAutomatically: true,
    supportZoom: true,
    // Ensures cookies are stored to disk, not just in memory
    incognito: false,
    databaseEnabled: true,
    domStorageEnabled: true,
    applicationNameForUserAgent: "GatePilot/1.0",
  );

  @override
  void initState() {
    super.initState();
    _requestPermissions();
  }

  Future<void> _requestPermissions() async {
    if (!kIsWeb) {
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
      backgroundColor: const Color(0xFF1E293B),
      body: SafeArea(
        bottom: false,
        child: PopScope(
          canPop: false,
          onPopInvokedWithResult: (didPop, result) async {
            if (didPop) return;
            if (webViewController != null &&
                await webViewController!.canGoBack()) {
              webViewController!.goBack();
            }
          },
          child: InAppWebView(
            key: webViewKey,
            initialUrlRequest: URLRequest(url: WebUri(widget.startUrl)),
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
              if (![
                "http",
                "https",
                "file",
                "chrome",
                "data",
                "javascript",
                "about"
              ].contains(uri.scheme)) {
                if (await canLaunchUrl(uri)) {
                  await launchUrl(uri);
                  return NavigationActionPolicy.CANCEL;
                }
              }
              return NavigationActionPolicy.ALLOW;
            },
            onGeolocationPermissionsShowPrompt: (controller, origin) async {
              return GeolocationPermissionShowPromptResponse(
                  origin: origin, allow: true, retain: true);
            },
            onConsoleMessage: (controller, consoleMessage) {
              if (kDebugMode) {
                print(consoleMessage);
              }
            },
            onDownloadStartRequest: (controller, downloadStartRequest) async {
              final validUri =
                  Uri.parse(downloadStartRequest.url.toString());
              if (await canLaunchUrl(validUri)) {
                await launchUrl(validUri,
                    mode: LaunchMode.externalApplication);
              }
            },
          ),
        ),
      ),
    );
  }
}
