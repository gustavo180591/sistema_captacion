<?php
namespace App\Models;

use Core\Model;

class ResultadoPrueba extends Model
{
    protected static $table = 'resultados_pruebas';
    protected $fillable = [
        'sesion_id', 'atleta_id', 'prueba_id', 'valor_izquierdo',
        'valor_derecho', 'valor_promedio', 'observaciones'
    ];
    
    /**
     * The evaluation session this result belongs to
     */
    public function sesion()
    {
        return $this->belongsTo(SesionEvaluacion::class, 'sesion_id');
    }
    
    /**
     * The athlete this result is for
     */
    public function atleta()
    {
        return $this->belongsTo(Atleta::class, 'atleta_id');
    }
    
    /**
     * The test this result is for
     */
    public function prueba()
    {
        return $this->belongsTo(Prueba::class, 'prueba_id');
    }
    
    /**
     * Get results by session and athlete
     * @param int $sesionId
     * @param int $atletaId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getBySesionAndAtleta($sesionId, $atletaId)
    {
        return static::where('sesion_id', $sesionId)
                    ->where('atleta_id', $atletaId)
                    ->with('prueba')
                    ->get();
    }
    
    /**
     * Get results by session and test
     * @param int $sesionId
     * @param int $pruebaId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getBySesionAndPrueba($sesionId, $pruebaId)
    {
        return static::where('sesion_id', $sesionId)
                    ->where('prueba_id', $pruebaId)
                    ->with('atleta')
                    ->get();
    }
    
    /**
     * Get the final value to display (prefers average if available, otherwise uses left value)
     * @return mixed
     */
    public function getValorFinalAttribute()
    {
        if (!is_null($this->valor_promedio)) {
            return $this->valor_promedio;
        }
        
        if (!is_null($this->valor_izquierdo)) {
            return $this->valor_izquierdo;
        }
        
        return $this->valor_derecho;
    }
    
    /**
     * Check if the test has both left and right values
     * @return bool
     */
    public function tieneAmbosLados()
    {
        return !is_null($this->valor_izquierdo) && !is_null($this->valor_derecho);
    }
    
    /**
     * Calculate and set the average value
     * @return $this
     */
    public function calcularPromedio()
    {
        if ($this->tieneAmbosLados()) {
            $this->valor_promedio = ($this->valor_izquierdo + $this->valor_derecho) / 2;
        }
        
        return $this;
    }
}
