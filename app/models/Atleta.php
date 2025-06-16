<?php
namespace App\Models;

use Core\Model;

class Atleta extends Model
{
    protected static $table = 'atletas';
    protected $fillable = [
        'usuario_id', 'nombre', 'apellido', 'dni', 'fecha_nacimiento',
        'sexo', 'domicilio', 'localidad', 'telefono', 'email', 'altura',
        'peso', 'envergadura', 'altura_sentado', 'evaluador_id', 'activo'
    ];
    
    protected $dates = ['fecha_nacimiento'];
    
    /**
     * The user account associated with the athlete
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
    
    /**
     * The evaluator assigned to this athlete
     */
    public function evaluador()
    {
        return $this->belongsTo(Evaluador::class, 'evaluador_id');
    }
    
    /**
     * The test results for this athlete
     */
    public function resultados()
    {
        return $this->hasMany(ResultadoPrueba::class, 'atleta_id');
    }
    
    /**
     * The evaluation sessions this athlete participated in
     */
    public function sesiones()
    {
        return $this->belongsToMany(
            SesionEvaluacion::class,
            'resultados_pruebas',
            'atleta_id',
            'sesion_id'
        )->withPivot('prueba_id', 'valor_izquierdo', 'valor_derecho', 'valor_promedio');
    }
    
    /**
     * Get full name of the athlete
     * @return string
     */
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre . ' ' . $this->apellido);
    }
    
    /**
     * Calculate age based on birth date
     * @return int
     */
    public function getEdadAttribute()
    {
        return $this->fecha_nacimiento->age;
    }
    
    /**
     * Find athlete by DNI
     * @param string $dni
     * @return Atleta|null
     */
    public static function findByDni($dni)
    {
        return static::where('dni', $dni)->first();
    }
    
    /**
     * Get active athletes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActivos()
    {
        return static::where('activo', true)->get();
    }
    
    /**
     * Get athletes by evaluator
     * @param int $evaluadorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByEvaluador($evaluadorId)
    {
        return static::where('evaluador_id', $evaluadorId)
                    ->where('activo', true)
                    ->get();
    }
    
    /**
     * Get the IMC (Body Mass Index) of the athlete
     * @return float|null
     */
    public function getImcAttribute()
    {
        if (empty($this->altura) || empty($this->peso)) {
            return null;
        }
        
        $alturaMetros = $this->altura / 100; // Convert cm to m
        return round($this->peso / ($alturaMetros * $alturaMetros), 2);
    }
}
