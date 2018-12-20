<?php
/**
 * Created by PhpStorm.
 * User: DevMaker
 * Date: 21/02/2018
 * Time: 12:57
 */

namespace App\Generator\src\BaseFiles\Requests;


use Illuminate\Http\Request as RequestBase;
use Illuminate\Support\Arr;

class Request extends RequestBase
{
    public function __construct(\Illuminate\Http\Request $request)
    {
        $query = $request->query->all();

        foreach ($query as $key => $item){
            if(is_array($item) && count($item) == 1 && $request->method() == 'GET'){
                if(strlen(preg_replace('/[0-9,]+/', '', reset($item))) <= 0){
                    $item = explode(',', $item[0]);
                };

                foreach ($item as $i_key => $i){
                    if(is_null($i) || $i == '')
                        unset($item[$i_key]);
                }
            }
            $query[$key] = $item;
        }

        parent::__construct(
            $request->query->all(),
            $request->toArray(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all(),
            $request->content
        );
    }

    public function toCollection($attributes = [])
    {
        $data = collect($this->allWithoutFiles())->reject(function($item){
            return is_null($item);
        });
        if(count($attributes) > 0){
            return collect($data->only($attributes));
        }
        return $data;
    }

    /**
     * Get all of the input and files for the request.
     *
     * @param  array|mixed  $keys
     * @return array
     */
    public function allWithoutFiles($keys = null)
    {
        $input = $this->input();

        if (! $keys) {
            return $input;
        }

        $results = [];

        foreach (is_array($keys) ? $keys : func_get_args() as $key) {
            Arr::set($results, $key, Arr::get($input, $key));
        }

        return $results;
    }
}
