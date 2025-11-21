<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Only set UUID if not already provided by sync payload
            if (empty($model->uuid)) {
                $model->uuid = (string) \Str::uuid();
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'branch_id',
        'role',
        'is_active',
        'printer_path',
        'office_id',
        'uuid'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        //'password',
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

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function transactions()
    {
        return $this->hasMany(\App\Models\Transaction::class);
    }

    public function nextOrNumber(): string|int
    {
        $lastOr = $this->transactions()
            ->whereNotNull('or_number')
            ->orderByDesc('id') // or 'date'
            ->value('or_number');

        // If numeric, increment. Else, fallback to 1.
        if (is_numeric($lastOr)) {
            return $lastOr + 1;
        }

        // Optionally handle custom OR formats here (e.g., OR-0001)
        return 1;
    }

    public function cashieringSessions()
    {
        return $this->hasMany(CashieringSession::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function preferences()
    {
        return $this->hasOne(UserPreference::class);
    }

    public function pref($key, $default = null)
    {
        return data_get($this->preferences->settings ?? [], $key, $default);
    }

    public function setPref(string $key, $value): void
    {
        $prefs = $this->preferences->settings ?? [];
        data_set($prefs, $key, $value);

        $this->preferences()->updateOrCreate(
            ['user_id' => $this->id],
            ['settings' => $prefs]
        );

        $this->refresh();
    }
}
