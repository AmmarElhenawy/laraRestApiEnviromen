# JWT Authentication Setup

This Laravel API now includes JWT (JSON Web Token) authentication. Here's what has been set up and how to use it.

## What's Been Installed

1. **tymon/jwt-auth** - JWT authentication package for Laravel
2. **JWT Configuration** - Published and configured
3. **JWT Secret Key** - Generated and stored in .env
4. **AuthController** - Handles login, register, logout, refresh, and user profile
5. **JWT Middleware** - Protects routes that require authentication
6. **Updated User Model** - Implements JWTSubject interface
7. **Updated Auth Configuration** - Added JWT guard
8. **Updated Routes** - Authentication endpoints and protected routes

## API Endpoints

### Public Endpoints (No Authentication Required)

#### Register a new user
```
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### Login
```
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

Response:
```json
{
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### Protected Endpoints (Authentication Required)

Include the JWT token in the Authorization header:
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

#### Get user profile
```
GET /api/user-profile
Authorization: Bearer {token}
```

#### Refresh token
```
POST /api/refresh
Authorization: Bearer {token}
```

#### Logout
```
POST /api/logout
Authorization: Bearer {token}
```

#### Protected Post Routes
- `POST /api/post` - Create a new post
- `PATCH /api/updatePost/{id}` - Update a post
- `POST /api/deletePost/{id}` - Delete a post

### Public Post Routes
- `GET /api/post` - Get all posts
- `GET /api/post/{id}` - Get a specific post

## Configuration Files

### JWT Configuration (`config/jwt.php`)
- Token TTL: 60 minutes (configurable via JWT_TTL env variable)
- Refresh TTL: 2 weeks (configurable via JWT_REFRESH_TTL env variable)
- Algorithm: HS256 (configurable via JWT_ALGO env variable)

### Auth Configuration (`config/auth.php`)
- Added JWT guard for API authentication
- Default guard remains 'web' for web routes

## Environment Variables

Make sure these are set in your `.env` file:
```
JWT_SECRET=your_jwt_secret_key_here
JWT_TTL=60
JWT_REFRESH_TTL=20160
JWT_ALGO=HS256
```

## Testing the API

1. **Register a user:**
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123","password_confirmation":"password123"}'
```

2. **Login:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

3. **Access protected route:**
```bash
curl -X GET http://localhost:8000/api/user-profile \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Security Features

- Tokens expire after 60 minutes by default
- Refresh tokens allow extending session without re-authentication
- Invalid/expired tokens return proper HTTP status codes
- Password hashing using Laravel's built-in Hash facade
- Input validation for all authentication endpoints

## Error Responses

- **401 Unauthorized** - Invalid or missing token
- **422 Unprocessable Entity** - Validation errors
- **400 Bad Request** - Invalid request data

## Next Steps

1. Test the authentication endpoints
2. Customize token TTL and refresh settings as needed
3. Add additional user fields if required
4. Implement password reset functionality if needed
5. Add rate limiting for authentication endpoints 