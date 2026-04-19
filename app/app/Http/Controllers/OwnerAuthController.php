<?php

namespace App\Http\Controllers;

use App\Models\UmkmLocal;
use App\Models\Loker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OwnerAuthController extends Controller
{
    /**
     * Show login form for business owners
     */
    public function login()
    {
        return view('public.owner.login');
    }

    /**
     * Handle owner login by phone number
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'pin' => 'required|string|size:6',
        ]);

        // Normalize phone number
        $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);

        // Try to find owner by phone in UmkmLocal or Loker
        $umkm = UmkmLocal::where('contact_wa', 'LIKE', "%{$cleanPhone}%")->first();
        $loker = Loker::where('contact_wa', 'LIKE', "%{$cleanPhone}%")->first();

        if (!$umkm && !$loker) {
            return back()->withErrors([
                'phone' => 'Nomor WhatsApp tidak ditemukan. Pastikan nomor yang Anda masukkan sesuai dengan yang terdaftar.'
            ])->withInput();
        }

        // Verify PIN for each record
        $verifiedUmkm = null;
        $verifiedLoker = null;

        if ($umkm && Hash::check($request->pin, $umkm->owner_pin)) {
            $verifiedUmkm = $umkm;
        }

        if ($loker && Hash::check($request->pin, $loker->owner_pin)) {
            $verifiedLoker = $loker;
        }

        if (!$verifiedUmkm && !$verifiedLoker) {
            return back()->withErrors([
                'pin' => 'PIN yang Anda masukkan salah. Ketik LUPA PIN jika lupa.'
            ])->withInput();
        }

        // Store owner data in session
        session([
            'owner_logged_in' => true,
            'owner_phone' => $cleanPhone,
            'owner_umkm_id' => $verifiedUmkm?->id,
            'owner_loker_id' => $verifiedLoker?->id,
        ]);

        return redirect()->route('owner.dashboard');
    }

    /**
     * Show owner dashboard
     */
    public function dashboard()
    {
        if (!session('owner_logged_in')) {
            return redirect()->route('owner.login');
        }

        $umkm = null;
        $loker = null;

        if (session('owner_umkm_id')) {
            $umkm = UmkmLocal::find(session('owner_umkm_id'));
        }

        if (session('owner_loker_id')) {
            $loker = Loker::find(session('owner_loker_id'));
        }

        return view('public.owner.dashboard', compact('umkm', 'loker'));
    }

    /**
     * Show reset PIN form
     */
    public function showResetPin()
    {
        if (!session('owner_logged_in')) {
            return redirect()->route('owner.login');
        }

        return view('public.owner.reset-pin');
    }

    /**
     * Handle PIN reset
     */
    public function resetPin(Request $request)
    {
        if (!session('owner_logged_in')) {
            return redirect()->route('owner.login');
        }

        $request->validate([
            'new_pin' => 'required|string|size:6|confirmed',
        ]);

        $newPinHash = Hash::make($request->new_pin);

        // Update PIN for UmkmLocal
        if (session('owner_umkm_id')) {
            $umkm = UmkmLocal::find(session('owner_umkm_id'));
            if ($umkm) {
                $umkm->update([
                    'owner_pin' => $newPinHash,
                    'last_toggle_at' => now(),
                ]);
            }
        }

        // Update PIN for Loker
        if (session('owner_loker_id')) {
            $loker = Loker::find(session('owner_loker_id'));
            if ($loker) {
                $loker->update([
                    'owner_pin' => $newPinHash,
                    'last_toggle_at' => now(),
                ]);
            }
        }

        return redirect()->route('owner.dashboard')
            ->with('success', 'PIN berhasil diperbarui. PIN baru Anda sekarang aktif.');
    }

    /**
     * Toggle active status (for UmkmLocal)
     */
    public function toggleUmkm(Request $request)
    {
        if (!session('owner_logged_in') || !session('owner_umkm_id')) {
            return redirect()->route('owner.login');
        }

        $umkm = UmkmLocal::find(session('owner_umkm_id'));
        if (!$umkm) {
            return redirect()->route('owner.dashboard');
        }

        $newStatus = !$umkm->is_active;
        $umkm->update([
            'is_active' => $newStatus,
            'last_toggle_at' => now(),
        ]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('owner.dashboard')
            ->with('success', "UMKM/Jasa Anda berhasil {$statusText}.");
    }

    /**
     * Toggle status (for Loker)
     */
    public function toggleLoker(Request $request)
    {
        if (!session('owner_logged_in') || !session('owner_loker_id')) {
            return redirect()->route('owner.login');
        }

        $loker = Loker::find(session('owner_loker_id'));
        if (!$loker) {
            return redirect()->route('owner.dashboard');
        }

        $newStatus = ($loker->status === 'aktif') ? 'nonaktif' : 'aktif';
        $loker->update([
            'status' => $newStatus,
            'last_toggle_at' => now(),
        ]);

        $statusText = ($newStatus === 'aktif') ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('owner.dashboard')
            ->with('success', "Lowongan kerja Anda berhasil {$statusText}.");
    }

    /**
     * Logout owner
     */
    public function logout()
    {
        session()->forget([
            'owner_logged_in',
            'owner_phone',
            'owner_umkm_id',
            'owner_loker_id',
        ]);

        return redirect()->route('owner.login')
            ->with('success', 'Anda telah keluar dari dashboard.');
    }

    /**
     * Show forgot PIN page
     */
    public function forgotPin()
    {
        return view('public.owner.forgot-pin');
    }

    /**
     * Request PIN reset via WhatsApp
     */
    public function requestPinReset(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);

        // Check if phone exists in system
        $umkm = UmkmLocal::where('contact_wa', 'LIKE', "%{$cleanPhone}%")->first();
        $loker = Loker::where('contact_wa', 'LIKE', "%{$cleanPhone}%")->first();

        if (!$umkm && !$loker) {
            return back()->withErrors([
                'phone' => 'Nomor WhatsApp tidak ditemukan di sistem kami.'
            ])->withInput();
        }

        // Get the actual stored phone number (from whichever record was found)
        $storedPhone = $umkm?->contact_wa ?? $loker?->contact_wa ?? $cleanPhone;
        // Clean stored phone for display (strip non-numeric)
        $storedPhoneClean = preg_replace('/[^0-9]/', '', $storedPhone);
        // Normalize to 0-prefix for Indonesian display (0812xxx not 6282xxx)
        if (str_starts_with($storedPhoneClean, '62')) {
            $storedPhoneClean = '0' . substr($storedPhoneClean, 2);
        }
        // Mask middle digits for privacy: 082345678901 → 0823****8901
        $maskedPhone = $storedPhoneClean;
        if (strlen($storedPhoneClean) >= 8) {
            $visible = 4;
            $maskedPhone = substr($storedPhoneClean, 0, $visible)
                . str_repeat('*', max(0, strlen($storedPhoneClean) - ($visible + 4)))
                . substr($storedPhoneClean, -4);
        }

        // For now, show instructions to contact admin
        // In future, could implement OTP-based reset
        $adminWa = appProfile()->whatsapp_complaint ?? appProfile()->phone ?? '6281234567890';

        return view('public.owner.pin-reset-requested', [
            'phone' => $maskedPhone,          // masked for display
            'phoneRaw' => $storedPhoneClean,      // raw for WA link
            'adminWa' => $adminWa,
        ]);
    }
}
