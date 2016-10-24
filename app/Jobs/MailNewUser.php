<?php

namespace App\Jobs;

use App\User, App\Report;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class MailNewUser extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $userID;
    protected $userName;
    protected $userEmail;

    /**
     * Create a new job instance.*
     */
    public function __construct($userID)
    {
        $this->userID = $userID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find($this->userID);
        $this->userName = $user->first_name.' '.$user->last_name;
        $this->userEmail = $user->email;

        $data = array(
            'username'      =>  $this->userName,
        );

        /* Segregates each email */
        Mail::send('emails.users.welcome', $data, function ($message) {
            $message->to($this->userEmail, $this->userName)->subject('Welcome to Bootmark');
        });

    }
}
