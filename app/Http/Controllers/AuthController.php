<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

/**
 * @group Authentication
 * 
 * APIs for user authentication and account management
 */
class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['login', 'register']]);
    }

    /**
     * User Registration
     * 
     * Register a new user account and receive an authentication token.
     * 
     * @bodyParam name string required The user's full name. Example: John Doe
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam password string required The user's password (minimum 6 characters). Example: password123
     * @bodyParam password_confirmation string required Password confirmation. Example: password123
     * 
     * @response 201 scenario="success" {
     *  "status": "success",
     *  "message": "User successfully registered",
     *  "user": {
     *    "id": 1,
     *    "name": "John Doe",
     *    "email": "john@example.com",
     *    "email_verified_at": null,
     *    "created_at": "2025-01-15T10:30:00.000000Z",
     *    "updated_at": "2025-01-15T10:30:00.000000Z"
     *  },
     *  "authorization": {
     *    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
     *    "type": "bearer"
     *  }
     * }
     * 
     * @response 422 scenario="validation error" {
     *  "errors": {
     *    "email": ["The email has already been taken."]
     *  }
     * }
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = auth()->login($user);

        return response()->json([
            'status' => 'success',
            'message' => 'User successfully registered',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ], 201);
    }

    /**
     * User Login
     * 
     * Authenticate user with email and password to get JWT token.
     * 
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam password string required The user's password. Example: password123
     * 
     * @response 200 scenario="success" {
     *  "status": "success",
     *  "message": "Login successful",
     *  "user": {
     *    "id": 1,
     *    "name": "John Doe",
     *    "email": "john@example.com",
     *    "email_verified_at": null,
     *    "created_at": "2025-01-15T10:30:00.000000Z",
     *    "updated_at": "2025-01-15T10:30:00.000000Z"
     *  },
     *  "authorization": {
     *    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
     *    "type": "bearer"
     *  }
     * }
     * 
     * @response 401 scenario="invalid credentials" {
     *  "error": "Invalid credentials"
     * }
     * 
     * @response 422 scenario="validation error" {
     *  "errors": {
     *    "email": ["The email field is required."]
     *  }
     * }
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'user' => auth()->user(),
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    /**
     * Get User Profile
     * 
     * Get the authenticated user's profile information.
     * 
     * @authenticated
     * 
     * @response 200 scenario="success" {
     *  "status": "success",
     *  "user": {
     *    "id": 1,
     *    "name": "John Doe",
     *    "email": "john@example.com",
     *    "email_verified_at": null,
     *    "created_at": "2025-01-15T10:30:00.000000Z",
     *    "updated_at": "2025-01-15T10:30:00.000000Z"
     *  }
     * }
     * 
     * @response 401 scenario="unauthenticated" {
     *  "message": "Unauthenticated."
     * }
     */
    public function me()
    {
        return response()->json([
            'status' => 'success',
            'user' => auth()->user()
        ]);
    }

    /**
     * User Logout
     * 
     * Logout the authenticated user and invalidate the JWT token.
     * 
     * @authenticated
     * 
     * @response 200 scenario="success" {
     *  "status": "success",
     *  "message": "Successfully logged out"
     * }
     * 
     * @response 401 scenario="unauthenticated" {
     *  "message": "Unauthenticated."
     * }
     */
    public function logout()
    {
        auth()->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Refresh JWT Token
     * 
     * Refresh the JWT token to extend session time.
     * 
     * @authenticated
     * 
     * @response 200 scenario="success" {
     *  "status": "success",
     *  "user": {
     *    "id": 1,
     *    "name": "John Doe",
     *    "email": "john@example.com",
     *    "email_verified_at": null,
     *    "created_at": "2025-01-15T10:30:00.000000Z",
     *    "updated_at": "2025-01-15T10:30:00.000000Z"
     *  },
     *  "authorization": {
     *    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
     *    "type": "bearer"
     *  }
     * }
     * 
     * @response 401 scenario="unauthenticated" {
     *  "message": "Unauthenticated."
     * }
     */
    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => auth()->user(),
            'authorization' => [
                'token' => auth()->refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
}
