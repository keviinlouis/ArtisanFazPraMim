<?php
/**
 * Created by PhpStorm.
 * User: devmaker
 * Date: 2018-12-20
 * Time: 19:34
 */

namespace App\Generator\src\Interfaces;


interface HasCustomBody
{
    public function addBody($str, $model): string;
}
