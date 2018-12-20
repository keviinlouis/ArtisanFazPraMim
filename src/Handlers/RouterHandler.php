<?php
/**
 * Created by PhpStorm.
 * User: devmaker
 * Date: 06/11/18
 * Time: 18:52
 */

namespace App\Generator\src\Handlers;


use Illuminate\Support\Str;

class RouterHandler extends HandlerBase
{
    public function getPath(): string
    {
        return '';
    }

    public function getNamespace(): string
    {
        return '';
    }

    public function makeRoutes()
    {
        $auths = $this->getAuths();

        foreach ($auths as $auth) {
            if($this->config['with_api']) {
                $this->makeRoute($auth, true);
            }

            if($this->config['with_web']) {
                $this->makeRoute($auth);
            }
        }
    }

    private function makeRoute($auth, $api = false)
    {
        $stub = ($api ? '/../Stubs/api-routebase.stub' : '/../Stubs/routebase.stub');

        $pathControllers = app_path(($api ? 'Http/Controllers/Api/' : 'Http/Controllers/') . $auth);

        $codeRoute = $api ? '    Route::apiResource(\'':'    Route::resource(\'';

        $indexConfig = $api ? 'routes_api' : 'routes';

        $subStr = file_get_contents(__DIR__ . $stub);

        $subStr = str_replace('DumpAuth', strtolower($auth), $subStr);

        $subStr = str_replace('DumpUpperAuth', $auth, $subStr);

        $routes = [];

        foreach (scandir($pathControllers) as $file) {
            $file = str_replace('.php', '', $file);

            if(in_array($file, ['.', '..', ucfirst($auth) . 'Controller']) !== false) {
                continue;
            }

            $model = str_replace('Controller', '', $file);

            $model = preg_split('/(?=\p{Lu})/u', $model);

            $modelLower = Str::slug(implode(' ', $model));

            $routes[] = $codeRoute . $modelLower . '\', \'' . $file . '\');';
        }

        $subStr = str_replace('{{routes}}', implode(PHP_EOL . PHP_EOL, $routes), $subStr);

        is_dir($this->config[$indexConfig]['path']) ?: mkdir($this->config['routes_api']['path']);

        $model = preg_split('/(?=\p{Lu})/u', $auth);

        $modelLower = Str::slug(implode(' ', $model));

        file_put_contents($this->config[$indexConfig]['path'] . '/' . $modelLower . '.php', $subStr);
        $api ? $this->registerApiRouteOnRouteServiceProvider($auth) :  $this->registerWebRouteOnRouteServiceProvider($auth);

    }

    private function registerApiRouteOnRouteServiceProvider(string $auth)
    {
        $model = preg_split('/(?=\p{Lu})/u', $auth);

        $modelLower = Str::slug(implode(' ', $model));

        $search = '->group(base_path(\'routes/api.php\'));';

        $code = [
            PHP_EOL,
            PHP_EOL . '        Route::prefix(\'api/' . $modelLower . '\')',
            PHP_EOL . '             ->middleware(\'api\')',
            PHP_EOL . '             ->namespace($this->namespace. \'\\Api\\'.$auth.'\')',
            PHP_EOL . '             ->group(base_path(\'routes/api/' . $modelLower . '.php\'));',
        ];

        $this->registerRouteOnProvider($modelLower, $code, $search);
    }

    private function registerWebRouteOnRouteServiceProvider(string $auth)
    {
        $model = preg_split('/(?=\p{Lu})/u', $auth);

        $modelLower = Str::slug(implode(' ', $model));

        $search = '->group(base_path(\'routes/web.php\'));';

        $code = [
            PHP_EOL,
            PHP_EOL . '        Route::prefix(\'' . $modelLower . '\')',
            PHP_EOL . '             ->middleware(\'web\')',
            PHP_EOL . '             ->namespace($this->namespace\''.$auth.'\')',
            PHP_EOL . '             ->group(base_path(\'routes/web/' . $modelLower . '.php\'));',
        ];

        $this->registerRouteOnProvider($modelLower, $code, $search);
    }

    private function registerRouteOnProvider($auth, $code, $search)
    {
        $path = app_path('Providers/RouteServiceProvider.php');

        $file = file_get_contents($path);

        if(strpos($file, $auth) !== false) {
            return;
        }

        $partsOfFile = explode($search, $file);

        $code[] = $partsOfFile[1];

        $partsOfFile[1] = implode('', $code);

        $strFile = implode($search, $partsOfFile);

        unlink($path);

        file_put_contents($path, $strFile);
    }

}
