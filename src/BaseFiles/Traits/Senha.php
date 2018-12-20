<?php
/**
 * Created by PhpStorm.
 * User: DevMaker
 * Date: 29/03/2018
 * Time: 14:06
 */

namespace DumpNamespace;


trait Senha
{
    /**
     * @param String $senha
     */
    public function setSenhaAttribute(String $senha) : void
    {
        $this->attributes['senha'] = \Hash::make($senha);
    }

    public function checkSenha(string $senha)
    {
        return \Hash::check($senha, $this->senha);
    }
}
