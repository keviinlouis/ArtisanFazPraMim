<?php
/**
 * Created by PhpStorm.
 * User: DevMaker
 * Date: 02/03/2018
 * Time: 18:45
 */

namespace App\Observers;

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

    protected $tiposRemoviveisAoRemoverDoBanco = [
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
        if(!$this->checkIfIsFile($file->nome)){
            return;
        }
        $this->copyFile($file);
    }

    public function created(File $file)
    {
        $this->fileService->removeFromTmp(
            $file->nome
        );
        if (in_array($file->tipo, $this->tiposComThumb) !== false) {
            $this->makeThumb($file);
        }
    }

    /**
     * @param File $file
     * @throws \Exception
     */
    public function updating(File $file)
    {
        if($this->isNotEqual('nome', $file)){
            $this->copyFile($file);
        }
    }

    public function updated(File $file)
    {
        $this->makeThumb($file);
        $this->fileService->removeFromTmp(
            $file->nome
        );
    }

    /**
     * @param File $file
     */
    public function deleted(File $file)
    {
        if (in_array($file->tipo, $this->tiposRemoviveisAoRemoverDoBanco) !== false) {
            $file->removeFile();
            $file->removeThumb();
        }
    }

    public function checkIfIsFile($nome)
    {
        return strpos($nome, '.') !== true;
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
        if($toPath[count($toPath)-1] == $file->nome){
            unset($toPath[count($toPath)-1]);
        }
        $toPath = implode('/', $toPath);

        $path = $this->fileService->copyFileFromTmp(
            $file->nome,
            $toPath
        );

        $file->path = $path;
        $file->url = $this->fileService->url($file->path);
        $file->extensao = $this->fileService->extractExtensionFromFileName($file->nome);
    }

    public function makeThumb(File $file)
    {
        if($file->isImage()){
            $this->fileService->resizeImage(File::THUMB_WIDTH, File::THUMB_HEIGHT, $file->path, $file->nome_thumb);
        }
    }

}
