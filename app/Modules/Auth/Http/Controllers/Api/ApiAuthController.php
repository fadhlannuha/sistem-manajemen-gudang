<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Api;

use App\Modules\Auth\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Auth\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class ApiAuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->string('email')->toString())->first();

        if ($user === null || ! Hash::check($request->string('password')->toString(), $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $roles = $user->roles()->pluck('name')->all();

        $token = $user->createToken(
            name: 'api-'.$user->id,
            abilities: $roles,
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $this->userPayload($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($this->userPayload($request->user()));
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * @return array{id: int, name: string, email: string, roles: array<int, string>}
     */
    private function userPayload(User $user): array
    {
        $user->loadMissing('roles:id,name');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name')->all(),
        ];
    }
}
