<?php
/**
 * Created by PhpStorm.
 * User: devmaker
 * Date: 06/11/18
 * Time: 18:52
 */

namespace Louisk\ArtisanFazPraMim\Handlers;


use Louisk\ArtisanFazPraMim\Interfaces\HasCustomBody;
use Louisk\ArtisanFazPraMim\Interfaces\HasStub;

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
            if(in_array($this->config['not_required_fields'], $field) !== false){
                continue;
            }

            if($field == 'password'){
                $storeRules[] = '            \'password\' => \'required|confirmed|min:6\',';
                $updateRules[] = '            \'new_password\' => \'required_with:new_password|min:6\',';
                $updateRules[] = '            \'old_password\' => \'required_with:old_password|min:6\',';
            }else if($field == 'cpf' && $this->config['lang'] == 'pt_br'){
                $storeRules[] = '            \'cpf\' => \'required|cpf\',';
                $updateRules[] = '            \'cpf\' => \'nullable|cpf\',';
            }else if($field == 'cnpj' && $this->config['lang'] == 'pt_br'){
                $storeRules[] = '            \'cnpj\' => \'required|cnpj\',';
                $updateRules[] = '            \'cnpj\' => \'nullable|cnpj\',';
            }else{
                $storeRules[] = '            \''.$field.'\' => \'required\',';
                $updateRules[] = '            \''.$field.'\' => \'nullable\',';
            }
        }

        $str = str_replace('{{store}}', implode(PHP_EOL, $storeRules), $str);
        $str = str_replace('{{update}}', implode(PHP_EOL, $updateRules), $str);

        return $str;
    }
}
