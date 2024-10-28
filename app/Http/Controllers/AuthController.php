<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request){
         $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:50|unique:users',
                'password' => 'required|string|min:8',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

        try{
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'id_role' => "1",
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json(
                [
                    'data' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'message' => 'User successfully registered'
                ], 201);

        }catch(\Exception $e){
             return response()->json(
                [
                    'message' => 'Failed registered',
                    'data' => $e->getMessage()
                ], 500);

        }
       
    }

     public function login(Request $request){

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }
        try{
            $user = User::where('email', $request['email'])->firstOrFail();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([

                'data' => $user,
                'message' => 'success',
                'access_token' => $token,
                'token_type' => 'Bearer',

            ], 200);
        }catch(\Exception $e){

    
            return response()->json([

                'message' => 'Failed Logi',
                'data' => $e->getMessage(),
               

            ], 200);
        }
        
    }

}
