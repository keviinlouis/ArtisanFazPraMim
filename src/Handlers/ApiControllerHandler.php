<?php
/**
 * Created by PhpStorm.
 * User: devmaker
 * Date: 06/11/18
 * Time: 18:52
 */

namespace Louisk\ArtisanFazPraMim\Handlers;


use Louisk\ArtisanFazPraMim\Interfaces\HasStub;
use Illuminate\Support\Str;

class ApiControllerHandler extends HandlerBase implements HasStub
{

    public function getNamespace(): string
    {
        return $this->config['controllers_api']['namespace'];
    }

    public function getPath(): string
    {
        return $this->config['controllers_api']['path'];
    }

    public function getFileName(): string
    {
        return 'Controller';
    }

    public function getStub($isAuth = false): string
    {
        if($isAuth){
            return __DIR__.'/../stubs/api-controller-login.stub';
        }

        return  __DIR__.'/../stubs/api-controller.stub';
    }

    public function getReplaces($file, $auth = ''): array
    {
        return [
            'DumpModel' => $file,
            'DumpLowerModel' =>  Str::camel($file),
            'DumpNamespaceResource' => $this->config['resources']['namespace'],
            'DumpNamespaceService' => $this->config['services']['namespace'],
            'DumpNamespaceControllerApi' => $this->config['controllers_api']['namespace'] . '\\' . $auth,
        ];
    }

    protected function getExcludedFiles(): array
    {
        return ['File.php', 'Address.php', 'BaseModel.php', 'BaseUser.php'];
    }

    public function makeStubs(): void
    {
        foreach ($this->getModelFiles(false) as $file) {
            $this->makeController($file);
        }
    }

    public function makeController($file)
    {
        foreach ($this->getAuths() as $auth) {

            $controllerApiStr = file_get_contents($this->getStub($auth == $file));

            $controllerApiStr = $this->replaceArray($this->getReplaces($file, $auth), $controllerApiStr);

            $pathApi = $this->config['controllers_api']['path'] . DIRECTORY_SEPARATOR . $auth;

            is_dir($this->config['controllers_api']['path']) ?: mkdir($this->config['controllers_api']['path']);

            is_dir($pathApi) ?: mkdir($pathApi);

            $pathControllerApi = $pathApi . DIRECTORY_SEPARATOR . $file . 'Controller.php';

            file_put_contents($pathControllerApi, $controllerApiStr);
        }
    }
}
