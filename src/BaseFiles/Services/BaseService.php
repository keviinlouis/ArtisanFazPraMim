<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 14/02/2018
 * Time: 20:50
 */

namespace DumpNamespace;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Collection;

abstract class BaseService
{
    use ValidatesRequests;

    public $relations = [];

    public $relationsCount = [];

    /**
     * @param Collection|array|null $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return Collection
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateWithArray($data, array $rules, array $messages = [], array $customAttributes = []) : Collection
    {
        if(is_null($data)){
            return collect([]);
        }
        $validator = \Validator::make($data instanceof Collection ? $data->toArray() : $data, $rules, $messages, $customAttributes);
        return collect($this->validateWith($validator));
    }

}
