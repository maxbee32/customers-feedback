<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Mail\PromptEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendemail:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email to branches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $date1 = Carbon::today();
        $result = DB::table('users')
        ->join('complains','users.id', '=' ,'complains.user_id')
        ->where(DB::raw('DATE(complains.created_at)',''),[$date1])
        ->select(array(
            DB::raw("SUM(CASE
            WHEN complains.comment = 'No' THEN 1  ELSE 0 END) AS No"),
            DB::raw("SUM(CASE
            WHEN  complains.comment = 'Yes' THEN 1 ELSE 0 END) AS Yes"),
            DB::raw("COUNT(Complains.comment) As comment"),
            DB::raw("DATE(complains.created_at) As date"),
            'email', 'branch'))
        ->groupby('branch','email', 'date')
        ->get();

        // customerfeedbackapp@izweghana.com
        $No =$result->pluck('No');
        $Yes =$result->pluck('Yes');
        $email = $result->pluck('email');

         foreach ($result as $result){
            if($No > $Yes){

            $email = $email;



        // if($result->pluck('No') > $result->pluck('Yes')){


    //    Mail::to($email)->cc('customerfeedbackapp@izweghana.com')->send(new PromptEmail($result));
          Mail::to($email)->send(new PromptEmail($result));
      }
 }
  return 0;
    }
}
