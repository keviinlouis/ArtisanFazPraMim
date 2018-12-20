<?php
/**
 * Created by PhpStorm.
 * User: devmaker
 * Date: 06/11/18
 * Time: 18:52
 */

namespace App\Generator\src\Handlers;


use App\Generator\src\Interfaces\HasCustomBody;
use App\Generator\src\Interfaces\HasStub;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RulesHandler extends HandlerBase implements HasStub, HasCustomBody
{

    public function getNamespace(): string
    {
        return $this->config['validators']['namespace'];
    }

    public function getPath(): string
    {
        return $this->config['validators']['path'];
    }

    public function getFileName(): string
    {
        return 'Rules';
    }

    public function getStub($isAuth = false): string
    {
        if($isAuth){
            return __DIR__.'/../stubs/validators-login.stub';
        }

        return  __DIR__.'/../stubs/validators.stub';
    }

    public function getReplaces($file, $auth = ''): array
    {
        return [
            'DumpModel' => $file,
            'DumpNamespaceValidators' => $this->config['validators']['namespace'],
            'DumpNamespaceModels' =>  $this->config['models']['namespace'],
        ];
    }

    protected function getExcludedFiles(): array
    {
        return ['File.php', 'Address.php', 'BaseModel.php', 'BaseUser.php'];
    }


    public function addBody($str, $model): string
    {
        $model = $this->instanceModel($model);

        $fillable = $model->getFillable();

        $storeRules = [];
        $updateRules = [];

        foreach($fillable as $field){
            $storeRules[] = '            \''.$field.'\' => \'required\',';
            $updateRules[] = '            \''.$field.'\' => \'nullable\',';
        }

        $str = str_replace('{{store}}', implode(PHP_EOL, $storeRules), $str);
        $str = str_replace('{{update}}', implode(PHP_EOL, $updateRules), $str);

        return $str;
    }
}
