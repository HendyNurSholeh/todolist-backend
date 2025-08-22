# Laravel JWT API Test

## Base URL
```
http://127.0.0.1:8000/api
```

## Endpoints

### 1. Register User
**POST** `/auth/register`

**Body (JSON):**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### 2. Login User
**POST** `/auth/login`

**Body (JSON):**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

### 3. Get User Profile (Protected)
**GET** `/auth/me`

**Headers:**
```
Authorization: Bearer {your_jwt_token}
```

### 4. Refresh Token (Protected)
**POST** `/auth/refresh`

**Headers:**
```
Authorization: Bearer {your_jwt_token}
```

### 5. Logout (Protected)
**POST** `/auth/logout`

**Headers:**
```
Authorization: Bearer {your_jwt_token}
```

## Contoh Penggunaan dengan cURL

### Register
```bash
curl -X POST http://127.0.0.1:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com", 
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login
```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Get Profile (gunakan token dari response login)
```bash
curl -X GET http://127.0.0.1:8000/api/auth/me \
  -H "Authorization: Bearer YOUR_JWT_TOKEN_HERE"
```
