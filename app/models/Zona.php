<?php
namespace App\Models;

use Core\Model;

class Zona extends Model
{
    protected static $table = 'zonas';
    protected $fillable = ['nombre', 'descripcion', 'activa'];
    
    /**
     * The centers located in this zone
     */
    public function centros()
    {
        return $this->hasMany(Centro::class, 'zona_id');
    }
    
    /**
     * Get active zones
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActivas()
    {
        return static::where('activa', true)->get();
    }
    
    /**
     * Get all zones with their active centers
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function withActiveCenters()
    {
        return static::with(['centros' => function($query) {
            $query->where('activo', true);
        }])->where('activa', true)->get();
    }
}
