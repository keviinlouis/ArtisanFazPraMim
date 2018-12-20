<?php
/**
 * Created by PhpStorm.
 * User: devmaker
 * Date: 06/11/18
 * Time: 17:54
 */

namespace Louisk\Generator\Handlers;


use Louisk\Generator\Interfaces\HasBaseFile;

class TraitsHandler extends HandlerBase implements HasBaseFile
{
    public function getNamespace(): string
    {
        return $this->config['traits']['namespace'];
    }

    public function getPath(): string
    {
        return $this->config['traits']['path'];
    }

    public function getResource(): string
    {
        return 'Traits';
    }
}
