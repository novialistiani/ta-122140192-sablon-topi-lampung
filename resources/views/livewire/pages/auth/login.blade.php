<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;

use function Livewire\Volt\form;
use function Livewire\Volt\layout;

layout('layouts.auth');

form(LoginForm::class);

$login = function () {
    $this->validate();

    $this->form->authenticate();

    Session::regenerate();

    $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
};

?>

<!-- Single root wrapper to satisfy Livewire -->
<div class="lgi-login">
    <!-- Home Button -->
    <div class="home-button">
        <a href="{{ route('home') }}">
            <i class="fas fa-home"></i>
            <span>Back to Home</span>
        </a>
    </div>

    <div class="top-bar">
        <a href="{{ route('admin.login') }}" class="admin-login" onclick="window.location.href='{{ route('admin.login') }}'; return false;">
            <i class="fas fa-lock"></i>
            <span>Admin Login</span>
        </a>
    </div>

    <div class="container">
        <div class="logo-section">
            <div class="logo-text-container">
                <div class="logo-text">LGI STORE</div>
                <div class="logo-tagline">PEDULI KUALITAS, BUKAN KUANTITAS</div>
            </div>
            <div class="logo-circle">
                <img src="{{ asset('images/logo-lgi-Photoroom.png') }}" alt="LGI Store Logo">
            </div>
        </div>

        <div class="login-card">
            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Success Notification -->
            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Error Notification -->
            @if (session('error'))
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form id="loginForm" wire:submit="login">
                <div class="form-group">
                    <label for="email">Nama Pengguna / Email</label>
                    <input
                        type="text"
                        id="email"
                        name="email"
                        placeholder="contoh.email.com"
                        required
                        wire:model="form.email"
                        autocomplete="username"
                        autofocus
                    >
                    <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                </div>

                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <div class="password-toggle">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••••"
                            required
                            wire:model="form.password"
                            autocomplete="current-password"
                        >
                        <i class="fas fa-eye toggle-icon" id="togglePassword"></i>
                    </div>
                    <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                </div>

                <div class="checkbox-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="remember" name="remember" wire:model="form.remember">
                        <label for="remember">ingat saya</label>
                    </div>

                    @if (Route::has('password.request'))
                        <a class="forgot-password" href="{{ route('password.request') }}" wire:navigate>
                            Lupa Kata Sandi?
                        </a>
                    @endif
                </div>

                <button type="submit" class="login-btn">Masuk</button>

                <div class="signup-text">
                    Kamu Tidak Memiliki Akun?
                    <a href="{{ route('register') }}" wire:navigate>Daftar</a>
                </div>

                <div class="divider">atau</div>
    
                <div class="social-login">
                    <a href="{{ route('google.login') }}" class="google-btn">
                        <svg class="google-icon" viewBox="0 0 24 24">
                            <path d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z"/>
                        </svg>
                        Masuk Melalui Google
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
