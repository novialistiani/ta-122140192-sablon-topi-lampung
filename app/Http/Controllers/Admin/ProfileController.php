<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Display admin profile page
     */
    public function index()
    {
        $admin = auth('admin')->user();
        return view('admin.profile', compact('admin'));
    }

    /**
     * Update avatar only
     */
    public function updateAvatar(Request $request)
    {
        $admin = auth('admin')->user();

        $validated = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Delete old avatar if exists
        if ($admin->avatar && Storage::disk('public')->exists($admin->avatar)) {
            Storage::disk('public')->delete($admin->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars/admins', 'public');
        $admin->avatar = $path;
        $admin->save();

        // Log activity
        Admin::logActivity('update', 'Updated profile avatar');

        return back()->with('success', 'Profile picture updated successfully!');
    }

    /**
     * Update admin profile (name and password)
     */
    public function update(Request $request)
    {
        $admin = auth('admin')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'current_password' => 'required_with:password',
            'password' => ['nullable', 'confirmed', Password::min(6)],
        ]);

        // Verify current password if changing password
        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $admin->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect']);
            }
            $admin->password = Hash::make($request->password);
        }

        // Update name
        $admin->name = $validated['name'];
        $admin->save();

        // Log activity
        Admin::logActivity('update', 'Updated own profile');

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Delete avatar
     */
    public function deleteAvatar()
    {
        $admin = auth('admin')->user();

        if ($admin->avatar && Storage::disk('public')->exists($admin->avatar)) {
            Storage::disk('public')->delete($admin->avatar);
        }

        $admin->avatar = null;
        $admin->save();

        // Log activity
        Admin::logActivity('update', 'Deleted profile avatar');

        return back()->with('success', 'Avatar deleted successfully!');
    }
}
