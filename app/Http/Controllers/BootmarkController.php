<?php

namespace App\Http\Controllers;

use App\Models\HttpResponse;
use App\Jobs\MailReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\SearchBootmarks as Search;

use App\Models\Link, App\Models\Media, App\Models\Photo, App\Models\Bootmark, App\Models\User, App\Models\Follower,
    App\Models\Vote, App\SimpleScraper, App\Models\Report;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class BootmarkController extends Controller
{

    /**
    * Returns all the bootmarks based on the filters passed.
    *
    * @param Request $request The request object containing all the inputs.
    *
    * @return mixed Returns a json array of all bootmarks.
    */
    public function index(Request $request)
    {
        $user_id = Auth::user()->id;
        $this->validate($request, [
            'lat'               => 'required|numeric',
            'lng'               => 'required|numeric',
            //'rad'               => 'required|numeric',
            //'discoverable'      => 'required|boolean',
            //'filter'            => ['required', Rule::in(['popular', 'newest', 'closest'])]
        ]);

        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $rad = $request->input('rad');
        $discoverable = $request->input('discoverable');

        /* Create the join for media and links meta data */
        $bootmarks = DB::table('bootmarks')
            ->leftJoin('media','bootmarks.media_id','=','media.id')
            ->leftJoin('links','bootmarks.link_id','=','links.id');

        $bootmarks = $bootmarks
            ->leftJoin(DB::raw("(select * from votes where votes.user_id = $user_id) v"),'bootmarks.id', '=', 'v.bootmark_id')
            ->distinct();

        $discovered = User::find($user_id)->discoveredBootmarks;

        if($discoverable == '1') {
            /* Remove regular bootmarks from return */
            $bootmarks = $bootmarks->where('discoverable', true);
        } else {
            /* Remove discoverable bootmarks from return */
            $bootmarks = $bootmarks->where('discoverable', false);
        }

        /* Applies the filter that has been selected */
        //$distance_select = "earth_distance(ll_to_earth($lat,$lng), ll_to_earth(lat, lng)) as distance_from_current";

        /* POSTGIS application of distance select */
        $distance_select = "ST_Distance(ST_GeographyFromText('SRID=4326;POINT($lng $lat)'), coordinates) as distance_from_current";

        if($request->input('filter') == 'closest') {
            $bootmarks = $bootmarks->orderBy('distance_from_current', 'asc');
        } else if($request->input('filter') == 'popular') {
            $filter_days = 30;
            $bootmarks = $bootmarks->where('bootmarks.created_at', '>', Carbon::now()->subDay($filter_days))
                                    ->orderBy('karma','desc');
        } else {
            $bootmarks = $bootmarks->orderBy('bootmarks.created_at','desc');
        }

        /* Finds all bootmarks within the radius given. */
        if($request->has('rad')) {
            //$bootmarks->whereRaw("earth_box(ll_to_earth($lat,$lng), $rad) @> ll_to_earth(lat, lng)");
            $bootmarks->whereRaw("ST_DWithin(coordinates, ST_GeographyFromText('SRID=4326;POINT($lng $lat)'), $rad)");
        }

        $bootmarks = $bootmarks->join('users','bootmarks.user_id','=','users.id');

        /* Select required data and paginate results */
        if($request->has('rad')) {
            $bootmarks = $bootmarks->select(
                        DB::raw($distance_select),
                        'users.name',
                        'bootmarks.*',
                        'links.url',
                        'links.title',
                        'links.meta_description',
                        'links.image_path',
                        'media.media_type',
                        'media.path',
                        'media.mime_type',
                        'v.vote')->get();
        } else {
            $bootmarks = $bootmarks->select(
                        DB::raw($distance_select),
                        'users.name',
                        'bootmarks.*',
                        'links.url',
                        'links.title',
                        'links.meta_description',
                        'links.image_path',
                        'media.media_type',
                        'media.path',
                        'media.mime_type',
                        'v.vote')->simplePaginate(20);
        }

        /* Get a count of comments made on each bootmark being returned. */
        foreach($bootmarks as $bootmark) {
            $temp = Bootmark::find($bootmark->id);
            $comment_count = $temp->comments()->count();
            $bootmark->comments = $comment_count;
        }

        if($request->has('rad')) {
            return response()->json([
                'response' => 'success',
                'bootmarks' => [ 'data' => $bootmarks ]
            ]);
        } else {
            return response()->json([
                'response' => 'success',
                'bootmarks' => $bootmarks
            ]);
        }
    }

    /**
     * Returns a cluster of bootmarks for a specific quadrant.
     *
     * @param Request $request
     */
    public function cluster(Request $request)
    {
        $user_id = Auth::user()->id;

        $this->validate($request, [
            'northWest'  => 'required', 'northWest.lng'  => 'required', 'northWest.lat'  => 'required',
            'northEast'  => 'required', 'northEast.lng'  => 'required', 'northEast.lat'  => 'required',
            'southWest'  => 'required', 'southWest.lng'  => 'required', 'southWest.lat'  => 'required',
            'southEast'  => 'required', 'southEast.lng'  => 'required', 'southEast.lat'  => 'required',
            'zoomLevel'  => 'required'
        ]);

        $div_amt = $this->getDividerAmount($request->input("zoomLevel"));

        $north_west = $request->input("northWest");
        $north_east = $request->input("northEast");
        $south_west = $request->input("southWest");
        $south_east = $request->input("southEast");

        if (floatval($north_west["lng"]) > floatval($north_east["lng"])) {
            $grid_width = ((180 - floatval($north_west["lng"])) + (180 - abs(floatval($north_east["lng"])))) / $div_amt;
        } else {
            $grid_width = abs(floatval($north_west["lng"]) - floatval($north_east["lng"])) / $div_amt;
        }
        $grid_height = abs(floatval($north_west["lat"]) - floatval($south_west["lat"])) / $div_amt;

        $bootmarks = [];

        for($i = 0; $i < $div_amt; $i++) {
            for($x = 0; $x < $div_amt; $x++) {
	            $nw_lat = $north_west["lat"] - ($grid_height * $i);
                $nw_lng = $this->calc_coord($north_west["lng"], $grid_width, $x);

                $se_lat = $north_west["lat"] - ($grid_height * ($i + 1));
                $se_lng = $this->calc_coord($north_west["lng"], $grid_width, $x + 1);
                //dd(array("nw_lat"=>$nw_lat, "nw_lng"=>$nw_lng, "se_lat"=>$se_lat, "se_lng"=>$se_lng, "grid_width"=>$grid_width, "grid_height"=>$grid_height));

                /* ST_MakeEnvelope(LEFT, BOTTOM, RIGHT, TOP, SRID) -- https://gis.stackexchange.com/questions/25797/select-bounding-box-using-postgis */
                $grid_query = Bootmark::selectRaw("id, location, description, lat, lng, discoverable")
                               ->whereExists(function($query) use ($nw_lat, $nw_lng, $se_lat, $se_lng) {
                                   if ($nw_lng > $se_lng) {
                                        $envelope_1 = "ST_MakeEnvelope($nw_lng, $se_lat, 180, $nw_lat, 4326)";
                                        $envelope_2 = "ST_MakeEnvelope(-180, $se_lat, $se_lng, $nw_lat, 4326)";
                                        $query->select(DB::raw(1))
                                              ->whereRaw("(geometry(coordinates) && $envelope_1) OR (geometry(coordinates) && $envelope_2)");
                                   } else {
                                        $envelope = "ST_MakeEnvelope($nw_lng, $se_lat, $se_lng, $nw_lat, 4326)";
                                        $query->select(DB::raw(1))
                                              ->whereRaw("geometry(coordinates) && $envelope");
                                   }
                               })->get();

                $count = $grid_query->count();
                switch ($count) {
                case 0:
                    $markers[] = [ 'lat' => '', 'lng' => '', 'count' => $count, 'bootmarks' => $grid_query ];
                    break;
                case 1:
                    $markers[] = [ 'lat' => $grid_query[0]["lat"], 'lng' => $grid_query[0]["lng"], 'count' => $count, 'bootmarks' => $grid_query ];
                    break;
                default:
                    /* Get the lats and lngs for bootmarks */
                    $lats = $grid_query->pluck('lat');
                    $lngs = $grid_query->pluck('lng');

                    /* Initialize */
                    $x = [];
                    $y = [];
                    $z = [];

                    for ($i = 0; $i < count($lats); $i++) {
                        /* Convert to radians */
                        $lats[$i] = $lats[$i] * pi() / 180;
                        $lngs[$i] = $lngs[$i] * pi() / 180;

                        /* Get computations for each x, y, z */
                        $x[] = cos($lats[$i]) * cos($lngs[$i]);
                        $y[] = cos($lats[$i]) * sin($lngs[$i]);
                        $z[] = sin($lats[$i]);
                    }

                    /* Get average */
                    $x = array_sum($x) / $count;
                    $y = array_sum($y) / $count;
                    $z = array_sum($z) / $count;

                    /* Get coordinate and convert to degrees */
                    $mkr_lat = atan2($z, sqrt(($x * $x) + ($y * $y))) * 180 / pi();
                    $mkr_lng = atan2($y, $x) * 180 / pi();

                    $markers[] = [ 'lat' => $mkr_lat, 'lng' => $mkr_lng, 'count' => $count, 'bootmarks' => $grid_query ];
                }
            }
       }

       return response()->json([
           'response' => 'Success',
           'markers' => $markers
       ]);
    }


    private function calc_coord($lng, $width, $idx)
    {
        /* Checks for longitude wrapping  */
        if (($lng + ($width * $idx)) > 180) {
            return $lng + ($width * $idx) - 360;
        } else {
            return $lng + ($width * $idx);
        }
    }

    /**
     * Gets the amount of dividers to create based on the zoom level.
     *
     * @param Integer $zoom
     *
     * @return 
     */
    private function getDividerAmount($zoom)
    {
        switch ($zoom) {
        case 1:  return 8;
        case 2:  return 12;
        case 3:  return 16;
        case 4:  return 20;
        case 5:  return 24;
        case 6:  return 28;
        case 7:  return 32;
        case 8:  return 36;
        case 9:  return 40;
        case 10: return 44;
        case 11: return 48;
        case 12: return 52;
        case 13: return 56;
        case 14: return 60;
        case 15: return 64;
        case 16: return 68;
        case 17: return 72;
        case 18: return 76;
        case 19: return 80;
        case 20: return 84;
        default: return 4;
        }
    }

    /**
     * Stores a new bootmark in the database.
     *
     * @param Request $request The request object with all the inputs.
     *
     * @return json Returns a success or failure message and the bootmark id if successful.
     */
    public function store(Request $request)
    {
        /* Create new Bootmark and set the user_id */
        $bootmark = new Bootmark;
        $bootmark->user_id = Auth::user()->id;


        $this->validate($request, [
            'lat'              => 'required|numeric',
            'lng'              => 'required|numeric',
            'remote'           => 'required|boolean',
            'location'         => 'required|string',
            'description'      => 'required|nullable|string',
            'type'             => ['required', Rule::in(['photo', 'link', 'text'])],
            'url'              => 'required_if:type,link'
        ]);

        /* Create a photo */
        if ($request->input('type') == 'photo') {
            $bootmark->media_id = $this->createMedia($request);
            $bootmark->type = "photo";

        /* Link is present */
        } else if ($request->input('type') == 'link') {
            /* Check if link is a video/audio media */
            if ($this->isMedia($request->input('url'))) {
                $bootmark->media_id = $this->createMedia($request);
                $bootmark->type = "media";

                /* Not a video/audio link */
            } else {
                $bootmark->link_id = $this->createLink($request);
                $bootmark->type = "link";
            }

        /* Default to a text post */
        } else {
            $bootmark->type = "text";
        }

        /* Set all other values */
        $bootmark->location = $request->input('location');
        $bootmark->karma = 0;
        $bootmark->description = $request->input('description');
        $bootmark->lat = $request->input('lat');
        $bootmark->lng = $request->input('lng');
        $bootmark->remote = $request->input('remote');
        $bootmark->discoverable = $request->input('discoverable');

        /* Save Bootmark*/
        $bootmark->save();

        /* Return success */
        return response()->json([
            'response' => 'Success',
            'message' => 'Bootmark successfully created',
            'data' => $bootmark->id
        ]);
    }

    /**
     * Updates an existing bootmark.
     *
     * @param int $bootmarks The bootmark id to be updated.
     * @param request $request The Request object with all the inputs.
     *
     * @return json Returns a success or failure message.
     */
    public function update($bootmarks)
    {

    }

    /**
     * Soft deletes a bootmark and all its votes and comments from the database.
     *
     * @param int $bootmarks The bootmark id to be deleted.
     *
     * @return json Returns a success or failure message.
     */
    public function destroy($bootmarks)
    {

    }

    /**
     * Generates a report for the specified bootmark
     *
     * @param int $bootmarkID The bootmark that is being report.
     * @param Request $request The request object containing all the inputs.
     *
     * @return Returns a success message or a failure message.
     */
    public function report($bootmark, Request $request)
    {
        $reporter_id = Auth::user()->id;

        $rules = array(
            'reason'            =>  ['required', Rule::in(['spam', 'inappropriate'])]
        );

        $messages = array(
            'reason.in'   =>  "Reason must be either 'spam' or 'inappropriate'"
        );

        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->validate();

        /* Retrieves the selected bootmark */
        $bootmark = Bootmark::where('id', $bootmark)->first();

        /* Bootmark was not found */
        if (!$bootmark) {
            return HttpResponse::notFoundResponse("Bootmark does not exist");
        }

        /* Creates a new report */
        $report = new Report;
        $report->reporter_id = $reporter_id;
        $report->bootmark_id = $bootmark->id;
        $report->message = $request->input('message');
        $report->status = "Report received";
        $report->reason = $request->input('reason');

        $report->save();

        dispatch(new MailReport($report->id));

        return HttpResponse::successResponse('Bootmark has been reported');
    }

     /**
     * Lets the user vote on a specific bootmark.
     *
     * @param int $bootmarks The bootmark that is being voted on.
     * @param Request $request The request object.
     *
     * @return Returns a success message or a failure message.
     */
    public function vote($bootmark, Request $request)
    {
        $user_id = Auth::user()->id;
        $bootmark = Bootmark::find($bootmark);
        if (!$bootmark) {
            return HttpResponse::notFoundResponse("Bootmark does not exist");
        }

        $this->validate($request, ['vote' => 'required|numeric|between:0,1']);

        /* 1 for upvote, 0 for downvote */
        $vote = $request->input('vote');
        if($this->hasVoted($user_id,$bootmark->id)) {
            /* Check if the user is reversing their vote. */
            $old_vote = Vote::where('user_id', $user_id)->where('bootmark_id', $bootmark->id)->first();
            if($old_vote->vote == $vote) {
                $old_vote->delete();
                if($vote == 0) {
                    $this->updateKarma($bootmark, 1);
                } else {
                    $this->updateKarma($bootmark, 0);
                }
                return response()->json([
                    'response' => 'success',
                    'message' => 'Bootmark was unvoted',
                ]);
            } else {
                $old_vote->vote = $vote;
                $old_vote->save();

                $this->updateKarma($bootmark, $vote);
                $this->updateKarma($bootmark, $vote);

                return response()->json([
                    'response' => 'success',
                    'message' => 'Bootmark vote was updated'
                ]);
            }
        } else {
            $new_vote = new Vote;
            $new_vote->user_id = $user_id;
            $new_vote->bootmark_id = $bootmark->id;
            $new_vote->vote = $vote;
            $new_vote->save();

            $this->updateKarma($bootmark, $vote);
            return response()->json([
                    'response' => 'success',
                    'message' => 'Bootmark was voted on'
                ]);
        }
    }

    /**
     * Search for bootmarks within a specific lat and lng.
     *
     * @param \App\Http\Requests\SearchBootmarks $request
     *
     * @return json
     */
    public function search(Search $request)
    {

    }


    /**
     * Checks to see if the user has voted on a bootmark already.
     *
     * @param int $user The id of the user voting.
     * @param int $bootmark The id of the bootmark being voted on.
     *
     * @return Returns The id of the vote if it exists, null otherwise.
     */
    private function hasVoted($user, $bootmark)
    {
        $vote_relation = Vote::where('user_id', $user)->where('bootmark_id', $bootmark)->get();
        if($vote_relation->isEmpty()) {
            return false;
        } else {
            return true;
        }

    }

    /**
     * Updates the bootmarks karma based on the vote given.
     *
     * @param object $bootmark The bootmark to be updated.
     * @param int $vote A '1' for an upvote and a '0' for a downvote.
     *
     * @return void
     */
    private function updateKarma($bootmark, $vote)
    {
        if($vote == 0) {
            $bootmark->karma--;
        } else {
            $bootmark->karma++;
        }
        $bootmark->save();
    }

    /**
     * Finds all the friends that a user has.
     *
     * @param int $id The users id to search.
     *
     * @return array An array of all the friends ids.
     */
    private function findFriends($id)
    {
        $user = User::find($id);
        if (!$user) {
            return array();
        }
        $friends = array();
        foreach($user->followers as $follower) {
            /* Check to see if you are following the user that has followed you */
            $friend = Follower::where('user_id', $follower->follower_id)->where('follower_id',$user->id)->first();
            if($friend != null) {
                array_push($friends,$friend->user_id);
            }
        }

        return $friends;
    }

    /**
     * Creates a Link object to store into the database.
     *
     * Uses a SimpleScraper in an attempt to extract data for the link object. Will call setDefaultLink to set
     * any data that was not obtained.
     *
     * @param Request $request The Request object with all the inputs.
     *
     * @return string The ID of the Link object that was created in the database
     */
    private function createLink(Request $request)
    {
        $link = new Link;
        $link->url = $request->input('url');

        $scraper = new SimpleScraper($link->url);

        /* Use the scraper to search/extract data */
        $this->checkMetaData($scraper, $link);

        /* Set the default data if something is missing */
        $this->setDefaultLink($link);
        $link->save();

        return $link->id;
    }

    /**
     * Creates a Media object to store into the database.
     *
     * A media object can either be a photo or other media stream (such as a video/audio file). It will set the
     * appropriate elements of the object based on the type of media object being created.
     *
     * @param Request $request The Request object with all the inputs.
     *
     * @return string The ID of the Media object that was created in the database
     */
    private function createMedia(Request $request)
    {
        $media = new Media;

        /* Set Media object as a photo */
        if ($request->input('type') == 'photo') {

            $file = $request->file('photo');

            if ($file == null) {
                return response()->json([
                    'response' => 'Failure',
                    'message' => "missing or invalid 'photo' parameter with attached file"
                ], 422);
            }

            $media->path = Photo::storePhoto('bootmark_uploads', $file);
            $media->mime_type = $file->getClientMimeType();
            $media->media_type = "photo";

        /* Set Media object as a media */
        } else {

            $link = $request->input('url');

            if ($this->isMedia($link)) {
                $media->path = $this->getMediaPath($link, $media);
            } else {
                $media->media_type = "unknown";
            }
        }

        $media->save();

        return $media->id;
    }

    /**
     * Takes a link from the http request as given as a parameter and will convert it to an embedded url path for
     * a video or null if one could not be generated.
     *
     * @param string $link The path/url from the Request itself.
     * @param Media $media The media object that is being generated
     *
     * @return null|string Returns the url path for a video or null on failure.
     */
    private function getMediaPath($link, $media)
    {
        /* Remove the apostrophes */
        $url = ltrim(rtrim($link, "'"), "'");

        /* YouTube Path */
        if ($this->isYouTube($url)) {
            $media->media_type = "youtube";
            return $this->createYouTubePath($url);
        }

        /* Insert more generated paths here */

        return null;
    }

    /**
     * Generates an embedded youtube path based on the format of the url.
     *
     * @param string $url A url/path/website
     *
     * @return string An embedded youtube link
     */
    private function createYouTubePath($url)
    {
        $video_id = '';

        if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $url, $id)) {
            $video_id = $id[1];
        } else if (preg_match('/youtube\.com\/embed\/([^\&\?\/]+)/', $url, $id)) {
            $video_id = $id[1];
        } else if (preg_match('/youtube\.com\/v\/([^\&\?\/]+)/', $url, $id)) {
            $video_id = $id[1];
        } else if (preg_match('/youtu\.be\/([^\&\?\/]+)/', $url, $id)) {
            $video_id = $id[1];
        } else if (preg_match('/youtube\.com\/verify_age\?next_url=\/watch%3Fv%3D([^\&\?\/]+)/', $url, $id)) {
            $video_id = $id[1];
        }

        return 'https://youtube.com/embed/' . $video_id;
    }

    /**
     * Takes a link as given in the HTTP Request and removes the apostrophes from either side.
     *
     * @param string $link A link as given via the HTTP request.
     *
     * @return bool Returns true if the link is some sort of accepted media file.
     */
    private function isMedia($link)
    {
        /* Remove the apostrophes */
        $url = ltrim(rtrim($link, "'"), "'");

        /* Check if url is YouTube */
        if ($this->isYouTube($url)) {
            return true;
        }

        /* Insert more media checks here */

        return false;
    }

    /**
     * A simple check to see if a url contains the youtube string.
     *
     * @param $url A url/path/website
     *
     * @return bool Returns true if the path contains the youtube string.
     */
    private function isYouTube($url)
    {

        return strpos($url, 'youtube.com') || strpos($url, 'youtu.be') || strpos($url, 'y2u.be');

    }

    /**
     * Checks for any null or blank fields within a Link Object and will set the default values.
     *
     * @param Link $link A link object that will have its contents set to default values.
     *
     * @return void
     */
    private function setDefaultLink($link)
    {
        if ($link->meta_description == null || $link->meta_description == '') {
            $link->meta_description = 'No description found';
        }

        if ($link->title == null || $link->title == '') {
            $link->title = 'No title found';
        }

        if ($link->image_path == null || $link->image_path == '') {
            $link->image_path = 'http://support.yumpu.com/en/wp-content/themes/qaengine/img/default-thumbnail.jpg';
        }
    }

    /**
     * Generic function for setting the contents of a Link object.
     *
     * It uses the SimpleScraper class and will search the data for a specified tag and set the contents of the Link
     * object if particular tags exist. More tags can be added into the $tags variable.
     *
     * @param SimpleScraper $scraper Scraper for holding and extracting data, written by Ramon Kayo.
     * @param Link $link A link object that will have its contents set
     *
     * @return void
     */
    private function checkMetaData(SimpleScraper $scraper, Link $link)
    {

        /* Tags to check for */
        $tags = ['ogp', 'twitter', 'meta'];

        foreach ($tags as $tag) {
            $meta = $scraper->getAllData()[$tag];

            /* Checks for relevant meta data from the tag */
            if (!empty($meta)) {
                if (!empty($meta['description']))
                    $link->meta_description = $meta['description'];

                if (!empty($meta['image']))
                    $link->image_path = $meta['image'];

                if (!empty($meta['title']))
                    $link->title = $meta['title'];
            }
        }
    }

    /**
     * Returns the photo in a response associated with a given bootmarkID.
     *
     * @param integer $bootmark The ID of the bootmark
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPhoto($bootmark)
    {
        /* Retrieves the selected bootmark */
        $bootmark = Bootmark::where('id', $bootmark)->first();

        /* Checks if the bootmark exists or has a media foreign key */
        if ($bootmark == null || $bootmark->media_id == null || $bootmark->media_id == '') {
            return response()->json([
                'response' => 'Failure',
                'message' => 'Bootmark has no photo'
            ] , 404);
        }

        else {
            /* Gets the media object */
            $media = Media::where('id', $bootmark->media_id)->first();

            /* Checks if the media is a photo */
            if ($media->media_type != 'photo') {
                return response()->json([
                    'response' => 'Failure',
                    'message' => 'Bootmark has no photo'
                ], 404);

            /* Returns the photo in a response */
            } else {

                /* If the photo exists */
                if (Photo::photoExists('bootmark_uploads', $media->path)) {
                    $file = Photo::getPhoto('bootmark_uploads', $media->path);
                    return response($file)->header('Content-Type', $media->mime_type);

                /* If the photo does not exist */
                } else {
                    return response()->json([
                        'response' => 'Failure',
                        'message' => 'Bootmark photo not found'
                    ], 404);
                }
            }
        }
    }

    /**
     * Extracts all the filters from the request.
     *
     * @param request $request The request object.
     *
     * @return array An array containing all filters to be applied.
     */
    private function getFilters($request)
    {
        $filters = array();

        /* Determines if the photo filter is applied */
        if($request->has('photos') && $request->input('photos') == 1) {
            array_push($filters,'photo');
        }

        /* Determines if the link filter is applied */
        if($request->has('links') && $request->input('links') == 1) {
            array_push($filters,'link');
        }

        /* Determines if the comment filter is applied */
        if($request->has('media') && $request->input('media') == 1) {
            array_push($filters,'media');
        }

        /* Determines if the comment filter is applied */
        if($request->has('texts') && $request->input('texts') == 1) {
            array_push($filters,'text');
        }

        return $filters;
    }
}
