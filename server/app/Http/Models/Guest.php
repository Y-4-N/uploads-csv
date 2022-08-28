<?php

namespace App\Http\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model{
    public static function insertData($data){

        $value=DB::table('guests')->where('name', $data['name'])->get();
        if($value->count() == 0){
            DB::table('guests')->insert($data);
        }
    }
}