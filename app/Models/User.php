<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'role',
        'phone',
        'passport',
        'password',
        'company_id',
        'department_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // -----------------------------
    // Relationships
    // -----------------------------
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function routes()
    {
        return $this->belongsToMany(\App\Models\SystemRoute::class, 'user_routes');
    }

    // -----------------------------
    // Helper methods
    // -----------------------------
    public function assignAllRoutes(): void
    {
        $allRoutes = \App\Models\SystemRoute::pluck('id');
        $this->routes()->sync($allRoutes);
    }

    /**
     * Routes every user always has access to (system constants)
     */
    public static array $alwaysAvailableRoutes = [
        'verification.notice',
        'app.auth.logout',
        'password.confirm',
        'verification.verify',
    ];

    /**
     * Get all routes this user has access to,
     * including system constant routes.
     *
     * @return \Illuminate\Support\Collection
     */
    public function allRoutes()
    {
        return $this->routes->pluck('name')
            ->merge(self::$alwaysAvailableRoutes)
            ->unique();
    }
}