<?php
/**
 * Created by PhpStorm.
 * User: Louis
 * Date: 25/05/2018
 * Time: 22:51
 */

namespace App\Observers;


use App\Models\Address;

class AddressObserver extends Observer
{
    public function saving(Address $address)
    {
        if($address->hasLatLong() && !$address->hasAddress()){
            $address->loadAddressByLatLong();
        }
    }
    /**
     * @param Address $address
     */
    public function saved(Address $address)
    {
        if(!$address->hasLatLong() || $this->isDifferent(['zip_code', 'street', 'number', 'city', 'state'], $address)){
            $address->loadLatLong();
        }

        $address->updateRelatedModel();
    }
}
