<?php
/**
 * Created by PhpStorm.
 * User: devmaker
 * Date: 06/11/18
 * Time: 18:52
 */

namespace Louisk\ArtisanFazPraMim\Handlers;


use Louisk\ArtisanFazPraMim\Interfaces\HasBaseFile;
use Louisk\ArtisanFazPraMim\Interfaces\HasCustomBody;
use Louisk\ArtisanFazPraMim\Interfaces\HasStub;
use Illuminate\Support\Str;
use Reliese\Coders\Model\Relations\BelongsTo;

class ServiceHandler extends HandlerBase implements HasStub, HasBaseFile, HasCustomBody
{

    public function getNamespace(): string
    {
        return $this->config['services']['namespace'];
    }

    public function getPath(): string
    {
        return $this->config['services']['path'];
    }

    public function getResource(): string
    {
        return 'Services';
    }

    public function getFileName(): string
    {
        return 'Service';
    }

    public function getStub($isAuth = false): string
    {
        if($isAuth){
            return __DIR__.'/../stubs/service-login.stub';
        }

        return  __DIR__.'/../stubs/service.stub';
    }

    public function getReplaces($file, $auth = ''): array
    {
        return [
            'DumpModel' => $file,
            'DumpNamespaceValidators' => $this->config['validators']['namespace'],
            'DumpNamespaceService' => $this->config['services']['namespace'],
            'DumpNamespaceModels' =>  $this->config['models']['namespace'],
        ];
    }

    protected function getExcludedFiles(): array
    {
        return ['File.php', 'Address.php', 'BaseModel.php', 'BaseUser.php'];
    }

    public function addBody($str, $model): string
    {
        $instanceModel = $this->instanceModel($model);

        $fieldsSearchables = (array) $this->config['searchable_fields'];

        $fillable = $instanceModel->getFillable();

        $str = $this->buildRelations($instanceModel, $fillable, $str);

        $searchFilter = '{{search_filter}}';

        $hasStatusField = false;

        $beforeCode = [
            '        if ($search = $filters->get(\'search\')) {',
            '           $query->where(function (Builder $builder) use ($search) {',
        ];

        $wheresSearchCode = [];

        foreach($fillable as $field) {
            if($field == 'status'){
                $hasStatusField = true;
            }

            if(in_array($field, $fieldsSearchables) === false) {
                continue;
            }

            if(count($wheresSearchCode) <= 0) {
                $wheresSearchCode[] = '               $builder->where(\'' . $field . '\', \'like\', "%$search%");';
                continue;
            }

            $wheresSearchCode[] = '               $builder->orWhere(\'' . $field . '\', \'like\', "%$search%");';
        }

        $afterCode = [
            '            });',
            '        }',
        ];


        $strCodeSearch = implode(PHP_EOL, array_merge($beforeCode, $wheresSearchCode, $afterCode));

        $str = str_replace($searchFilter, $strCodeSearch, $str);


        if(!$hasStatusField){
            return str_replace('{{where_status}}', '', $str);
        }

        $whereStatusCode = [
            '        $status = $filters->get(\'status\');',
            '        if(!is_null($status)){',
            '          $query->where(\'status\', $status);',
            '        }',

        ];

        return str_replace('{{where_status}}', implode(PHP_EOL, $whereStatusCode), $str);
    }

    private function buildRelations($instanceModel, array $fillable, $str)
    {
        $relations = array_filter($fillable, function($item){
            return str_contains($item, '_id');
         });

        if(!$relations){
            $str = str_replace('{{relations_store}}', '', $str);
            $str = str_replace('{{relations_update}}', '', $str);

            return $str;
        }
        $relationsStoreCode = [];

        $relationsUpdateCode = [];

        foreach($relations as $relation){
            $relation = str_replace('_id', '', $relation);
            $relationCamel = Str::camel($relation);
            
            if(in_array($relation.'_type', $fillable) !== false){
                continue;
            }
            
            if($instanceModel->$relationCamel() instanceof BelongsTo){
                continue;
            }

            $relationsStoreCode[] = '        $model->'.$relationCamel.'()->create($data->get(\''.$relation.'\'));';

            $relationsUpdateCode[] = '        if($'.$relationCamel.' = $data->get(\''.$relation.'\')){';
            $relationsUpdateCode[] = '              $model->'.$relationCamel.'->update($data->get(\''.$relation.'\'));';
            $relationsUpdateCode[] = '        }';
        }

        $str = str_replace('{{relations_store}}', implode(PHP_EOL, $relationsStoreCode), $str);
        $str = str_replace('{{relations_update}}', implode(PHP_EOL, $relationsUpdateCode), $str);

        return $str;
    }
}
