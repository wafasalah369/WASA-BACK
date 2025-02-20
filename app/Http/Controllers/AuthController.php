<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function index()
    {
        $users = User::all();
        return response()->json($users, 200);
    }
    public function register(Request $request)
    {
    
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',  
        ]);
        
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
    
        return response()->json([  
            'user' => $user,
            'token' => $user->createToken('API Token')->plainTextToken,
        ], 201);
    }
    

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        return response()->json(['user' => $user, 'token' => $user->createToken('API Token')->plainTextToken]);
    }
    
    public function show($id)
    {
        $user = User::find($id);
        if(!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user, 200);
        
    }
    public function update (Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
           'name' => 'sometimes|string|max:255',
           'email' => 'sometimes|email|unique:users,email, .$user->id',
         
        ]);
        $user->update($validated);

        return response()->json([
             'message' => 'User updated successfully',
             'user' => $user], 200);
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
