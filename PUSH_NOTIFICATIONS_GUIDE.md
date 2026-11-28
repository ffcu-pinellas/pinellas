# Push Notifications Implementation Guide

## 1. Get Provider Credentials
- Firebase (FCM) for Android/Web, APNs for iOS.
- Create project, download service account JSON and APNs key.

## 2. Database
- Create `push_device_tokens` table: id, user_id, platform, token, created_at, updated_at.

## 3. Backend
- Add model and migration.
- Add service to send via FCM/APNs.
- Add routes to register/revoke tokens.

## 4. Frontend
- Request notification permission.
- Register service worker.
- Send token to backend.

## 5. Testing
- Test on real devices.
- Verify delivery and failure handling.

## 6. Deployment
- Add env vars for credentials.
- Run migrations.
- Monitor logs.
```
filePath">
C:\Users\USER\Downloads\frontfield-remodel pinelas fcu\PUSH_NOTIFICATIONS_GUIDE.md