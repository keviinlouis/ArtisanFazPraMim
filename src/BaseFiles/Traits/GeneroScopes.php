<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 05/03/2018
 * Time: 11:24
 */

namespace DumpNamespace;


use Illuminate\Database\Eloquent\Builder;

trait GeneroScopes
{
    static public $masculinoType = 'M';
    static public $femininoType = 'F';

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeMasculino(Builder $query)
    {
        return $query->where('genero', self::$masculinoType);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeFeminino(Builder $query)
    {
        return $query->where('genero', self::$femininoType);
    }

    static public function getGeneros($withTodos = false)
    {
        $data = [self::$masculinoType, self::$femininoType];
        if($withTodos){
            $data[] = 'T';
        }
        return $data;
    }

    public function isMasculino()
    {
        return $this->genero == self::$masculinoType;
    }

    public function isFeminino()
    {
        return $this->genero == self::$femininoType;
    }
}
