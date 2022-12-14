<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;


class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function getLogin(){
        return view('auth.login');
    }
    public function postLogin(Request $request){


        $validate = Validator::make($request->all(),[
            'username' => 'required',
            'password' => 'required'
        ]);

        if($validate->fails()){
            return redirect()->back()
                ->withErrors($validate)
                ->withInput();
        }
        $name = $request->username;
        $password = $request->password;
        if(filter_var($name, FILTER_VALIDATE_EMAIL)) {
            if(Auth::attempt(['email'=>$name,'password'=>$password])){
                return \redirect()->intended('/');
            }else{
                return back()->withInput()->with('error','Mật khẩu hoặc tài khoản không đúng!');
            }
        }else{
            if(Auth::attempt(['name'=>$name,'password'=>$password])){
                return \redirect()->intended('/');
            }else{
                return back()->withInput()->with('error','Mật khẩu hoặc tài khoản không đúng!');
            }
        }



    }
    public function logout(){
        session()->flush();
        Auth::logout();
        return \redirect()->intended('login');
    }

}
