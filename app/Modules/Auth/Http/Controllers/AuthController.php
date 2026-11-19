<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Modules\Auth\Events\UserRegistered;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Auth\Http\Requests\RegisterRequest;
use App\Modules\Auth\Models\Role;
use App\Modules\Auth\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth::auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard.index'));
    }

    public function showRegister(): View
    {
        return view('auth::auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request): User {
            $user = User::query()->create([
                'name' => $request->string('name')->toString(),
                'email' => $request->string('email')->toString(),
                'password' => $request->string('password')->toString(),
            ]);

            $role = Role::query()->firstOrCreate(
                ['name' => 'staff_gudang'],
                ['label' => 'Staff Gudang'],
            );

            $user->roles()->attach($role);

            return $user;
        });

        UserRegistered::dispatch(
            userId: $user->id,
            name: $user->name,
            email: $user->email,
            roles: $user->roles()->pluck('name')->all(),
        );

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard.index')
            ->with('status', 'Registrasi berhasil. Selamat datang di Sistem Manajemen Gudang.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Anda telah logout.');
    }
}
