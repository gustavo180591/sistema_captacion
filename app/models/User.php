<?php
namespace App\Models;

use Core\Model;
use Core\Database;
use Core\Hash;

class User extends Model
{
    protected static $table = 'usuarios';
    protected $fillable = ['nombre', 'email', 'password', 'rol_id'];
    protected $hidden = ['password', 'remember_token'];
    
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }
    
    /**
     * Check if user has a specific role
     * @param string|array $roles
     * @return bool
     */
    public function hasRole($roles)
    {
        if (is_string($roles)) {
            return $this->role->nombre === $roles;
        }
        
        if (is_array($roles)) {
            return in_array($this->role->nombre, $roles);
        }
        
        return false;
    }
    
    /**
     * Check if user is an admin
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('Administrador');
    }
    
    /**
     * Check if user is an evaluator
     * @return bool
     */
    public function isEvaluator()
    {
        return $this->hasRole('Evaluador');
    }
    
    /**
     * Check if user is an athlete
     * @return bool
     */
    public function isAthlete()
    {
        return $this->hasRole('Atleta');
    }
    
    /**
     * Get the evaluator record associated with the user.
     */
    public function evaluator()
    {
        return $this->hasOne(Evaluador::class, 'usuario_id');
    }
    
    /**
     * Get the athlete record associated with the user.
     */
    public function athlete()
    {
        return $this->hasOne(Atleta::class, 'usuario_id');
    }
    
    /**
     * Set the user's password.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }
    
    /**
     * Find a user by email
     * @param string $email
     * @return User|null
     */
    public static function findByEmail($email)
    {
        return static::where('email', $email)->first();
    }
    
    /**
     * Get the full name of the user
     * @return string
     */
    public function getFullNameAttribute()
    {
        if ($this->isEvaluator() && $this->evaluator) {
            return $this->evaluator->nombre . ' ' . $this->evaluator->apellido;
        }
        
        if ($this->isAthlete() && $this->athlete) {
            return $this->athlete->nombre . ' ' . $this->athlete->apellido;
        }
        
        return $this->nombre;
    }
}
