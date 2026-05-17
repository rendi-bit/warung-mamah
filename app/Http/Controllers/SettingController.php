<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ✅ FIX #4: Gunakan query agregat langsung — tidak load semua order ke memory
        $totalOrders   = Order::where('user_id', $user->id)->count();
        $pendingOrders = Order::where('user_id', $user->id)
            ->where('order_status', 'pending')
            ->count();
        $totalSpent    = Order::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->sum('grand_total');

        return view('settings.index', compact(
            'user',
            'totalOrders',
            'pendingOrders',
            'totalSpent'
        ));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string|max:1000',
            'avatar'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $avatarPath = $user->avatar;

        if ($request->hasFile('avatar')) {
            // Hapus avatar lama jika ada
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update([
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'address' => $request->address,
            'avatar'  => $avatarPath,
        ]);

        return redirect()->route('settings.index')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    // ✅ FIX #2: Tambah method updatePassword yang sebelumnya tidak ada
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password'      => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'password.required'         => 'Password baru wajib diisi.',
            'password.min'              => 'Password baru minimal 8 karakter.',
            'password.confirmed'        => 'Konfirmasi password baru tidak cocok.',
        ]);

        // Cek apakah password lama benar
        if (!Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Password lama yang kamu masukkan salah.'])
                ->withInput();
        }

        // Cegah pakai password yang sama
        if (Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors(['password' => 'Password baru tidak boleh sama dengan password lama.'])
                ->withInput();
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('settings.index')
            ->with('success', 'Password berhasil diperbarui. Silakan login ulang jika diperlukan.');
    }
}