<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Show the password change form.
     *
     * @return \Illuminate\View\View
     */
    public function editPassword(Request $request)
    {
        return view('profile.password');
    }

    /**
     * Update the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8'],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password_confirmation.required' => 'Konfirmasi password baru wajib diisi.',
            'password_confirmation.min' => 'Konfirmasi password baru minimal 8 karakter.',
        ]);

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Password lama tidak cocok dengan password Anda.'])
                ->withInput();
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('profile.password.edit')
            ->with('success', 'Password berhasil diubah. Silakan login kembali dengan password baru Anda.');
    }
}
