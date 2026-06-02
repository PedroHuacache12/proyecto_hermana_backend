<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function checkSuperAdmin(Request $request)
    {
        if (!$request->user()->isSuperAdmin()) abort(403, 'Solo superadmin.');
    }

    public function index(Request $request)
    {
        $this->checkSuperAdmin($request);
        return response()->json(User::orderBy('created_at')->get(['id', 'name', 'email', 'whatsapp_number', 'role', 'created_at']));
    }

    public function store(Request $request)
    {
        $this->checkSuperAdmin($request);
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users',
            'password'        => 'required|string|min:8',
            'whatsapp_number' => 'nullable|string|max:20',
            'role'            => 'in:superadmin,user',
        ]);
        $user = User::create([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'password'        => Hash::make($data['password']),
            'whatsapp_number' => $data['whatsapp_number'] ?? null,
            'role'            => $data['role'] ?? 'user',
        ]);
        return response()->json($user, 201);
    }

    public function update(Request $request, User $user)
    {
        $this->checkSuperAdmin($request);
        $data = $request->validate([
            'name'            => 'sometimes|string|max:255',
            'whatsapp_number' => 'nullable|string|max:20',
            'role'            => 'in:superadmin,user',
            'password'        => 'nullable|string|min:8',
        ]);
        if (isset($data['password'])) $data['password'] = Hash::make($data['password']);
        else unset($data['password']);
        $user->update($data);
        return response()->json($user);
    }

    public function destroy(Request $request, User $user)
    {
        $this->checkSuperAdmin($request);
        if ($user->id === $request->user()->id) abort(400, 'No puedes eliminarte a ti mismo.');
        $user->delete();
        return response()->json(null, 204);
    }
}
