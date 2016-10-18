<?php

namespace App\Http\Controllers;

use App\Jobs\MailReport;
use Illuminate\Http\Request;

use App\Comment, App\Report;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{

    /**
     * Generates a report for the specified comment
     *
     * @param int $commendID The comment that is being report.
     * @param request $request The request object containing all the inputs.
     *
     * @return Returns a success message or a failure message.
     */
    public function report($commendID, Request $request)
    {
        $reporter_id = Auth::user()->id;

        /* Retrieves the selected comment */
        $comment = Comment::where('id', $commendID)->first();
        if ($comment == null) {
            return response()->json([
                'response' => 'failure',
                'message' => 'Comment not found',
            ], 404);
        }

        /* Creates a new report */
        $report = new Report;
        $report->reporter_id = $reporter_id;
        $report->comment_id = $commendID;
        $report->message = $request->input('message');
        $report->status = "Report received";

        $report->save();

        dispatch(new MailReport($report->id));

        return response()->json([
            'response' => 'success',
            'message' => 'Comment has been reported',
        ]);
    }
}
