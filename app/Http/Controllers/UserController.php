<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'nullable|integer',
        ]);
        $perPage = $validated['per_page'] ?? 10;
        $users = User::paginate($perPage);
        return UserResource::collection($users);
    }

   public function show(User $user)
   {
    return new UserResource($user);
   }

   public function update(Request $request,$user)
   {
    $user = User::findOrFail($user);
    try{
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
        ], [
            'email.unique' => 'The email address is already taken by another user.'
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    }
    $user->update($validated);

    return response()->json([
        'message' => 'User updated successfully' ,
        'data' => new UserResource($user)
    ], 200);
   }

    
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json([
            'message' => 'User deleted successfully'
        ], 200);
    }
}
