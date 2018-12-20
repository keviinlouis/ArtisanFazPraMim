<?php
/**
 * Created by PhpStorm.
 * User: devmaker
 * Date: 06/11/18
 * Time: 17:54
 */

namespace Louisk\ArtisanFazPraMim\Handlers;


use Louisk\ArtisanFazPraMim\Interfaces\HasBaseFile;

class MiddlewaresHandler extends HandlerBase implements HasBaseFile
{
    public function getNamespace(): string
    {
        return 'App\Http\Middleware';
    }

    public function getPath(): string
    {
        return app_path('Http/Middleware');
    }

    public function getResource(): string
    {
        return 'Middlewares';
    }
}
