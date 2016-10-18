<?php

namespace App\Jobs;

use App\User, App\Report;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class MailReport extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $reportID;
    protected $reporterName;
    protected $reporterEmail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($reportID)
    {
        $this->reportID = $reportID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $report = Report::find($this->reportID);
        $reporterInfo = User::find($report->reporter_id);
        $this->reporterName = $reporterInfo->first_name.' '.$reporterInfo->last_name;
        $this->reporterEmail = $reporterInfo->email;

        $data = array(
            'id'            =>  $report->id,
            'reporter_id'   =>  $report->reporter_id,
            'bodyMessage'   =>  $report->message,
            'status'        =>  $report->status,
        );

        if (!($report->bootmark_id == null)) {
            $view = 'report-bootmark';
            $data['bootmark_id'] = $report->bootmark_id;
        } else if (!($report->comment_id == null)) {
            $view = 'report-comment';
            $data['comment_id'] = $report->bootmark_id;
        } else {
            $view = 'report-user';
            $data['user_id'] = $report->bootmark_id;
        }

        /* Segregates each email */
        Mail::send($view, $data, function ($message) {
            $message->to('Info@bootmark.ca', 'Bootmark Team')->subject('Report has been generated');
        });

        Mail::send($view, $data, function ($message) {
            $message->to($this->reporterEmail, $this->reporterName)->subject('Bootmark - Your report has been received!');
        });
    }
}
