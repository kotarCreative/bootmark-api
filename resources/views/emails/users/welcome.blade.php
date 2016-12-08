<head>
    <link href='http://fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
    <style>
        h1 {
            font-family:        Lato, Open, Sans, serif;
            font-weight:        800
        }

        h3 {
            font-family:        Lato, Open, Sans, serif;
            font-weight:        600;
        }

        .sig-div {
            display:            inline;
        }

        #sig-list {
            display:            inline-block;
            margin:             0;
            padding:            0;
            list-style-type:    none;
        }

        .sig-list-item {
            margin:             0;
        }

        #wordmark-image {
            width:              120px !important;
            height:             30px !important;
        }

        #logo-image {
            width:              60px !important;
            height:             60px !important;
            border-right:       thin solid #333;
        }

        #url-link {
            text-decoration:    none;
            color:              #333;
            margin-left:        4px;
        }

        #url-link:hover {
            cursor:             pointer;
            color:              #00b3c6;
        }
    </style>
</head>

<body>
    <h1>Hello {{ $username }},</h1>
    <h1>Welcome to Bootmark!</h1>

    <p>
        We are so excited to have you be a part of this community. bootmark is all about sharing interesting content and
        cool new places... we think you're going to love it!
    </p>

    <h3>Getting Started</h3>
    <p>
        Whenever you open the app feel free to post something about the location you are currently in. Then you'll be
        able to see it on the map in the search tab along with everyone else's posts. After that cruise on over to the
        newsfeed tab and see all the cool content that people have been posting. Make sure to upvote the things you like
        so that others can see the coolest content out there.
    </p>

    <h3>This is a beta version right?</h3>
    <p>
        Yep! We will be updating things regularly to give you new features and fix and bugs that you find. You'll get
        notifications on Testflight whenever a new version of the beta is available.
    </p>

    <h3>I want a new feature. How do I tell you guys?</h3>
    <p>
        You can tell us about any new features you'd like to see along with what you like, what you don't like and any
        bugs you might find by sending us feedback through testflight or emailing us at info@bootmark.ca.
    </p>
    <p>
        Happy posting...let's work together to make this thing awesome!
    </p>

    <p>
        Mike | Scott | Dave | Isaac | Cody
    </p>

    <div class="sig-div">
        <img id="logo-image" src='{{ $message->embed(public_path() . '/img/tree-logo.png') }}'/>
    </div>
    <div class="sig-div">
        <ul id="sig-list">
            <li class="sig-list-item">
                <img id="wordmark-image" src='{{ $message->embed(public_path() . '/img/wordmark.png') }}'/>
            </li>
            <li class="sig-list-item">
                <a href="www.bootmark.ca" id="url-link">www.bootmark.ca</a>
            </li>
        </ul>
    </div><br/>
</body>
