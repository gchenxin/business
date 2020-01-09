<?php

namespace App\Http\Controllers;


class TestController extends Controller
{
    //
    protected $user;

    public function __construct(){
    }

    public function show($id)
    {
        exit("alert();");
    }
}
