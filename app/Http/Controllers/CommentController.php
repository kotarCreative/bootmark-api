<?php

namespace App\Http\Controllers;

use App\Jobs\MailReport;
use Illuminate\Http\Request;

use App\Comment, App\Report, App\Bootmark, App\User;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Gets the comments for the bootmark.
     *
     * @param int $bootmark The id of the bootmark to retrieve comments for.
     *
     * @return array Returns an array of comments that are connected to the bootmark.
     */
    public function index($bootmark)
    {
        $bootmark = Bootmark::find($bootmark);
        if($bootmark) {
            $comments = $bootmark->comments()->orderBy('created_at', 'asc')->paginate(10);

            foreach($comments as $comment) {
                $user = User::find($comment->user_id);
                $comment->username = $user->name;
            }

            return response()->json([
                'reponse' => 'success',
                'comments' => $comments
            ]);
        } else {
            return response()->json([
                'response' => 'failure',
                'message' => 'The bootmark requested does not exist'
            ], 422);
        }
    }

    /**
     * Posts a comment to a specific bootmark.
     *
     * @param int $bootmark The id of the bootmark being commented on.
     * @param Request $request The request object containing all the inputs.
     *
     * @return Response Returns a json response based on the outcome of the action.
     */
    public function store($bootmark, Request $request)
    {
        $this->validate($request, [
            'comment' => 'required'
        ]);

        $user = Auth::user();
        $bootmark = Bootmark::find($bootmark);
        $comment = new Comment();
        $comment->user_id = $user->id;
        $comment->comment = $request->input('comment');

        $bootmark->comments()->save($comment);

        return response()->json([
            'status' => 200,
            'message' => 'The bootmark has been commented on.',
            'user_id' => $user->id,
            'username' => $user->name,
            'created_at' => $comment->created_at->toDateString()
        ]);
    }

    /**
     * Generates a report for the specified comment
     *
     * @param int $comment The comment that is being report.
     * @param request $request The request object containing all the inputs.
     *
     * @return Returns a success message or a failure message.
     */
    public function report($comment, Request $request)
    {
        $user = Auth::user();
        $reporter_id = $user->id;

        /* Retrieves the selected comment */
        $comment = Comment::where('id', $comment)->first();
        if ($comment == null) {
            return response()->json([
                'response' => 'failure',
                'message' => 'Comment not found',
            ], 404);
        }

        /* Creates a new report */
        $report = new Report;
        $report->reporter_id = $reporter_id;
        $report->comment_id = $comment->id;
        $report->message = $request->input('message');
        $report->status = "Report received";

        $report->save();

        dispatch(new MailReport($report->id));

        return response()->json([
            'response' => 'success',
            'message' => 'Comment has been reported'
        ]);
    }
}
