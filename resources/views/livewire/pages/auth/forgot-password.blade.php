<?php
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;

use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;

layout('layouts.auth');

state(['email' => '']);

rules(['email' => ['required', 'string', 'email']]);

$sendPasswordResetLink = function () {
    $this->validate();

    $status = Password::sendResetLink(
        $this->only('email')
    );

    if ($status != Password::RESET_LINK_SENT) {
        $this->addError('email', __($status));
        return;
    }

    $this->reset('email');
    Session::flash('status', __($status));
};
?>

<div class="forgot-password-container">
    <h2 class="forgot-password-title">{{ __('Lupa Password?') }}</h2>
    
    <p class="instruction-text">
        {{ __('Masukkan alamat email Anda dan kami akan mengirimkan link untuk reset password.') }}
    </p>

    @if (session('status'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form wire:submit="sendPasswordResetLink" class="reset-form">
        <div class="form-group">
            <label for="email">Email</label>
            <input
                wire:model="email"
                id="email"
                type="email"
                name="email"
                required
                autofocus
                placeholder="nama@email.com"
            >
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <button type="submit" class="reset-btn">
            {{ __('Kirim Link Reset Password') }}
        </button>

        <a href="{{ route('login') }}" class="back-to-login">
            {{ __('Kembali ke Login') }}
        </a>
    </form>
</div>