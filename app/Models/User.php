<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];
    protected $hidden = ['password', 'remember_token'];

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            $user->id ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function hasCmsRole(UserRole $role): bool
    {
        return $this->roles()->where('normalized_name', mb_strtoupper($role->value))->exists();
    }

    public function isManager(): bool
    {
        return $this->is_active && $this->hasCmsRole(UserRole::OrganizationManager);
    }

    public function canEditContent(): bool
    {
        return $this->is_active && (
            $this->hasCmsRole(UserRole::OrganizationManager)
            || $this->hasCmsRole(UserRole::Author)
        );
    }
}
