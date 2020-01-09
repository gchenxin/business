<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Model\LoginRecord;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->middleware('guest')->except('logout');
    }

    public function checkLogin(User $userModel){
        $test = $userModel->checkLogin(['username'=> $this->username,'password'=>$this->password]);
        if (!is_array($test)){
            self::exception($test);
        }
        return $test;
    }

    public function logout(LoginRecord $loginRecordModel,$uid){
        $loginRecordModel->where(['userid' => $uid,'state' => 1,'loginIp'=>$this->clientIp])->delete();
        return [];
    }
}
