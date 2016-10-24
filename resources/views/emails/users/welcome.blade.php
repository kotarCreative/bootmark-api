<head>
    <link href='http://fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
</head>

<body>
    <h1 style='font-family: "Lato" Open sans;font-weight: 800'>Hello {{ $username }},</h1>
    <h1 style='font-family: "Lato" Open sans;font-weight: 800'>Welcome to Bootmark!</h1>

    <p>We are so excited to have you be a part of this community. bootmark is all about sharing interesting content and cool new places... we think you're going to love it!</p>

    <h3 style='font-family: "Lato" Open sans;font-weight: 600'>Getting Started</h3>
    <p>Whenever you open the app feel free to post something about the location you are currently in. Then you'll be able to see it on the map in the search tab along with everyone else's posts. After that cruise on over to the newsfeed tab and see all the cool content that people have been posting. Make sure to upvote the things you like so that others can see the coolest content out there.</p>

    <h3 style='font-family: "Lato" Open sans;font-weight: 600'>This is a beta version right?</h3>
    <p>Yep! We will be updating things regularly to give you new features and fix and bugs that you find. You'll get notifications on Testflight whenever a new version of the beta is available.</p>

    <h3 style='font-family: "Lato" Open sans;font-weight: 600'>I want a new feature. How do I tell you guys?</h3>
    <p>You can tell us about any new features you'd like to see along with what you like, what you don't like and any bugs you might find by sending us feedback through testflight or emailing us at info@bootmark.ca.</p>

    <p>Happy posting...let's work together to make this thing awesome!</p>

    <p>Mike | Scott | Dave | Isaac | Cody</p>

    <div style='display: inline;'>
        <img style='width: 40px; height: 40px; border-right: thin solid #333' src='{{ $message->embed(public_path() . '/img/tree-logo.png') }}'/>
    </div>
    <div style='display: inline;'>
        <img style='width: 120px; height: 30px;' src='{{ $message->embed(public_path() . '/img/wordmark.png') }}'/>
    </div><br/>
    <p>www.bootmark.ca</p>
</body>
