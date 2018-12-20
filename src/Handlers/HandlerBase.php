<?php
/**
 * Created by PhpStorm.
 * User: devmaker
 * Date: 06/11/18
 * Time: 17:56
 */

namespace Louisk\Generator\Handlers;


use Louisk\Generator\Interfaces\HasBaseFile;
use Louisk\Generator\Interfaces\HasCustomBody;
use Louisk\Generator\Interfaces\HasStub;
use Illuminate\Database\Eloquent\Model;

abstract class HandlerBase
{
    const CONFIG_NAME = 'faz_pra_mim';

    public $baseFiles = [];
    public $modelFiles = [];
    private $auths = [];
    public $config;
    public $path;

    public function __construct()
    {
        $this->config = self::getConfig();
    }

    abstract public function getPath(): string;

    abstract public function getNamespace(): string;

    public function instanceModel($model): Model
    {
        $fullModel = 'App\Models\\'.$model;
        /** @var Model $instanceModel */
        $instanceModel = new $fullModel();

        return $instanceModel;
    }

    public function makeStubs(): void
    {

        foreach ($this->getModelFiles(false) as $file) {
            $this->makeStub($file);
        }

    }

    public function copyBaseFiles(): self
    {
        is_dir($this->getPath()) ?: mkdir($this->getPath());

        foreach ($this->getBaseFiles() as $file) {
            $this->copyBaseFile($file);
        }

        return $this;
    }

    public function replaceArray(array $array, $str): string
    {
        foreach ($array as $search => $replace) {
            $str = str_replace($search, $replace, $str);
        }

        return $str;
    }

    public function removeExtensionFromArray(array $itens)
    {
        return array_map(function ($item) {
            return str_replace('.php', '', $item);
        }, $itens);
    }

    public function loadAuths(): void
    {
        $auths = [];

        $pathModels = $this->config['models']['path'];

        $itens = array_filter(scandir($pathModels), function ($item) {
            return in_array($item, ['.', '..', 'BaseModel.php', 'BaseUser.php']) === false;
        });

        foreach ($this->removeExtensionFromArray($itens) as $nameClass) {

            $fullNameClass = $this->config['models']['namespace'] . '\\' . $nameClass;

            /** @var Model $class */
            $class = new $fullNameClass();

            if(!$this->isUser($class)) {
                continue;
            }

            $auths[] = $nameClass;
        }

        $this->auths = $auths;
    }

    public function getAuths(): array
    {
        if(!$this->auths) {
            $this->loadAuths();
        }

        return $this->auths;
    }

    protected static final function getConfig(): array
    {
        return config(self::CONFIG_NAME, include(__DIR__ . '/../../config/' . self::CONFIG_NAME . '.php'));
    }

    protected function copyBaseFile($file): void
    {
        $from = $this->path . '/' . $file;
        $to = $this->getPath() . '/' . $file;

        copy($from, $to);

        $str = file_get_contents($to);

        $str = str_replace('DumpNamespace', $this->getNamespace(), $str);

        file_put_contents($to, $str);
    }

    protected final function getBaseFiles($withExtension = true): array
    {
        if(!$this->baseFiles) {
            $this->loadBaseFiles();
        }

        if(!$withExtension) {
            return $this->removeExtensionFromArray($this->baseFiles);
        }

        return $this->baseFiles;
    }

    protected final function getModelFiles($withExtension = true): array
    {
        if(!$this->modelFiles) {
            $this->loadModelFiles();
        }

        if(!$withExtension) {
            return $this->removeExtensionFromArray($this->modelFiles);
        }

        return $this->modelFiles;
    }

    private final function loadBaseFiles(): void
    {
        if($this instanceof HasBaseFile) {
            $this->path = $this->getResourcePath($this->getResource());

            $excludedFiles = array_merge($this->getExcludedFiles(), ['.', '..']);

            foreach (scandir($this->path) as $file) {
                if(in_array($file, $excludedFiles) !== false) {
                    continue;
                }

                $this->baseFiles[] = $file;
            }

            return;
        }

        $this->baseFiles = [];
    }

    private final function loadModelFiles(): void
    {
        $modelPath = $this->config['models']['path'];

        $excludedFiles = array_merge($this->getExcludedFiles(), ['.', '..']);

        foreach (scandir($modelPath) as $file) {
            if(in_array($file, $excludedFiles) !== false) {
                continue;
            }

            $this->modelFiles[] = $file;
        }
    }

    protected function getExcludedFiles(): array
    {
        return [];
    }

    protected function getAppFiles($withExtension = false): array
    {
        $excludedFiles = array_merge(['.', '..'], $this->getExcludedFiles());

        $data = array_filter(scandir($this->getPath()), function ($item) use ($excludedFiles) {
            return in_array($item, $excludedFiles) !== false;
        });

        if(!$withExtension) {
            $data = $this->removeExtensionFromArray($data);
        }

        return $data;
    }

    private function isUser(Model $model): bool
    {
        $fields = $this->config['auth_fields'];
        $assert = [];

        foreach ($model->getFillable() as $key => $field) {
            if(in_array($field, $fields)) {
                $assert[] = $key;
                continue;
            }
        }

        return count($assert) == count($fields);
    }

    private function makeStub($file): void
    {
        if($this instanceof HasStub) {
            $subStr = file_get_contents($this->getStub(in_array($file, $this->getAuths()) !== false));

            $subStr = $this->replaceArray($this->getReplaces($file), $subStr);


            is_dir($this->getPath()) ?: mkdir($this->getPath());

            $pathServiceApi = $this->getPath() . DIRECTORY_SEPARATOR . $file . $this->getFileName() . '.php';

            if($this instanceof HasCustomBody){
                $subStr = $this->addBody($subStr, $file);
            }

            file_put_contents($pathServiceApi, $subStr);
        }
    }

    private function getResourcePath($resource = ''): string
    {
        return __DIR__ . '/../BaseFiles/' . $resource;
    }
}
