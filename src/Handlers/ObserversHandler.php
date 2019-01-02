<?php
/**
 * Created by PhpStorm.
 * User: devmaker
 * Date: 06/11/18
 * Time: 17:54
 */

namespace Louisk\ArtisanFazPraMim\Handlers;


use Louisk\ArtisanFazPraMim\Interfaces\HasBaseFile;

class ObserversHandler extends HandlerBase implements HasBaseFile
{
    public function getNamespace(): string
    {
        return $this->config['observers']['namespace'];
    }

    public function getPath(): string
    {
        return $this->config['observers']['path'];
    }

    public function getResource(): string
    {
        return 'Observers';
    }

    protected function getExcludedFiles(): array
    {
        $files = [];

        if(!$this->config['with_address_model']) {
            $files[] = 'AddressObserver.php';
        }

        if(!$this->config['with_file_model']) {
            $files[] = 'FileObserver.php';
        }

        return $files;
    }

    public function makeStubs(): void
    {
        parent::makeStubs();

        $this->registerObservers();
    }

    private function registerObservers()
    {
        $this->copyProvider();

        $path = app_path('Providers/ObserversProvider.php');

        $subStr = file_get_contents($path);

        $useStrBreaker = 'use Illuminate\Support\ServiceProvider;';
        $registerStrBreaker = '// TODO';

        $observers = [
            $registerStrBreaker
        ];
        $uses = [
            $useStrBreaker
        ];

        if(!$this->config['with_address_model']) {
            $observers[] = 'Address::observe(AddressObserver::class);';
            $uses[] = 'use '.$this->config['models']['namespace'].'\Address;';
        }

        if(!$this->config['with_file_model']) {
            $observers[] = 'File::observe(FileObserver::class);';
            $uses[] = 'use '.$this->config['models']['namespace'].'\File;';
        }

        $subStr = str_replace($useStrBreaker, implode(PHP_EOL, $uses), $subStr);
        $subStr = str_replace($registerStrBreaker, implode(PHP_EOL, $observers), $subStr);

        file_put_contents($path, $subStr);
    }

    private function copyProvider()
    {
        $from = __DIR__ . '/../BaseFiles/Providers/ObserversProvider.php';
        $to = app_path('Providers/ObserversProvider.php');

        copy($from, $to);

        $str = file_get_contents($to);

        $str = str_replace('DumpNamespace', 'App\\Providers', $str);

        file_put_contents($to, $str);
    }
}
