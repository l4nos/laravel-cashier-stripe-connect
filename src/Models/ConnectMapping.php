<?php

namespace Lanos\CashierConnect\Models;

use Illuminate\Database\Eloquent\Model;

class ConnectMapping extends Model
{

    protected $guarded = [
        "future_requirements" => "json",
        "requirements" => "json"
    ];

    public $timestamps = false;

    protected $table = 'stripe_connect_mappings';

    public function setFutureRequirementsAttribute($value){
        $this->attributes['future_requirements'] = json_encode($value);
    }

    public function setRequirementsAttribute($value){
        $this->attributes['requirements'] = json_encode($value);
    }

    public function getFutureRequirementsAttribute($value){
        if($value){
            return json_decode($value);
        }else{
            return null;
        }
    }

    public function getRequirementsAttribute($value){
        if($value){
            return json_decode($value);
        }else{
            return null;
        }
    }

}