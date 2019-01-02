<?php
/**
 * Created by PhpStorm.
 * User: DevMaker
 * Date: 02/03/2018
 * Time: 18:45
 */

namespace DumpNamespace;

use App\Entities\File;
use App\Entities\Cliente;
use App\Entities\Modelo;
use App\Entities\Veiculo;
use App\Services\FileService;
use App\Traits\CheckOriginalAttribute;

class FileObserver extends Observer
{
    private $fileService;

    public function __construct()
    {
        $this->fileService = new FileService();
    }

    protected $typesRemovable = [
        // TODO Preencher Tipo de files que irão ser removidas ao remover o file do banco
    ];

    protected $tiposComThumb = [
        // TODO Preencher Tipo de fotos com thumb
    ];

    /**
     * @param File $file
     * @throws \Exception
     */
    public function creating(File $file)
    {
        if(!$this->checkIfIsFile($file->name)){
            return;
        }
        $this->copyFile($file);
    }

    public function created(File $file)
    {
        $this->fileService->removeFromTmp(
            $file->name
        );
        if (in_array($file->tipo, $this->typeWithThumb) !== false) {
            $this->makeThumb($file);
        }
    }

    /**
     * @param File $file
     * @throws \Exception
     */
    public function updating(File $file)
    {
        if($this->isNotEqual('name', $file)){
            $this->copyFile($file);
        }
    }

    public function updated(File $file)
    {
        $this->makeThumb($file);
        $this->fileService->removeFromTmp(
            $file->name
        );
    }

    /**
     * @param File $file
     */
    public function deleted(File $file)
    {
        if (in_array($file->tipo, $this->typesRemovable) !== false) {
            $file->removeFile();
            $file->removeThumb();
        }
    }

    public function checkIfIsFile($name)
    {
        return strpos($name, '.') !== true;
    }

    /**
     * @param $path
     * @param $name
     * @param $file
     * @throws \Exception
     */
    public function copyFile(File &$file)
    {
        $toPath = explode('/', $file->path);
        if($toPath[count($toPath)-1] == $file->name){
            unset($toPath[count($toPath)-1]);
        }
        $toPath = implode('/', $toPath);

        $path = $this->fileService->copyFileFromTmp(
            $file->name,
            $toPath
        );

        $file->path = $path;
        $file->url = $this->fileService->url($file->path);
        $file->extension = $this->fileService->extractExtensionFromFileName($file->name);
    }

    public function makeThumb(File $file)
    {
        if($file->isImage()){
            $this->fileService->resizeImage(File::THUMB_WIDTH, File::THUMB_HEIGHT, $file->path, $file->name_thumb);
        }
    }

}
