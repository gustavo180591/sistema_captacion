<?php
namespace App\Models;

use Core\Model;

class SesionEvaluacion extends Model
{
    protected static $table = 'sesiones_evaluacion';
    protected $fillable = [
        'nombre', 'descripcion', 'fecha', 'hora_inicio', 'hora_fin',
        'centro_id', 'evaluador_id', 'observaciones', 'estado'
    ];
    
    protected $dates = ['fecha', 'hora_inicio', 'hora_fin'];
    
    /**
     * The center where the evaluation session is held
     */
    public function centro()
    {
        return $this->belongsTo(Centro::class, 'centro_id');
    }
    
    /**
     * The evaluator conducting the session
     */
    public function evaluador()
    {
        return $this->belongsTo(Evaluador::class, 'evaluador_id');
    }
    
    /**
     * The test results for this session
     */
    public function resultados()
    {
        return $this->hasMany(ResultadoPrueba::class, 'sesion_id');
    }
    
    /**
     * The athletes who participated in this session
     */
    public function atletas()
    {
        return $this->belongsToMany(
            Atleta::class,
            'resultados_pruebas',
            'sesion_id',
            'atleta_id'
        )->withPivot('prueba_id', 'valor_izquierdo', 'valor_derecho', 'valor_promedio');
    }
    
    /**
     * The tests conducted in this session
     */
    public function pruebas()
    {
        return $this->belongsToMany(
            Prueba::class,
            'resultados_pruebas',
            'sesion_id',
            'prueba_id'
        )->withPivot('atleta_id', 'valor_izquierdo', 'valor_derecho', 'valor_promedio');
    }
    
    /**
     * Get sessions by evaluator
     * @param int $evaluadorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByEvaluador($evaluadorId)
    {
        return static::where('evaluador_id', $evaluadorId)
                    ->orderBy('fecha', 'desc')
                    ->orderBy('hora_inicio')
                    ->get();
    }
    
    /**
     * Get upcoming sessions
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getProximas($limit = 5)
    {
        return static::where('fecha', '>=', date('Y-m-d'))
                    ->where('estado', 'pendiente')
                    ->orderBy('fecha')
                    ->orderBy('hora_inicio')
                    ->limit($limit)
                    ->get();
    }
    
    /**
     * Get the session date in a readable format
     * @return string
     */
    public function getFechaFormateadaAttribute()
    {
        return $this->fecha->format('d/m/Y');
    }
    
    /**
     * Get the session time range in a readable format
     * @return string
     */
    public function getHorarioAttribute()
    {
        return $this->hora_inicio->format('H:i') . ' - ' . $this->hora_fin->format('H:i');
    }
    
    /**
     * Check if the session is completed
     * @return bool
     */
    public function isCompletada()
    {
        return $this->estado === 'completada';
    }
    
    /**
     * Check if the session is pending
     * @return bool
     */
    public function isPendiente()
    {
        return $this->estado === 'pendiente';
    }
    
    /**
     * Check if the session is canceled
     * @return bool
     */
    public function isCancelada()
    {
        return $this->estado === 'cancelada';
    }
}
