<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'phone',
        'google_id',
        'avatar',
        'address',
        'province',
        'city',
        'district',
        'postal_code',
        'address_notes',
        'birth_date',
        'bio',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birth_date' => 'date',
    ];

    /**
     * Get the customer addresses for the user
     */
    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class, 'user_id');
    }

    /**
     * Get the virtual accounts for the user
     */
    public function virtualAccounts()
    {
        return $this->hasMany(VirtualAccount::class, 'user_id');
    }

    /**
     * Get the payment transactions for the user
     */
    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'user_id');
    }

    /**
     * Get the orders for the user
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    /**
     * Get the custom design orders for the user
     */
    public function customDesignOrders()
    {
        return $this->hasMany(CustomDesignOrder::class, 'user_id');
    }

    /**
     * Get notifications for this user
     */
    public function notifications()
    {
        return $this->morphMany(\App\Models\Notification::class, 'notifiable')
            ->orderBy('created_at', 'desc');
    }
}