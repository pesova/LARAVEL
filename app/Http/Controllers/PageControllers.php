<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageControllers extends Controller
{
    //

    public function MyWork(){
        $data = array(
            "Name" => "Pesova",
            "Age" => 19,
            "Dept" => "Abe",
            "Level" => 500
        );

        return response(json_encode($data));
    }
}
