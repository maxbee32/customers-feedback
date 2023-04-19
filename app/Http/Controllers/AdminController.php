<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Mail\PromptEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function sendResponse($data, $message, $status = 200){
        $response =[
            'data' => $data,
            'message' => $message
        ];
        return response()->json($response, $status);
     }






     public function __construct(){
        $this->middleware('auth:api', ['except'=>['adminSignUp', 'branchSignUp','adminLogin','getAllComplains','getComplainToday','getAllBranch','allfeedbackchart',
        'feedbackChartPeriod','cardData', 'cardData1','cardData2','cardData3','commentfor7','commentfor30','commentfor90', 'commentfor365','deleteUser','singleBranch',
        'singleBranchWithDate']]);
    }
    //admin account created
    public function adminSignUp(Request $request){
        $validator = Validator::make($request->all(), [

            'email' => ['required','email','unique:admins'],
            'password' => ['required']

        ]);

        if($validator->stopOnFirstFailure()-> fails()){
            return $this->sendResponse([
                'success' => false,
                'data'=> $validator->errors(),
                'message' => 'Validation Error'
            ], 400);

        }
           Admin::create(array_merge(
                    $validator-> validated(),
                    ['password'=>bcrypt($request->password)]
                ));


                return $this->sendResponse([
                    'success' => true,
                    'message' => "Admin account created successfully"
                ],200);
}


    //register branch users
    public function branchSignUp(Request $request){
        $validator = Validator::make($request->all(), [

            'email' => ['required','email','unique:users'],
            'password' => ['required'],
            'branch'=> ['required','unique:users']

        ]);

        if($validator->stopOnFirstFailure()-> fails()){
            return $this->sendResponse([
                'success' => false,
                'data'=> $validator->errors(),
                'message' => 'Validation Error'
            ], 400);

        }
           User::create(array_merge(
                    $validator-> validated(),
                    ['password'=>bcrypt($request->password)]
                ));



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
                    'message' => "branch account created successfully"
                ],200);




}




public function adminLogin(Request $request){
    $validator = Validator::make($request->all(), [
        'email'=>['required','email:rfc,filter,dns'],
        'password'=> ['required','string'],
        // 'branch'=>['required','string']
    ]);


    if($validator->stopOnFirstFailure()-> fails()){
        return $this->sendResponse([
            'success' => false,
            'data'=> $validator->errors(),
            'message' => 'Validation Error'
        ], 400);


    }
    return $this->sendResponse([
        'success' => true,
        'message' => "Logged in successfully"
    ],200);


}

public function getAllComplains(){
   $result = DB::table('complains')
       ->join('users', 'complains.user_id', '=' ,'users.id')
       ->orderBy("complains.created_at", 'desc')
       ->get(array(
            'complains.id',
            'branch',
            'phone_number',
            'comment',
            DB::raw("DATE(complains.created_at) AS Date" ),

       ));

       return $this ->sendResponse([
        'success' => true,
         'message' => $result,

       ],200);


}

public function getComplainToday(Request $request){
    $validator = Validator::make($request->all(), [
        'start_date' => ['required','date'],
        'end_date' => ['required','date','after_or_equal:start_date'],

    ]);



    if($validator-> fails()){
        return $this->sendResponse([
            'success' => false,
            'data'=> $validator->errors(),
            'message' => 'Validation Error'
        ], 400);

    }

    $startDate =carbon::parse($request->start_date);
    $endDate = carbon::parse($request->end_date);

    $results=  DB::table('complains')
    ->join('users', 'complains.user_id', '=' ,'users.id')
     -> whereBetween(DB::raw('DATE(complains.created_at)'),  [$startDate, $endDate])
    ->get(array(
          'users.id',
         'branch',
         'phone_number',
         'comment'
    ));

    return $this ->sendResponse([
        'success' => true,
         'message' => $results,

       ],200);



 }

 public function getAllBranch(){
    $res= DB::table('users')
    ->orderBy("users.created_at", 'desc')
    ->get(array(
        'id',
        'email',
        'branch'
    ));
    return $this ->sendResponse([
        'success' => true,
         'message' => $res,

       ],200);


 }

 public function allfeedbackchart(){
    $result = DB::table('complains')
    ->join('users', 'complains.user_id', '=' ,'users.id')
    ->select(array(
        DB::raw("SUM(CASE
        WHEN complains.comment = 'No' THEN 1  ELSE 0 END) AS No"),
        DB::raw("SUM(CASE
        WHEN  complains.comment = 'Yes' THEN 1 ELSE 0 END) AS Yes"),
        'branch'))
    ->groupby('branch')
    ->get();
        array(
         'branch',
         'No',
         'Yes'
    );

    return $this ->sendResponse([
     'success' => true,
      'message' => $result,

    ],200);
 }



 public function feedbackChartPeriod(Request $request){
    $validator = Validator::make($request->all(), [
        'start_date' => ['required','date'],
        'end_date' => ['required','date','after_or_equal:start_date'],

    ]);



    if($validator-> fails()){
        return $this->sendResponse([
            'success' => false,
            'data'=> $validator->errors(),
            'message' => 'Validation Error'
        ], 400);

    }

    $startDate =carbon::parse($request->start_date);
    $endDate = carbon::parse($request->end_date);
    $result = DB::table('complains')
    ->join('users', 'complains.user_id', '=' ,'users.id')
    -> whereBetween(DB::raw('DATE(complains.created_at)'),  [$startDate, $endDate])
    ->select(array(
        DB::raw("SUM(CASE
        WHEN complains.comment = 'No' THEN 1  ELSE 0 END) AS No"),
        DB::raw("SUM(CASE
        WHEN  complains.comment = 'Yes' THEN 1 ELSE 0 END) AS Yes"),
        'branch'))
    ->groupby('branch')
    ->get();
        array(
         'branch',
         'No',
         'Yes'
    );

    return $this ->sendResponse([
     'success' => true,
      'message' => $result,

    ],200);
 }

 public function cardData(){
    $date = \Carbon\Carbon::today()->subDays(7);
   $date1 = Carbon::today();
    $result = DB::table('complains')
    ->join('users', 'complains.user_id', '=' ,'users.id')
    -> whereBetween(DB::raw('DATE(complains.created_at)'),[$date, $date1 ])
    ->select(array(
        DB::raw("SUM(CASE
        WHEN complains.comment = 'No' THEN 1  ELSE 0 END) AS No"),
        DB::raw("SUM(CASE
        WHEN  complains.comment = 'Yes' THEN 1 ELSE 0 END) AS Yes"),
       // 'branch'))
         DB::raw('COUNT(DISTINCT branch) as branch')))
    //  ->groupby('branch')
    ->get();
        array(
         'branch',
         'No',
         'Yes'
    );

    return $this ->sendResponse([
        'success' => true,
         'message' => $result,

       ],200);
 }


 public function cardData1(){
    $date = \Carbon\Carbon::today()->subDays(30);
   $date1 = Carbon::today();
    $result = DB::table('complains')
    ->join('users', 'complains.user_id', '=' ,'users.id')
    -> whereBetween(DB::raw('DATE(complains.created_at)'),[$date, $date1 ])
    ->select(array(
        DB::raw("SUM(CASE
        WHEN complains.comment = 'No' THEN 1  ELSE 0 END) AS No"),
        DB::raw("SUM(CASE
        WHEN  complains.comment = 'Yes' THEN 1 ELSE 0 END) AS Yes"),
         DB::raw('COUNT(DISTINCT branch) as branch')))
    //  ->groupby('branch')
    ->get();
        array(
         'branch',
         'No',
         'Yes'
    );

    return $this ->sendResponse([
        'success' => true,
         'message' => $result,

       ],200);
 }


 public function cardData2(){
    $date = \Carbon\Carbon::today()->subDays(90);
   $date1 = Carbon::today();
    $result = DB::table('complains')
    ->join('users', 'complains.user_id', '=' ,'users.id')
    -> whereBetween(DB::raw('DATE(complains.created_at)'),[$date, $date1 ])
    ->select(array(
        DB::raw("SUM(CASE
        WHEN complains.comment = 'No' THEN 1  ELSE 0 END) AS No"),
        DB::raw("SUM(CASE
        WHEN  complains.comment = 'Yes' THEN 1 ELSE 0 END) AS Yes"),
       // 'branch'))
         DB::raw('COUNT(DISTINCT branch) as branch')))
    //  ->groupby('branch')
    ->get();
        array(
         'branch',
         'No',
         'Yes'
    );

    return $this ->sendResponse([
        'success' => true,
         'message' => $result,

       ],200);
 }

 public function cardData3(){
//     $date = \Carbon\Carbon::today()->subDays(365);
//    $date1 = Carbon::today();
    $result = DB::table('complains')
    ->join('users', 'complains.user_id', '=' ,'users.id')
    // -> whereBetween(DB::raw('DATE(complains.created_at)'),[$date, $date1 ])
    ->select(array(
        DB::raw("SUM(CASE
        WHEN complains.comment = 'No' THEN 1  ELSE 0 END) AS No"),
        DB::raw("SUM(CASE
        WHEN  complains.comment = 'Yes' THEN 1 ELSE 0 END) AS Yes"),
        DB::raw("COUNT(Complains.comment) As comment"),
         DB::raw('COUNT(DISTINCT branch) as branch')
         ))
    //  ->groupby('branch')
    ->get();
        array(
         'branch',
         'No',
         'Yes'
    );

    return $this ->sendResponse([
        'success' => true,
         'message' => $result,

       ],200);
 }


 public function commentfor7(){
    $date = \Carbon\Carbon::today()->subDays(7);
   $date1 = Carbon::today();
    $result = DB::table('complains')
    ->join('users', 'complains.user_id', '=' ,'users.id')
    -> whereBetween(DB::raw('DATE(complains.created_at)'),[$date, $date1 ])
    ->select(array(
        'users.id',
        DB::raw("SUM(CASE
        WHEN complains.comment = 'No' THEN 1  ELSE 0 END) AS No"),
        DB::raw("SUM(CASE
        WHEN  complains.comment = 'Yes' THEN 1 ELSE 0 END) AS Yes"),
        'branch'))
      ->groupby('branch','users.id')
    ->get();
        array(
         'branch',
         'No',
         'Yes'
    );

    return $this ->sendResponse([
        'success' => true,
         'message' => $result,

       ],200);
 }


 public function commentfor30(){
    $date = \Carbon\Carbon::today()->subDays(30);
   $date1 = Carbon::today();
    $result = DB::table('complains')
    ->join('users', 'complains.user_id', '=' ,'users.id')
    -> whereBetween(DB::raw('DATE(complains.created_at)'),[$date, $date1 ])
    ->select(array(
        'users.id',
        DB::raw("SUM(CASE
        WHEN complains.comment = 'No' THEN 1  ELSE 0 END) AS No"),
        DB::raw("SUM(CASE
        WHEN  complains.comment = 'Yes' THEN 1 ELSE 0 END) AS Yes"),
        'branch'))
      ->groupby('branch','users.id')
    ->get();
        array(
         'branch',
         'No',
         'Yes'
    );

    return $this ->sendResponse([
        'success' => true,
         'message' => $result,

       ],200);
 }


 public function commentfor90(){
    $date = \Carbon\Carbon::today()->subDays(90);
   $date1 = Carbon::today();
    $result = DB::table('complains')
    ->join('users', 'complains.user_id', '=' ,'users.id')
    -> whereBetween(DB::raw('DATE(complains.created_at)'),[$date, $date1 ])
    ->select(array(
        'users.id',
        DB::raw("SUM(CASE
        WHEN complains.comment = 'No' THEN 1  ELSE 0 END) AS No"),
        DB::raw("SUM(CASE
        WHEN  complains.comment = 'Yes' THEN 1 ELSE 0 END) AS Yes"),
        'branch'))
      ->groupby('branch','users.id')
    ->get();
        array(
         'branch',
         'No',
         'Yes'
    );

    return $this ->sendResponse([
        'success' => true,
         'message' => $result,

       ],200);
 }




 public function commentfor365(){
    $date1 = Carbon::today();
        $result = DB::table('users')
        ->join('complains','users.id', '=' ,'complains.user_id')
        ->where(DB::raw('DATE(complains.created_at)',''),[$date1])
        ->select(array(
            DB::raw("SUM(CASE
            WHEN complains.comment = 'No' THEN 1  ELSE 0 END) AS no"),
            DB::raw("SUM(CASE
            WHEN  complains.comment = 'Yes' THEN 1 ELSE 0 END) AS yes"),
            DB::raw("COUNT(Complains.comment) As comment"),
            DB::raw("DATE(complains.created_at) As date"),
            'email', 'branch'))
        ->groupby('branch','email', 'date')
        ->get();

        // customerfeedbackapp@izweghana.com
        $No =$result->pluck('No');
        $Yes =$result->pluck('Yes');
        $email = $result->pluck('email');

        // echo($result->pluck('yes'));

         foreach ($result as $result){
            if($No > $Yes){

            $email = $email;



        // if($result->pluck('No') > $result->pluck('Yes')){


    //    Mail::to($email)->cc('customerfeedbackapp@izweghana.com')->send(new PromptEmail($result));
        //    Mail::to($email)->send(new PromptEmail($result));
      }
 }
 return $this ->sendResponse([
    'success' => true,
     'message' => $result,

   ],200);
//   return 0;

    }



 public function deleteUser($id){
 $user = User::find($id);
 if (is_null($user)){
    return $this ->sendResponse([
        'success' => true,
         'message' => 'Branch manager not found.'

       ],200);
   }

   else {
     DB::beginTransaction();
     try{
        $user->delete();
        DB::commit();
        return $this ->sendResponse([
            'success' => true,
             'message' => 'Account has been permanently removed from the system.'

           ],200);
     } catch(Exception $err){
        DB::rollBack();
     }


}
 }


 public function singleBranch(Request $request){
    $validator = Validator::make($request->all(), [
        'branch' => ['required','string'],

    ]);

    if($validator-> fails()){
        return $this->sendResponse([
            'success' => false,
            'data'=> $validator->errors(),
            'message' => 'Validation Error'
        ], 400);

    }

    $branch =$request->branch;
    $result = DB::table('complains')
    ->join('users', 'complains.user_id', '=' ,'users.id')
    -> where(DB::raw('branch'),  [$branch])
    ->select(array(
        DB::raw("SUM(CASE
        WHEN complains.comment = 'No' THEN 1  ELSE 0 END) AS No"),
        DB::raw("SUM(CASE
        WHEN  complains.comment = 'Yes' THEN 1 ELSE 0 END) AS Yes"),
        DB::raw("COUNT(Complains.comment) As comment"),
        'branch'))
    ->groupby('branch')
    ->get();
        array(
         'branch',
         'No',
         'Yes'
    );

    return $this ->sendResponse([
     'success' => true,
      'message' => $result,

    ],200);
 }



 public function singleBranchWithDate(Request $request){
    $validator = Validator::make($request->all(), [
        'branch' => ['required','string'],
        'start_date' => ['required','date'],
        'end_date' => ['required','date','after_or_equal:start_date']

    ]);

    if($validator-> fails()){
        return $this->sendResponse([
            'success' => false,
            'data'=> $validator->errors(),
            'message' => 'Validation Error'
        ], 400);

    }

    $startDate =carbon::parse($request->start_date);
    $endDate = carbon::parse($request->end_date);
    $branch =$request->branch;
    $result = DB::table('complains')
    ->join('users', 'complains.user_id', '=' ,'users.id')
    -> where(DB::raw('branch'),  [$branch])
    -> whereBetween(DB::raw('DATE(complains.created_at)'),  [$startDate, $endDate])
    ->select(array(
        DB::raw("SUM(CASE
        WHEN complains.comment = 'No' THEN 1  ELSE 0 END) AS No"),
        DB::raw("SUM(CASE
        WHEN  complains.comment = 'Yes' THEN 1 ELSE 0 END) AS Yes"),
        DB::raw("COUNT(Complains.comment) As comment"),
        'branch'))
    ->groupby('branch')
    ->get();
        array(
         'branch',
         'No',
         'Yes'
    );

    return $this ->sendResponse([
     'success' => true,
      'message' => $result,

    ],200);
 }


}
