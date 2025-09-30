<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $adminUsername = config('services.admin.username');
        $adminPassword = config('services.admin.password');

        if ($request->username === $adminUsername && $request->password === $adminPassword) {
            $role = 'admin';
            $name = 'Administrator';
            $email = 'admin@gmail.com';
            $username = $adminUsername;
            $password = $adminPassword;

            $user = User::firstOrCreate(
                ['username' => $username],
                [
                    'name'     => $name,
                    'role'     => $role,
                    'email'    => $email,
                    'password' => Hash::make($password),
                ]
            );
        } else {
            $user = User::where('username', $request->username)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }
        }

        $token = $user->createToken($user->role . '-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user'    => $user,
            'token'   => $token,
        ]);
    }

    public function updateSalesRep(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'username' => 'sometimes|string|unique:users,username,' . $id,
            'email'    => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:6',
        ]);

        $salesRep = User::where('id', $id)->where('role', 'sales_rep')->first();

        if (!$salesRep) {
            return response()->json(['message' => 'Sales rep not found'], 404);
        }

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $salesRep->update($validated);

        return response()->json([
            'message' => 'Sales rep updated successfully',
            'sales_rep' => $salesRep,
        ]);
    }


}
