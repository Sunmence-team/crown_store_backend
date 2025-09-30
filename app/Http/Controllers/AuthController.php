<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Load credentials from services config
        $adminUsername   = config('services.admin.username');
        $adminPassword   = config('services.admin.password');

        $salesRepUsername = config('services.sales_rep.username');
        $salesRepPassword = config('services.sales_rep.password');

        // Check if user is admin
        if ($request->username === $adminUsername && $request->password === $adminPassword) {
            $role = 'admin';
            $name = 'Administrator';
            $email = 'admin@gmail.com';
            $username = $adminUsername;
            $password = $adminPassword;
        }
        // Check if user is sales_rep
        elseif ($request->username === $salesRepUsername && $request->password === $salesRepPassword) {
            $role = 'sales_rep';
            $name = 'Sales Representative';
            $email = 'salesrep@gmail.com';
            $username = $salesRepUsername;
            $password = $salesRepPassword;
        }
        else {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Check if user already exists in DB
        $user = User::where('username', $username)->first();

        if (!$user) {
            $user = User::create([
                'username' => $username,
                'name'     => $name,
                'role'     => $role,
                'email'    => $email,
                'password' => Hash::make($password),
            ]);
        }

        // Generate token
        $token = $user->createToken($role . '-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user'    => $user,
            'token'   => $token,
        ]);
    }
}
