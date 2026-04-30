<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $orders = Order::where('user_id', $user->id)->get();

        $totalOrders = $orders->count();
        $pendingOrders = $orders->where('order_status', 'pending')->count();
        $totalSpent = $orders->sum('grand_total');

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
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        'phone' => 'nullable|string|max:30',
        'address' => 'nullable|string|max:1000',
        'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    $avatarPath = $user->avatar;

    if ($request->hasFile('avatar')) {
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $avatarPath = $request->file('avatar')->store('avatars', 'public');
    }

    $user->update([
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'address' => $request->address,
        'avatar' => $avatarPath,
    ]);

    return redirect()->route('settings.index')->with('success', 'Profil berhasil diperbarui.');
}
}