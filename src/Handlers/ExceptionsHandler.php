<?php
/**
 * Created by PhpStorm.
 * User: devmaker
 * Date: 06/11/18
 * Time: 17:54
 */

namespace Louisk\ArtisanFazPraMim\Handlers;


use Louisk\ArtisanFazPraMim\Interfaces\HasBaseFile;

class ExceptionsHandler extends HandlerBase implements HasBaseFile
{
    public function getNamespace(): string
    {
        return 'App\Exceptions';
    }

    public function getPath(): string
    {
        return app_path('Exceptions');
    }

    public function getResource(): string
    {
        return 'Exceptions';
    }
}
