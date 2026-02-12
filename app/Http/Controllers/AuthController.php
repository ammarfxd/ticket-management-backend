<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' =>  $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => [
                'id'=> $user->id,
                'name'=> $user->name,
                'email'=> $user->email,
                'role'=> $user->role,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email',$data['email'])->first();

        if(!$user || !Hash::check($data['password'],$user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id'=> $user->id,
                'name'=> $user->name,
                'email'=> $user->email,
                'role'=> $user->role,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);

    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ], 200);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => [
            'id'=> $request->user()->id,
            'name'=> $request->user()->name,
            'email'=> $request->user()->email,
            'role'=> $request->user()->role,
            ],
        ], 200);
    }
}
