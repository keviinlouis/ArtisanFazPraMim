<?php
/**
 * Created by PhpStorm.
 * User: DevMaker
 * Date: 29/03/2018
 * Time: 14:06
 */

namespace DumpNamespace;


trait Password
{
    /**
     * @param String $password
     */
    public function setSenhaAttribute(String $password) : void
    {
        $this->attributes['password'] = \Hash::make($password);
    }

    public function checkSenha(string $password)
    {
        return \Hash::check($password, $this->password);
    }
}
