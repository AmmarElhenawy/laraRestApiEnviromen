# دليل إعداد JWT Authentication في Laravel 12+

هذا الدليل شامل لإضافة JWT (JSON Web Token) authentication إلى أي Laravel API.

## 📋 المتطلبات الأساسية

- Laravel 12+
- PHP 8.2+
- Composer

## 🚀 خطوات الإعداد

### 1. تثبيت JWT Package

```bash
composer require tymon/jwt-auth
```

### 2. نشر ملفات التكوين

```bash
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
```

### 3. إنشاء JWT Secret Key

```bash
php artisan jwt:secret
```

### 4. تسجيل JWT Provider

أضف السطر التالي إلى `bootstrap/providers.php`:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    Tymon\JWTAuth\Providers\LaravelServiceProvider::class, // أضف هذا السطر
];
```

### 5. إعداد Auth Configuration

أضف JWT guard إلى `config/auth.php`:

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => 'jwt',        // أضف هذا
        'provider' => 'users',
    ],
],
```

### 6. تحديث User Model

أضف JWTSubject interface إلى `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
```

### 7. إنشاء JWT Middleware

```bash
php artisan make:middleware JwtMiddleware
```

ثم أضف الكود التالي إلى `app/Http/Middleware/JwtMiddleware.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token not valid'], 401);
        }

        return $next($request);
    }
}
```

### 8. تسجيل Middleware

أضف إلى `bootstrap/app.php`:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt' => \App\Http\Middleware\JwtMiddleware::class, // أضف هذا
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
```

### 9. إنشاء AuthController

```bash
php artisan make:controller AuthController
```

ثم أضف الكود التالي إلى `app/Http/Controllers/AuthController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    // User registration
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user','token'), 201);
    }

    // User login
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user = JWTAuth::user();

            return response()->json(compact('token', 'user'));
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }

    // Get authenticated user
    public function userProfile()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }

        return response()->json(compact('user'));
    }

    // User logout
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not logout'], 500);
        }
    }

    // Refresh token
    public function refresh()
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            return response()->json(compact('token'));
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not refresh token'], 500);
        }
    }
}
```

### 10. إعداد Routes

أضف إلى `routes/api.php`:

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Public Auth Routes
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Protected Routes
Route::middleware('jwt')->group(function () {
    Route::get('user-profile', [AuthController::class, 'userProfile']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    
    // أضف هنا الطرق المحمية الأخرى
    // Route::post('/posts', [PostController::class, 'store']);
    // Route::put('/posts/{id}', [PostController::class, 'update']);
    // Route::delete('/posts/{id}', [PostController::class, 'destroy']);
});

// Public Routes
// Route::get('/posts', [PostController::class, 'index']);
// Route::get('/posts/{id}', [PostController::class, 'show']);
```

## 🔧 متغيرات البيئة المطلوبة

أضف إلى ملف `.env`:

```env
JWT_SECRET=your_jwt_secret_key_here
JWT_TTL=60
JWT_REFRESH_TTL=20160
JWT_ALGO=HS256
```

## 📡 API Endpoints

### Public Endpoints (لا تحتاج authentication)

#### Register
```
POST /api/register
Content-Type: application/json

{
    "name": "User Name",
    "email": "user@example.com",
    "password": "password123"
}
```

#### Login
```
POST /api/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password123"
}
```

### Protected Endpoints (تحتاج JWT token)

#### Get User Profile
```
GET /api/user-profile
Authorization: Bearer {your_jwt_token}
```

#### Logout
```
POST /api/logout
Authorization: Bearer {your_jwt_token}
```

#### Refresh Token
```
POST /api/refresh
Authorization: Bearer {your_jwt_token}
```

## 🧪 اختبار API

### باستخدام PHP
```php
<?php
// test_api.php

// Register
$url = 'http://localhost:8000/api/register';
$data = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123'
];

$options = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);
echo $result;
```

### باستخدام cURL
```bash
# Register
curl -X POST "http://localhost:8000/api/register" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123"}'

# Login
curl -X POST "http://localhost:8000/api/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'

# Get Profile (with token)
curl -X GET "http://localhost:8000/api/user-profile" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### باستخدام Postman
1. أرسل POST request إلى `/api/register` مع JSON body
2. أرسل POST request إلى `/api/login` مع JSON body
3. استخدم الـ token في Authorization header للطرق المحمية

## ⚙️ إعدادات JWT (اختيارية)

يمكنك تخصيص إعدادات JWT في `config/jwt.php`:

```php
// مدة صلاحية الـ token (بالدقائق)
'ttl' => env('JWT_TTL', 60),

// مدة صلاحية الـ refresh token (بالدقائق)
'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),

// خوارزمية التشفير
'algo' => env('JWT_ALGO', Tymon\JWTAuth\Providers\JWT\Provider::ALGO_HS256),
```

## 🔒 إضافة Custom Claims (اختياري)

يمكنك إضافة معلومات إضافية للـ token:

```php
// في AuthController
public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    try {
        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user = JWTAuth::user();
        
        // إضافة custom claims
        $token = JWTAuth::claims([
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role ?? 'user'
        ])->fromUser($user);

        return response()->json(compact('token', 'user'));
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }
}
```

## 🚨 استكشاف الأخطاء

### مشاكل شائعة وحلولها:

1. **خطأ "Token not valid"**
   - تأكد من إرسال الـ token في Authorization header
   - تأكد من أن الـ token لم ينتهي صلاحيته

2. **خطأ "Could not create token"**
   - تأكد من أن JWT_SECRET موجود في .env
   - تأكد من أن User model implements JWTSubject

3. **خطأ "User not found"**
   - تأكد من أن المستخدم موجود في قاعدة البيانات
   - تأكد من صحة الـ credentials

4. **خطأ 500 Internal Server Error**
   - تحقق من سجلات الأخطاء في `storage/logs/laravel.log`
   - تأكد من أن جميع الملفات تم تحديثها بشكل صحيح

## 📝 ملاحظات مهمة

1. **الأمان**: تأكد من استخدام HTTPS في الإنتاج
2. **Token Storage**: لا تخزن الـ token في localStorage (غير آمن)
3. **Token Expiration**: استخدم refresh tokens لتجديد الـ access tokens
4. **Rate Limiting**: أضف rate limiting للـ authentication endpoints
5. **Validation**: أضف validation قوي لجميع المدخلات

## 🎯 الخطوات التالية

1. إضافة password reset functionality
2. إضافة email verification
3. إضافة role-based authorization
4. إضافة rate limiting
5. إضافة API documentation (Swagger/OpenAPI)

---

**ملاحظة**: هذا الدليل مخصص لـ Laravel 12+. إذا كنت تستخدم إصدار أقدم، قد تحتاج لتعديل بعض الخطوات. 