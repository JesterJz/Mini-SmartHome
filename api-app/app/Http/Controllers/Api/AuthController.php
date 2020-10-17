<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;  
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // login
    public function login(Request $request){
        $creds =$request->only(['email','password']);
        
        if(!$token=auth()->attempt($creds)){
            return response()->json([
                'success' => false,
                'message' => 'invalid credintials'
            ]);
        }
        return response()->json([
            'success'   => true,
            'token'     => $token,
            'user'      => Auth::user()
        ]);
    }

    // register
    public function register(Request $request){
        // ma hoa password 
       $encryptedPass = Hash::make($request->password);
       $user = new User;
       try {
            // $user->name     = $request->name;
            $user->email    = $request->email;
            $user->password = $encryptedPass;
            $user->save();
            return $this->login($request);

       } catch (Exception $e) {
           return response()->json([
               'success'    => false,
               'message'    => ''.$e
           ]);
       }
    }

    // logout
    public function logout (Request $request) {
        try {
            JWTAuth::invalidate(JWTAuth::parseToken($request->token));
            return response()->json([
                'success'   => true,
                'message'   => 'logout success'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success'   => false,
                'message'   => ''.$e
            ]);
            
        }
    }

    // Save user info name, lastname , photo
    public function saveUserInfo (Request $request){
        $user = User::find(Auth::user()->id);
        $user->name = $request->name;
        $user->lastname = $request->lastname;
        $photo ='';
        // kiem tra neu chon anh
         if($request->photo!=''){
             // luu ten anh khong bi trung
             $photo = time().'.jpg';
             // giai ma anh va luu lai
             file_put_contents('storage/profiles/'.$photo,base64_decode($request->photo));
            $user->photo = $photo;
        }
        $user->update();

        return response()->json([
            'success'   => true,
            'photo'     => $photo
        ]);
    }










}
