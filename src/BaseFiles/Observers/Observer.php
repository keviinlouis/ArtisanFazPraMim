<?php
/**
 * Created by PhpStorm.
 * User: Louis
 * Date: 25/05/2018
 * Time: 22:23
 */

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

abstract class Observer
{
    public function isEqual($field, Model $model)
    {
        if(is_array($field)){
            return $this->handlerArrayIsEqual($field, $model);
        }

        return $model->getOriginal($field) == $model->$field;
    }

    public function handlerArrayIsEqual($field, Model $model)
    {
        foreach ($field as $item){
            if(!$this->isEqual($item, $model)){
                return false;
            }
        }
        return true;
    }

    /**
     * @param $field
     * @param Model $model
     * @return bool
     */
    public function isDifferent($field, Model $model)
    {
        return !$this->isEqual($field, $model);
    }

    public function isOriginalNull($attribute, Model $model)
    {
        if(is_array($attribute)){
            foreach($attribute as $item){
                if($this->isOriginalNull($item, $model)){
                    return true;
                }
            }
        }

        return is_null($model->getOriginal($attribute));
    }

    public function isOriginalNotNull($attribute, Model $model)
    {
        return !$this->isOriginalNull($attribute, $model);
    }
}
