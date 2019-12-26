<?php

namespace App\Http\Controllers;

use App\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class TestController extends Controller
{
    //
    protected $user;

    public function __construct(Request $request){
        parent::__construct($request);
//        $this->user = $users;
    }

    public function show($id)
    {
        $userInfo = User::getUserInfoById($id, ['nickname','username']);
        return $userInfo;
    }
}
