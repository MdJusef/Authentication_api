<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
//use Validator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;

class UserController extends Controller
{
    // Register
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'verified_email' => 'nullable',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'nullable',
            'code' => 'nullable'
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(),400);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'verified_email' => $request->verified_email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'code' => $this->generateCode(),
        ]);
        $this->sendOtp($user->email,$user->code);
//        $user->generateCode();
        return response()->json([
            'message' => 'User registration Successfully',
            'user' => $user
        ]);
    }

    //send otp
    public function sendOtp($email, $code)
    {
//        $validator = Validator::make($request->all(), [
//            'email' => 'required|email|exists:users',
//        ]);
//
//        if ($validator->fails()) {
//            return response(['errors' => $validator->errors()], 422);
//        }
//
//        $user = User::where('email', $request->email)->first();
//
//        $code = $user->code;

        Mail::to($email)->send(new SendOtpMail($code));

//        return response([
//            'message' => 'OTP sent successfully',
//        ]);
    }

//verified email
    public function verifiedEmail(Request $request){
        $validator = Validator::make($request->all(), [
            'code' => 'required|digits:4',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }

        $user = User::where('code', $request->code)->first();

        if (!$user) {
            return response(['message' => 'Invalid OTP'], 422);
        }
        $user->update(['verified_email'=> 1]);
        $user->update(['code'=> 0]);
        return response(['message' => 'Email verified successfully']);
    }

    //forget password
    public function forgetPassword(Request $request){
        $email = $request->email;
        $otp = sendOtp($email);
    }


    public function generateCode(){
        $this->timestamps = false;
        $this->code = rand(1000,9999);
        $this->expire_at = now()->addMinute(2);
        return $this->code;
    }

    public function resetCode(){
        $this->timestamps = false;
        $this->code = null;
        $this->expire_at = null;
        $this->save();
    }



    // Login

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|string|email',
            'password' => 'required|string|min:6'
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(),400);
        }
        if (!$token = auth()->attempt($validator->validated()))
        {
            return response() ->json(['error'=>'Unauthorized']);
        }
        return $this->responseWithToken($token);
    }
    protected function responseWithToken($token){
        return response()->json([
            'access_token'=>$token,
            'token_type'=>'bearer',
            'expires_in'=>auth()->factory()-> getTTL() * 60
        ]);
    }

    // Profile
    public function profile()
    {
        return response()->json(auth()->user());
    }

    //change password
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|different:current_password',
            'confirm_password' => 'required|string|same:new_password',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }

        $user = auth()->user();
        $user = User::get()->first();

        if (!Hash::check($request->current_password, $user->password)) {
            return response(['message' => 'Current password is incorrect'], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response(['message' => 'Password updated successfully']);
    }


    //Refresh
    public function refresh()
    {
        return $this->responseWithToken(auth()->refresh());
    }

    //logout
    public function logout()
    {
        auth()->logout();
        return response()->json(['message'=>'User Successfully Logged Out']);
    }

}
