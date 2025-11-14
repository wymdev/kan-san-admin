<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\LogsActivity;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable , HasRoles;

    use LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    public function activities()
    {
        return $this->morphMany(ActivityLog::class, 'actor')
            ->orderBy('created_at', 'desc');
    }
    
    /**
     * Get activities where this user was the subject
     */
    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable')
            ->orderBy('created_at', 'desc');
    }
    
    /**
     * Custom identifier for logs (optional)
     */
    public function getLogIdentifier(): string
    {
        return $this->email ?? $this->name ?? "#" . $this->id;
    }
}
