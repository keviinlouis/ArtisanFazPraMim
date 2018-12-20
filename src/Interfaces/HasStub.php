<?php
/**
 * Created by PhpStorm.
 * User: devmaker
 * Date: 06/11/18
 * Time: 18:53
 */

namespace Louisk\ArtisanFazPraMim\Interfaces;


interface HasStub
{
    public function getStub($isAuth = false): string;

    public function getReplaces($file, $auth = ''): array;

    public function getFileName(): string;
}
