<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new UserCollection(User::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $user = User::create($request->only(['name', 'email', 'password', 'phone']));

        return response()->json([
            'message' => 'User created successfully.',
            'data'    => UserResource::make($user),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return UserResource::make($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $user->update($request->only(['name', 'email', 'password', 'phone']));

        return response()->json([
            'message' => 'User updated successfully.',
            'data'    => UserResource::make($user),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        $userName = $user->name;
        $user->delete();

        return response()->json([
            'message' => "User '{$userName} deleted successfully.",
        ], 200);
    }
}