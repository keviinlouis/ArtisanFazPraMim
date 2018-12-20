<?php
/**
 * Created by PhpStorm.
 * User: devmaker
 * Date: 25/09/18
 * Time: 11:58
 */

namespace DumpNamespace;


use ReflectionClass;

trait ReflectionMethodsTrait
{
    /**
     * @return ReflectionClass
     * @throws \ReflectionException
     */
    public static function getReflectionClass()
    {
        return new ReflectionClass(static::class);
    }

    /**
     * @param string $resource
     * @return array
     * @throws \ReflectionException
     */
    public static function getConstants($resource = '')
    {
        $reflection = static::getReflectionClass();
        $constants = [];

        foreach($reflection->getConstants() as $constant => $value){
            if(!empty($resource)){
                if(strpos($constant, $resource) !== false){
                    $constants[$value] = $constant;
                }
            }else{
                $constants[$value] = $constant;
            }
        }

        return $constants;
    }
}
