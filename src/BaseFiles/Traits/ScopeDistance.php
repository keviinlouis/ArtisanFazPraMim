<?php
/**
 * Created by PhpStorm.
 * User: devmaker
 * Date: 14/08/18
 * Time: 13:14
 */

namespace DumpNamespace;


use Illuminate\Database\Eloquent\Builder;

trait ScopeDistance
{
    public $fieldDistance = 'distancia';
    public $tableRelated = 'enderecos';

    public function scopeWhereDistance(Builder $query, float $latitude, float $longitude, float $raio, \Closure $where = null)
    {
        $latitude = str_replace(',', '.', $latitude);
        $longitude = str_replace(',', '.', $longitude);

        $sql = "((ACOS(SIN({$latitude} * PI() / 180) * SIN({$this->tableRelated}.latitude * PI() / 180) +
                COS({$latitude} * PI() / 180) * COS({$this->tableRelated}.latitude * PI() / 180) *
            COS(({$longitude} - {$this->tableRelated}.longitude) * PI() / 180)) * 180 / PI()) * 60 * 1.853159616)";

        $query->selectRaw($this->getTable() . ".*, $sql as " . $this->fieldDistance);

        $query->from($this->getTable());

        $query->join($this->tableRelated, $this->tableRelated . '.entidade_id', '=', $this->getTable() . '.'. $this->getKeyName());

        $query->where($this->tableRelated . '.entidade_type', '=', get_class($this));

        if ($where) {
            $query->where($where);
        }

        $query->whereRaw("$sql <= $raio");
    }

    public static function getDistanceField()
    {
        return (new self())->fieldDistance;
    }
}
