<?php
namespace App\Models;

use Core\Model;

class Role extends Model
{
    protected static $table = 'roles';
    
    /**
     * The users that belong to the role.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'rol_id');
    }
    
    /**
     * Get role by name
     * @param string $name
     * @return Role|null
     */
    public static function findByName($name)
    {
        return static::where('nombre', $name)->first();
    }
}
