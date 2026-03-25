import javax.net.ssl.*

// 1. SSL BYPASS
val trustAllCerts = arrayOf<TrustManager>(object : X509TrustManager {
    override fun checkClientTrusted(chain: Array<java.security.cert.X509Certificate>, authType: String) {}
    override fun checkServerTrusted(chain: Array<java.security.cert.X509Certificate>, authType: String) {}
    override fun getAcceptedIssuers(): Array<java.security.cert.X509Certificate>? = null
})
val sc = SSLContext.getInstance("TLS")
sc.init(null, trustAllCerts, java.security.SecureRandom())
HttpsURLConnection.setDefaultSSLSocketFactory(sc.socketFactory)
HttpsURLConnection.setDefaultHostnameVerifier { _, _ -> true }

allprojects {
    repositories {
        maven { url = uri("http://maven.aliyun.com/repository/google") ; isAllowInsecureProtocol = true }
        maven { url = uri("http://maven.aliyun.com/repository/public") ; isAllowInsecureProtocol = true }
    }
    
    buildscript {
        repositories {
            maven { url = uri("http://maven.aliyun.com/repository/google") ; isAllowInsecureProtocol = true }
            maven { url = uri("http://maven.aliyun.com/repository/public") ; isAllowInsecureProtocol = true }
        }
        configurations.all {
            resolutionStrategy {
                // Force AGP 8.6.0 for all plugins to prevent resolution of blocked versions
                force("com.android.tools.build:gradle:8.6.0")
            }
        }
    }
}

val newBuildDir: Directory =
    rootProject.layout.buildDirectory
        .dir("../../build")
        .get()
rootProject.layout.buildDirectory.value(newBuildDir)

subprojects {
    val newSubprojectBuildDir: Directory = newBuildDir.dir(project.name)
    project.layout.buildDirectory.value(newSubprojectBuildDir)
}

subprojects {
    afterEvaluate {
        val android = project.extensions.findByName("android")
        if (android is com.android.build.gradle.BaseExtension) {
            android.compileSdkVersion(35)
            android.buildToolsVersion("35.0.0")
            android.ndkVersion = "25.1.8937393"
            android.defaultConfig {
                minSdk = 24
                targetSdk = 35
            }
        }
    }
}

subprojects {
    configurations.all {
        resolutionStrategy {
            force("androidx.core:core:1.13.1")
            force("androidx.core:core-ktx:1.13.1")
            force("androidx.browser:browser:1.8.0")
            force("androidx.lifecycle:lifecycle-common:2.8.0")
            force("androidx.lifecycle:lifecycle-runtime:2.8.0")
            force("androidx.lifecycle:lifecycle-process:2.8.0")
        }
    }
}

tasks.register<Delete>("clean") {
    delete(rootProject.layout.buildDirectory)
}
