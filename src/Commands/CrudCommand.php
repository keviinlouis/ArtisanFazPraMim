<?php

namespace App\Generator\Commands;

use App\Generator\src\Handlers\ApiControllerHandler;
use App\Generator\src\Handlers\ExceptionsHandler;
use App\Generator\src\Handlers\MiddlewaresHandler;
use App\Generator\src\Handlers\ModelsHandler;
use App\Generator\src\Handlers\ResourcesHandler;
use App\Generator\src\Handlers\RouterHandler;
use App\Generator\src\Handlers\RulesHandler;
use App\Generator\src\Handlers\ServiceHandler;
use App\Generator\src\Handlers\TraitsHandler;
use ErrorException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

class CrudCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'faz-pra-mim';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Crud including controller, model, views & migrations.';
    /**
     * @var MiddlewaresHandler
     */
    protected $middlewaresHandler;
    /**
     * @var RouterHandler
     */
    protected $routerHandler;
    /**
     * @var ExceptionsHandler
     */
    protected $exceptionsHandler;

    private $hasApi;

    private $hasGeocode;

    private $hasPush;

    private $hasAddress;

    private $hasFile;

    private $hasWeb;

    /**
     * @var TraitsHandler
     */
    private $traitsHandler;
    /**
     * @var ModelsHandler
     */
    private $modelsHandler;

    /**
     * @var ApiControllerHandler
     */
    private $apiControllerHandler;
    /**
     * @var ServiceHandler
     */
    private $serviceHandler;
    /**
     * @var RulesHandler
     */
    private $rulesHandler;
    /**
     * @var ResourcesHandler
     */
    private $resourcesHandler;


    /**
     * Create a new command instance.
     *
     * @param TraitsHandler $traitsHandler
     * @param ModelsHandler $modelsHandler
     * @param ApiControllerHandler $apiControllerHandler
     * @param ServiceHandler $serviceHandler
     * @param RulesHandler $rulesHandler
     * @param ResourcesHandler $resourcesHandler
     * @param MiddlewaresHandler $middlewaresHandler
     * @param ExceptionsHandler $exceptionsHandler
     * @param RouterHandler $routerHandler
     */
    public function __construct(
        TraitsHandler $traitsHandler,
        ModelsHandler $modelsHandler,
        ApiControllerHandler $apiControllerHandler,
        ServiceHandler $serviceHandler,
        RulesHandler $rulesHandler,
        ResourcesHandler $resourcesHandler,
        MiddlewaresHandler $middlewaresHandler,
        ExceptionsHandler $exceptionsHandler,
        RouterHandler $routerHandler
    ) {
        parent::__construct();

        $this->traitsHandler = $traitsHandler;
        $this->modelsHandler = $modelsHandler;
        $this->apiControllerHandler = $apiControllerHandler;
        $this->serviceHandler = $serviceHandler;
        $this->rulesHandler = $rulesHandler;
        $this->resourcesHandler = $resourcesHandler;
        $this->middlewaresHandler = $middlewaresHandler;
        $this->routerHandler = $routerHandler;
        $this->exceptionsHandler = $exceptionsHandler;

        $this->hasApi = $this->resourcesHandler->config['with_api'];
        $this->hasWeb = $this->resourcesHandler->config['with_web'];
        $this->hasGeocode = $this->resourcesHandler->config['with_geocode'];
        $this->hasPush = $this->resourcesHandler->config['with_push'];

        $this->hasAddress = $this->resourcesHandler->config['with_address_model'];
        $this->hasFile = $this->resourcesHandler->config['with_file_model'];

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->handleBaseConfig();

        $this->handleBaseFiles();

        try{
            $this->modelsHandler->runCodeModels();
            $this->info('Gerado models pelo banco');
        }catch (\Exception $e){
            dd($e);
            $this->info('Configuração Base finalizada, execute o comando novamente');
            return;
        }

        exec('composer dumpautoload');

        $this->info('Configurando Guards');

        $this->modelsHandler->makeAuths();

        $this->handleGenerator();

        $this->info('Proximos passos:');

        if($this->hasApi) {
            $this->info('Configurar CORS: https://github.com/barryvdh/laravel-cors');
            $this->info('Configurar JWT: https://github.com/tymondesigns/jwt-auth');
        }

        return;
    }

    private function handleBaseFiles()
    {
        $this->info('Copiando Traits');

        $this->traitsHandler->copyBaseFiles();

        $this->info('Copiando Base Models');

        $this->modelsHandler->copyBaseFiles();

        $this->info('Copiando Base Services');

        $this->serviceHandler->copyBaseFiles();

        $this->info('Copiando Excpetion Handler');

        $this->exceptionsHandler->copyBaseFiles();

        if($this->hasApi) {
            $this->info('Copiando Base Resources');

            $this->resourcesHandler->copyBaseFiles();

            $this->info('Copiando Base Resources');

            $this->middlewaresHandler->copyBaseFiles();
        }

        if($this->hasAddress) {
            $this->info('Copiando Migrate da model Address');
            copy(__DIR__ . '/../BaseFiles/Migrations/2018_06_20_000000_create_address_table.php',
                database_path('migrations') . '/2018_06_20_000000_create_address_table.php');
        }

        if($this->hasFile) {
            $this->info('Copiando Migrate da model File');
            copy(__DIR__ . '/../BaseFiles/Migrations/2018_06_20_000000_create_files_table.php',
                database_path('migrations') . '/2018_06_20_000000_create_files_table.php');
        }
    }

    private function handleGenerator()
    {
        $this->info('Fazendo Rules');

        $this->rulesHandler->makeStubs();

        $this->info('Fazendo Services');

        $this->serviceHandler->makeStubs();

        if($this->hasApi) {

            $this->info('Fazendo Resources');

            $this->resourcesHandler->makeStubs();

            $this->info('Fazendo Api Controllers');

            $this->apiControllerHandler->makeStubs();

        }

        if($this->hasWeb){
            $this->info('Fazendo Controllers');
        }

        $this->info('Fazendo Rotas');

        $this->routerHandler->makeRoutes();
    }

    private function handleBaseConfig()
    {
        $require = [];
        if($this->hasApi) {
            if(!$this->checkInComposer('tymon/jwt-auth:dev-develop')) {
                $require[] = 'tymon/jwt-auth:dev-develop';
            }

            if(!$this->checkInComposer('barryvdh/laravel-cors')) {
                $require[] = 'barryvdh/laravel-cors';
            }

            $this->info('Copiando Middlewares');

            $this->middlewaresHandler->copyBaseFiles();

            $this->info('Adicionando JWT Keys no arquivo .env');

            $this->addEnvKeys([
                "JWT_SECRET" => "",
                "JWT_TTL" => 21600,
                "JWT_REFRESH_TTL" => 21600000,
                "JWT_BLACKLIST_GRACE_PERIOD" => 30,
            ]);

        }

        if($this->hasGeocode) {
            if(!$this->checkInComposer('jcf/geocode')) {
                $require[] = 'jcf/geocode';
            }

            $this->info('Adicionando Geocode Keys no arquivo .env');

            $this->addEnvKeys([
                "GEOCODE_GOOGLE_API_KEY" => "",
                "GEOCODE_GOOGLE_LANGUAGE" => "pt-BR",
            ]);
        }

        if($this->hasPush) {
            if(!$this->checkInComposer('louisk/laravel-push-fcm')) {
                $require[] = 'louisk/laravel-push-fcm';
            }

            $this->info('Adicionando FCM Keys no arquivo .env');

            $this->addEnvKeys([
                "FCM_SERVER_KEY" => "",
                "FCM_SENDER_ID" => "",
            ]);
        }

        $this->composerRequire($require);
    }

    public function checkInComposer($package)
    {
        list($name) = explode(':', $package);

        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        return isset($composer['require'][$name]);
    }

    public function composerRequire(array $packages)
    {
        if(empty($packages)) {
            return;
        }
        $packages = implode(' ', $packages);

        $this->info('Instalando ' . $packages);


        exec('cd ' . base_path() . '&& composer require ' . $packages);
    }

    private function addEnvKeys(array $keys)
    {
        $envPath = base_path().'/.env';
        $envStr = file_get_contents($envPath);

        $envStr .= PHP_EOL;

        foreach($keys as $key => $value){
            if(!$this->hasInEnv($key)){

                $envStr .= PHP_EOL.$key.'='.trim($value);
            }
        }

        file_put_contents($envPath, $envStr);
    }

    private function hasInEnv($key)
    {
        $envPath = base_path().'/.env';
        $env = file($envPath);

        foreach($env as $line){
            list($keyOnEnv) = explode('=', $line);

            if(strpos($key, $keyOnEnv) !== false){
                return true;
            }
        }

        return false;
    }
}
