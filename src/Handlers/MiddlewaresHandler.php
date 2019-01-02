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

    public function copyBaseFiles(): HandlerBase
    {
        parent::copyBaseFiles();

        $this->registerMiddleware();
    }

    private function registerMiddleware()
    {
        $break = 'protected $routeMiddleware = [';

        $path = app_path('Http/Kernel.php');

        $middlewares = [
            $break
        ];

        if($this->config['has_api']){
            $middlewares[] = '        \'jwt\' => CheckToken::class,';
        }

        $strFile = file_get_contents($path);

        $strFile = str_replace($break, implode(PHP_EOL, $middlewares), $strFile);

        file_put_contents($path, $strFile);



    }
}
