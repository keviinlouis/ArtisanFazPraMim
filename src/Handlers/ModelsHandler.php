<?php
/**
 * Created by PhpStorm.
 * User: devmaker
 * Date: 06/11/18
 * Time: 17:54
 */

namespace Louisk\ArtisanFazPraMim\Handlers;


use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Louisk\ArtisanFazPraMim\Interfaces\HasBaseFile;
use Reliese\Coders\Model\Config;
use Reliese\Coders\Model\Factory;
use Reliese\Support\Classify;

class ModelsHandler extends HandlerBase implements HasBaseFile
{
    const ADDRESS_FILES = 'Address.php';
    const FILE_FILES = 'File.php';

    const BASE_USER = 'BaseUser';
    const BASE_MODEL = 'BaseModel';

    public function getNamespace(): string
    {
        return $this->config['models']['namespace'];
    }

    public function getPath(): string
    {
        return $this->config['models']['path'];
    }

    public function getResource(): string
    {
        return 'Models';
    }

    protected function getExcludedFiles(): array
    {
        $data = [];

        if($this->config['with_address_model']) {
            $data[] = self::ADDRESS_FILES;
        }

        if($this->config['with_file_model']) {
            $data[] = self::FILE_FILES;
        }

        return $data;
    }

    public function runCodeModels(): self
    {
        Artisan::call('vendor:publish', ['--tag' => 'reliese-models']);

        $configModels = file_get_contents($this->getCodeModelsStub());

        $configModels = $this->replaceArray([
            '{{namespace}}' => $this->getNamespace(),
            '{{parent}}' => $this->getNamespace() . '\BaseModel::class',
            '{{models_path}}' => $this->getPath(),
        ], $configModels);

        file_put_contents(config_path('models.php'), $configModels);

        $config = require config_path('models.php');

        $factory = new Factory(
            app()->make('db'),
            app()->make(Filesystem::class),
            new Classify(),
            new Config($config)
        );
        $connection = config('database.default');

        $schema = config("database.connections.$connection.database");

        $factory->on($connection)->map($schema);

        $this->handleBaseAddressAndFile();

        exec('php artisan ide-helper:models -W');

        return $this;
    }

    private function handleBaseAddressAndFile()
    {
        if($this->config['with_address_model'])
            copy(__DIR__.'/../BaseFiles/Models/'.self::ADDRESS_FILES, $this->config['models']['path'].'/'.self::ADDRESS_FILES);
        
        if($this->config['with_file_model'])
            copy(__DIR__.'/../BaseFiles/Models/'.self::FILE_FILES, $this->config['models']['path'].'/'.self::FILE_FILES);
    }

    private function getCodeModelsStub(): string
    {
        return __DIR__ . '/../../config/models.stub';
    }

    public function makeAuths(): self
    {

        foreach ($this->getAuths() as $auth) {
            $this->makeAuth($auth);
        }

        return $this;
    }

    private function makeAuth($file): void
    {
        $this->changeBaseModelToBaseUser($file);

        $this->makeGuard($file);
    }

    private function changeBaseModelToBaseUser($file): void
    {
        $modelPath = $this->getPath() . '/' . $file . '.php';

        $fileStr = file_get_contents($modelPath);

        $fileStr = str_replace(self::BASE_MODEL, self::BASE_USER, $fileStr);

        file_put_contents($modelPath, $fileStr);
    }

    private function makeGuard($file): void
    {
        if(!is_file(config_path('auth_old.php'))) {
            copy(config_path('auth.php'), config_path('auth_old.php'));
        }

        $auth = include config_path('auth.php');

        $auth['guards'][Str::snake($file)] = [
            'driver' => 'session',
            'provider' => Str::snake($file),
        ];

        if($this->config['with_api']) {
            $auth['guards'][Str::snake($file) . '_api'] = [
                'driver' => 'jwt',
                'provider' => Str::snake($file),
            ];
        }

        $auth['providers'][Str::snake($file)] = [
            'driver' => 'eloquent',
            'model' => $this->getNamespace() . '\\' . $file,
        ];

        file_put_contents(config_path('auth.php'), '<?php' . PHP_EOL . ' return ' . var_export($auth, true) . ';');
    }
}
