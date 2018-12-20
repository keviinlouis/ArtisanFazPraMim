<?php

namespace DumpNamespace;

use App\Traits\AttributesMasks;
use App\Traits\Files;
use App\Traits\Senha;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

abstract class BaseUser extends Authenticatable implements JWTSubject
{
	use SoftDeletes, AttributesMasks, Senha, Files;

    /**
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims() : array
    {
        return [
            'class' => static::class
        ];
    }
}
