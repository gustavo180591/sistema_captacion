<?php
namespace App\Models;

use Core\Model;

class Centro extends Model
{
    protected static $table = 'centros';
    protected $fillable = ['nombre', 'direccion', 'zona_id', 'telefono', 'email', 'activo'];
    
    /**
     * The zone where the center is located
     */
    public function zona()
    {
        return $this->belongsTo(Zona::class, 'zona_id');
    }
    
    /**
     * The evaluators working at this center
     */
    public function evaluadores()
    {
        return $this->hasMany(Evaluador::class, 'centro_id');
    }
    
    /**
     * The evaluation sessions held at this center
     */
    public function sesiones()
    {
        return $this->hasMany(SesionEvaluacion::class, 'centro_id');
    }
    
    /**
     * Get active centers
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActivos()
    {
        return static::where('activo', true)->get();
    }
    
    /**
     * Get centers by zone
     * @param int $zonaId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByZona($zonaId)
    {
        return static::where('zona_id', $zonaId)
                    ->where('activo', true)
                    ->get();
    }
}
