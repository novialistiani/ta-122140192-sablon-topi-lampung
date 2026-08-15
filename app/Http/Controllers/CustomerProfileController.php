<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\EmailChangeRequest;
use App\Mail\EmailChangeConfirmation;

class CustomerProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $user->load('addresses');
        return view('pages.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'province' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
        ]);

        // Check if email is being changed
        if ($validated['email'] !== $user->email) {
            // Delete any existing pending email change requests
            EmailChangeRequest::where('user_id', $user->id)
                ->where('is_confirmed', false)
                ->delete();

            // Create new email change request
            $emailChangeRequest = EmailChangeRequest::create([
                'user_id' => $user->id,
                'old_email' => $user->email,
                'new_email' => $validated['email'],
                'token' => Str::random(64),
                'expires_at' => now()->addHours(24),
            ]);

            // Send confirmation email to OLD email address
            Mail::to($user->email)->send(new EmailChangeConfirmation($emailChangeRequest));

            // Remove email from update array - don't change it yet
            unset($validated['email']);

            // Update other fields
            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Profile berhasil diperbarui. Email konfirmasi telah dikirim ke ' . $user->email . '. Silakan cek email Anda untuk mengonfirmasi perubahan email.',
                'email_change_pending' => true,
                'user' => $user
            ]);
        }

        // Update normally if email is not changed
        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Confirm email change
     */
    public function confirmEmailChange($token)
    {
        $request = EmailChangeRequest::where('token', $token)
            ->where('is_confirmed', false)
            ->first();

        if (!$request) {
            return redirect()->route('profile')->with('error', 'Link konfirmasi tidak valid atau sudah digunakan.');
        }

        if ($request->isExpired()) {
            return redirect()->route('profile')->with('error', 'Link konfirmasi sudah kadaluarsa. Silakan ubah email kembali.');
        }

        // Update user email
        $request->user->update(['email' => $request->new_email]);

        // Mark request as confirmed
        $request->update(['is_confirmed' => true]);

        return redirect()->route('profile')->with('success', 'Email berhasil diubah! Anda sekarang dapat login menggunakan email baru.');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048'], // 2MB max
        ]);

        $user = Auth::user();

        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars/customers', 'public');
        
        $user->update(['avatar' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Avatar updated successfully',
            'avatar_url' => Storage::url($path)
        ]);
    }

    public function deleteAvatar()
    {
        $user = Auth::user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Avatar deleted successfully'
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    }

    /**
     * Store new address
     */
    public function storeAddress(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'label' => ['nullable', 'string', 'max:100'],
            'is_primary' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
        
        // If this is set as primary, unset other primary addresses
        if ($validated['is_primary'] ?? false) {
            \App\Models\CustomerAddress::where('user_id', $user->id)
                ->update(['is_primary' => false]);
        }
        
        $address = $user->addresses()->create($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil ditambahkan',
            'address' => $address
        ]);
    }

    /**
     * Update existing address
     */
    public function updateAddress(Request $request, $id)
    {
        $user = Auth::user();
        
        $address = \App\Models\CustomerAddress::where('user_id', $user->id)
            ->findOrFail($id);
        
        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'label' => ['nullable', 'string', 'max:100'],
            'is_primary' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
        
        // If this is set as primary, unset other primary addresses
        if ($validated['is_primary'] ?? false) {
            \App\Models\CustomerAddress::where('user_id', $user->id)
                ->where('id', '!=', $id)
                ->update(['is_primary' => false]);
        }
        
        $address->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil diperbarui',
            'address' => $address
        ]);
    }

    /**
     * Delete address
     */
    public function deleteAddress($id)
    {
        $user = Auth::user();
        
        $address = \App\Models\CustomerAddress::where('user_id', $user->id)
            ->findOrFail($id);
        
        $address->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil dihapus'
        ]);
    }

    /**
     * Set address as primary
     */
    public function setPrimaryAddress($id)
    {
        $user = Auth::user();
        
        $address = \App\Models\CustomerAddress::where('user_id', $user->id)
            ->findOrFail($id);
        
        // Unset all other primary addresses
        \App\Models\CustomerAddress::where('user_id', $user->id)
            ->update(['is_primary' => false]);
        
        // Set this address as primary
        $address->update(['is_primary' => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'Alamat utama berhasil diatur'
        ]);
    }
}
