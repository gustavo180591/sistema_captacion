<?php
namespace App\Models;

use Core\Model;

class Prueba extends Model
{
    protected static $table = 'pruebas';
    protected $fillable = [
        'nombre', 'descripcion', 'unidad_medida', 'instrucciones',
        'valor_minimo', 'valor_maximo', 'es_mejor_mayor', 'activo'
    ];
    
    /**
     * The test results for this test
     */
    public function resultados()
    {
        return $this->hasMany(ResultadoPrueba::class, 'prueba_id');
    }
    
    /**
     * Get active tests
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActivas()
    {
        return static::where('activo', true)->orderBy('nombre')->get();
    }
    
    /**
     * Get tests by type
     * @param string $tipo
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByTipo($tipo)
    {
        return static::where('tipo', $tipo)
                    ->where('activo', true)
                    ->orderBy('nombre')
                    ->get();
    }
    
    /**
     * Get all test types
     * @return array
     */
    public static function getTipos()
    {
        return [
            'fuerza' => 'Fuerza',
            'velocidad' => 'Velocidad',
            'resistencia' => 'Resistencia',
            'flexibilidad' => 'Flexibilidad',
            'coordinacion' => 'Coordinación',
            'otro' => 'Otro'
        ];
    }
}
