<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show the user profile edit form.
     *
     * @return \Illuminate\View\View
     */
    public function edit(Request $request)
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.max' => 'Nama lengkap maksimal 255 karakter.',
            'foto.image' => 'Foto harus berupa gambar.',
            'foto.mimes' => 'Foto harus berformat jpeg, png, jpg, atau gif.',
            'foto.max' => 'Foto maksimal 2MB.',
        ]);

        $data = [
            'nama_lengkap' => $request->nama_lengkap,
            'no_hp' => $request->no_hp,
        ];

        // Handle foto upload
        if ($request->hasFile('foto')) {
            // Delete old foto if exists
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }
            
            $foto = $request->file('foto');
            $fotoPath = $foto->store('profiles', 'public');
            $data['foto'] = $fotoPath;
        }

        $user->update($data);

        return redirect()->route('profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }

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
