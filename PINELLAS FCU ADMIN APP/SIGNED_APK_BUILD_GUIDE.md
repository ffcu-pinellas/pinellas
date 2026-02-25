# Guide: Building the Signed APK for Pinellas Admin Mobile App

Follow these steps to generate a professional, signed APK ready for installation on your Android devices.

## 1. Prerequisites
- **Android Studio** installed.
- Your **Admin App Folder** (`PINELLAS FCU ADMIN APP`) ready.
- **google-services.json** placed inside the `app/` folder of your Android project.

## 2. Prepare the Capacitor Project
**[ALREADY DONE]**: I have already initialized the project, installed the libraries, and added the Android platform for you.

To open the project in Android Studio, you can simply run:
```bash
npx cap open android
```
(Run this command in the `PINELLAS FCU ADMIN APP` directory).

Once Android Studio opens, you can continue with the signing and building steps below.

## 3. Generate a Signed Bundle / APK
In Android Studio:
1.  Go to **Build** > **Generate Signed Bundle / APK...**
2.  Select **APK** and click **Next**.
3.  **Key Store Path**:
    - If you don't have one, click **Create new...**
    - Choose a path and a strong password.
    - **Alias**: Use `pinellas-admin`.
    - **Key Password**: Use another strong password.
    - Fill out the certificate info (Name, Org, etc.) and click **OK**.
4.  Select the keystore you just created and click **Next**.
5.  **Build Variant**: Select `release`.
6.  **Signature Versions**: Check both **V1 (Jar Signature)** and **V2 (Full APK Signature)** for maximum compatibility.
7.  Click **Finish**.

## 4. Locate and Install
- Android Studio will notify you when the build is finished. Click **Locate** to find the `app-release.apk`.
- Transfer this file to your Android phone.
- **Installation**: Open the file on your phone. If prompted, allow "Install from unknown sources".

## 5. First Launch
- Log in with your Administrative credentials.
- Your device will automatically register for push notifications. You can verify this in the **Console** if you connect a debugger, or by triggering a test event from the web portal.
