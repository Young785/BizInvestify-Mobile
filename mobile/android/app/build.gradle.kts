plugins {
    id("com.android.application")
    id("kotlin-android")
    // The Flutter Gradle Plugin must be applied after the Android and Kotlin Gradle plugins.
    id("dev.flutter.flutter-gradle-plugin")
}

android {
    namespace = "com.bizinvestify.mobile"
    compileSdk = 34
    ndkVersion = flutter.ndkVersion

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    kotlinOptions {
        jvmTarget = JavaVersion.VERSION_17.toString()
    }

    defaultConfig {
        applicationId = "com.bizinvestify.mobile"
        minSdk = 23
        targetSdk = 34
        versionCode = 1
        versionName = "1.0.0"
        multiDexEnabled = true
    }

    signingConfigs {
        release {
            // Release signing configuration will be added here
            // For now, using debug signing for development
        }
    }

    buildTypes {
        release {
            isMinifyEnabled = true
            isShrinkResources = true
            proguardFiles(
                getDefaultProguardFile("proguard-android-optimize.txt"),
                "proguard-rules.pro"
            )
            // signingConfig = signingConfigs.getByName("release")
            signingConfig = signingConfigs.getByName("debug") // Temporary for development
        }
        debug {
            isDebuggable = true
            applicationIdSuffix = ".debug"
            versionNameSuffix = "-debug"
        }
    }
}

flutter {
    source = "../.."
}

// Apply Google services plugin only if google-services.json exists
if (file("google-services.json").exists()) {
    apply(plugin = "com.google.gms.google-services")
}
