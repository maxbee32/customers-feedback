<?php

namespace App\Http\Controllers;

use App\Models\Complain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function sendResponse($data, $message, $status = 200){
        $response =[
            'data' => $data,
            'message' => $message
        ];
        return response()->json($response, $status);
     }



     public function __construct(){
        $this->middleware('auth:api', ['except'=>['userLogin','custFeedback']]);
    }

     //branch login
    public function userLogin(Request $request){
        $validator = Validator::make($request->all(), [
            'email'=>['required','email:rfc,filter,dns'],
            'password'=> ['required','string'],

        ]);


        if($validator->stopOnFirstFailure()-> fails()){
            return $this->sendResponse([
                'success' => false,
                'data'=> $validator->errors(),
                'message' => 'Validation Error'
            ], 400);


        }

        if(!$token = auth()->attempt($validator->validated())){
            return $this->sendResponse([
                'success' => false,
                'data'=> $validator->errors(),
                'message' => 'Invalid login credentials'
            ], 400);


        }


        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            //  'expires_in' => config('jwt.ttl') * 60,//auth()->factory()->getTTL()* 60,
             'user'=>auth()->user(),
            'message' => "Logged in successfully"
        ],200);


    }



    public function custFeedback(Request $request){

        $branch  = auth()->user()->branch;
        $validator = Validator::make($request->all(), [

            'phone_number'=>['required','min:10'],
            'comment'=> ['required','in:No,Yes'],
            'branch'=> $branch
        ]);


       if($validator->stopOnFirstFailure()-> fails()){
        return $this->sendResponse([
            'success' => false,
            'data'=> $validator->errors(),
            'message' => 'Validation Error'
        ], 400);
    }


     Complain::create(array_merge(
        ['user_id' => optional(Auth()->user())->id],
            $validator-> validated()
    ));


    // event(new ComplainEvent($request->comment));

    return $this->sendResponse([
        'success' => true,
        'message' => 'Feedback submitted .'
    ], 200);

    }
}
