<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;

layout('layouts.auth');

state([
    'name' => '',
    'email' => '',
    'password' => '',
    'password_confirmation' => '',
    'gender' => '',
    'phone' => ''
]);

rules([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
    'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
    'gender' => ['required', 'in:male,female'],
    'phone' => ['required', 'string', 'regex:/^(\+62|62|0)[0-9]{9,12}$/']
]);

$register = function () {
    try {
        $validated = $this->validate();

        // Hash password sebelum disimpan
        $validated['password'] = Hash::make($validated['password']);

        // Buat user baru
        $user = User::create($validated);

        // Trigger event registered
        event(new Registered($user));

        // Redirect ke dashboard
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Validation error akan ditangani otomatis oleh Livewire
        throw $e;
    } catch (\Exception $e) {
        // Log error untuk debugging
        \Log::error('Registration error: ' . $e->getMessage());
        
        session()->flash('error', 'Gagal membuat akun. Silakan coba lagi. Error: ' . $e->getMessage());
    }
};
?>

<div style="min-height: 100vh; background-color: #f5f5f5; display: flex; flex-direction: column;">
    <!-- Top Bar -->
    <div style="background-color: #1a2942; padding: 12px 40px; display: flex; justify-content: flex-end; align-items: center;">
        <a href="{{ route('admin.login') }}" style="color: #fff; text-decoration: none; display: flex; align-items: center; gap: 6px; font-size: 13px; border-left: 2px solid rgba(255, 255, 255, 0.3); padding-left: 16px;">
            <i class="fas fa-lock"></i>
            <span>Admin Login</span>
        </a>
    </div>

    <!-- Content -->
    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 30px 20px;">
        <!-- Logo Section -->
        <div style="text-align: center; margin-bottom: 28px;">
            <div style="width: 64px; height: 64px; background-color: transparent; border-radius: 50%; border: 3px solid #000; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <i class="fas fa-shopping-bag" style="font-size: 28px; color: #000; font-weight: bold;"></i>
            </div>
            <div style="font-size: 32px; font-weight: 900; color: #ffc107; letter-spacing: 1.5px;">LGI STORE</div>
            <div style="font-size: 10px; color: #333; font-weight: 600; margin-top: 4px; letter-spacing: 0.8px;">PEDULI KUALITAS, BUKAN KUANTITAS</div>
        </div>

        <!-- Register Card -->
        <div style="background-color: #fff; border-radius: 16px; padding: 40px 50px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); width: 100%; max-width: 700px;">
            
            <!-- Error Notification -->
            @if (session('error'))
                <div class="mb-6 bg-red-500 bg-opacity-20 border border-red-500 text-red-300 px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form wire:submit="register" style="margin: 0;">
                <!-- Name Row -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px;">
                    <div>
                        <input wire:model="name" type="text" placeholder="Nama Depan" required
                               style="width: 100%; padding: 12px 16px; border: 1px solid #d0d0d0; border-radius: 8px; font-size: 14px;">
                        <x-input-error :messages="$errors->get('name')" style="margin-top: 8px; color: #dc2626; font-size: 13px;" />
                    </div>
                    <div>
                        <input type="text" placeholder="Nama Belakang"
                               style="width: 100%; padding: 12px 16px; border: 1px solid #d0d0d0; border-radius: 8px; font-size: 14px;">
                    </div>
                </div>

                <!-- Gender -->
                <div style="margin-bottom: 18px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 600; color: #ffc107; margin-bottom: 10px;">
                        <span>Jenis Kelamin</span>
                        <i class="fas fa-question-circle" style="font-size: 12px;"></i>
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <label style="display: flex; align-items: center; justify-content: center; padding: 12px; border: 1px solid #d0d0d0; border-radius: 8px; cursor: pointer; font-size: 14px;">
                            <input type="radio" wire:model="gender" value="male" style="margin-right: 8px;">
                            <span>Laki - Laki</span>
                        </label>
                        <label style="display: flex; align-items: center; justify-content: center; padding: 12px; border: 1px solid #d0d0d0; border-radius: 8px; cursor: pointer; font-size: 14px;">
                            <input type="radio" wire:model="gender" value="female" style="margin-right: 8px;">
                            <span>Perempuan</span>
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('gender')" style="margin-top: 8px; color: #dc2626; font-size: 13px;" />
                </div>

                <!-- Phone -->
                <div style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 14px; font-weight: 400; color: #999; margin-bottom: 10px;">Nomor Seluler</label>
                    <input wire:model="phone" type="tel" placeholder="+62" required
                        style="width: 100%; padding: 12px 16px; border: 1px solid #d0d0d0; border-radius: 8px; font-size: 14px;">
                    <x-input-error :messages="$errors->get('phone')" style="margin-top: 8px; color: #dc2626; font-size: 13px;" />
                </div>

                <!-- Email -->
                <div style="margin-bottom: 18px;">
                    <input wire:model="email" type="email" placeholder="Email" required
                           style="width: 100%; padding: 12px 16px; border: 1px solid #d0d0d0; border-radius: 8px; font-size: 14px;">
                    <x-input-error :messages="$errors->get('email')" style="margin-top: 8px; color: #dc2626; font-size: 13px;" />
                </div>

                <!-- Password -->
                <div style="margin-bottom: 20px;">
                    <input wire:model="password" type="password" placeholder="Password" required
                           style="width: 100%; padding: 12px 16px; border: 1px solid #d0d0d0; border-radius: 8px; font-size: 14px;">
                    <x-input-error :messages="$errors->get('password')" style="margin-top: 8px; color: #dc2626; font-size: 13px;" />
                </div>

                <!-- Password Confirmation -->
                <div style="margin-bottom: 20px;">
                    <input wire:model="password_confirmation" type="password" placeholder="Konfirmasi Password" required
                        style="width: 100%; padding: 12px 16px; border: 1px solid #d0d0d0; border-radius: 8px; font-size: 14px;">
                </div>

                <!-- Terms -->
                <div style="text-align: center; margin-bottom: 20px;">
                    <p style="font-size: 13px; color: #ffc107; line-height: 1.6;">
                        Dengan menekan tombol Daftar, Anda setuju dengan Ketentuan,<br>
                        Kebijakan Privasi, serta Kebijakan Cookie yang berlaku di LGI STORE.
                    </p>
                </div>

                <!-- Register Button -->
                <button type="submit" 
                        style="width: 100%; padding: 14px; background: linear-gradient(to right, #f9a825, #ffc107); border: none; border-radius: 8px; font-size: 16px; font-weight: 600; color: #fff; cursor: pointer; transition: all 0.3s; margin-bottom: 16px;">
                    DAFTAR
                </button>

                <!-- Login Link -->
                <div style="text-align: center;">
                    <p style="font-size: 13px; color: #666;">
                        Sudah Memiliki Akun ?
                        <a href="{{ route('login') }}" wire:navigate style="color: #ffc107; text-decoration: none; font-weight: 600; cursor: pointer;">
                            Login Disini
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>