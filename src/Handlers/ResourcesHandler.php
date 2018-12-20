<?php
/**
 * Created by PhpStorm.
 * User: devmaker
 * Date: 06/11/18
 * Time: 18:52
 */

namespace App\Generator\src\Handlers;


use App\Generator\src\Interfaces\HasBaseFile;
use App\Generator\src\Interfaces\HasCustomBody;
use App\Generator\src\Interfaces\HasStub;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ResourcesHandler extends HandlerBase implements HasStub, HasBaseFile, HasCustomBody
{

    public function getNamespace(): string
    {
        return $this->config['resources']['namespace'];
    }

    public function getPath(): string
    {
        return $this->config['resources']['path'];
    }

    public function getResource(): string
    {
       return 'Resources';
    }

    public function getFileName(): string
    {
        return 'Resource';
    }

    public function getStub($isAuth = false): string
    {
       return  __DIR__.'/../stubs/resource.stub';
    }

    public function getReplaces($file, $auth = ''): array
    {
        return [
            'DumpNamespaceResource' => $this->getNamespace(),
            'DumpModel' => $file,
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

        $data = [];

        foreach($fillable as $field){
            if(strpos($field, '_id') !== false){
                $relationField = Str::camel(str_replace('_', ' ', str_replace('_id', '', $field)));
                $resource = ucfirst($relationField);

                $data[] = '            \''.str_replace('_id', '',$field).'\' => new '.$resource.'Resource($resource->'.$relationField.'),';
                continue;
            }

            $data[] = '            \''.$field.'\' => $resource->'.$field.',';
        }

        $str = str_replace('{{data}}', implode(PHP_EOL, $data), $str);

        return $str;
    }
}
