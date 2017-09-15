<?php

namespace App\Jobs;

use App\Models\User, App\Models\Report;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class MailNewUser extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $userID;
    protected $username;
    protected $email;

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
        $this->email = $user->email;

        $data = array(
            'username'  =>  $user->name,
        );

        /* Segregates each email */
        Mail::send('emails.users.welcome', $data, function ($message) {
            $message->to($this->email, $this->username)->subject('Welcome to Bootmark');
        });

    }
}
