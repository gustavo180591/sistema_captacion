<?php
namespace App\Models;

use Core\Model;

class Evaluador extends Model
{
    protected static $table = 'evaluadores';
    protected $fillable = [
        'usuario_id', 'nombre', 'apellido', 'dni', 'domicilio', 
        'telefono', 'email', 'centro_id', 'activo'
    ];
    
    /**
     * The user account associated with the evaluator
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
    
    /**
     * The center where the evaluator works
     */
    public function centro()
    {
        return $this->belongsTo(Centro::class, 'centro_id');
    }
    
    /**
     * The evaluation sessions conducted by this evaluator
     */
    public function sesiones()
    {
        return $this->hasMany(SesionEvaluacion::class, 'evaluador_id');
    }
    
    /**
     * The athletes evaluated by this evaluator
     */
    public function atletas()
    {
        return $this->hasMany(Atleta::class, 'evaluador_id');
    }
    
    /**
     * Get full name of the evaluator
     * @return string
     */
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre . ' ' . $this->apellido);
    }
    
    /**
     * Find evaluator by DNI
     * @param string $dni
     * @return Evaluador|null
     */
    public static function findByDni($dni)
    {
        return static::where('dni', $dni)->first();
    }
    
    /**
     * Get active evaluators
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActivos()
    {
        return static::where('activo', true)->get();
    }
}
