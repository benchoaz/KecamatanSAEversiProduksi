<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        try {
            $credentials = $request->validate([
                'username' => ['required', 'string'],
                'password' => ['required'],
            ]);

            $throttleKey = Str::transliterate(Str::lower($request->input('username')).'|'.$request->ip());

            if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
                $seconds = RateLimiter::availableIn($throttleKey);
                throw ValidationException::withMessages([
                    'username' => trans('auth.throttle', [
                        'seconds' => $seconds,
                        'minutes' => ceil($seconds / 60),
                    ]),
                ]);
            }

            if (Auth::attempt($credentials)) {
                RateLimiter::clear($throttleKey);
                $request->session()->regenerate();
                $request->session()->regenerateToken(); // Ensure fresh CSRF token

                // Prevent redirects to admin panel to avoid 419 errors
                $intendedUrl = $request->session()->pull('url.intended', route('dashboard'));
                if (str_contains($intendedUrl, '/admin/')) {
                    $intendedUrl = route('dashboard');
                }

                return redirect($intendedUrl);
            }

            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors([
                'username' => 'Username atau password salah.',
            ])->onlyInput('username');
        } catch (\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage(), [
                'username' => $request->input('username'),
                'ip' => $request->ip(),
            ]);

            return back()->withErrors([
                'username' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
            ])->onlyInput('username');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/')->with('logout_success', true);
    }
}
