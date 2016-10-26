<head>
    <link href='http://fonts.googleapis.com/css?family=Lato:600,800' rel='stylesheet' type='text/css'>
</head>
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
            width:              120px;
            height:             30px;
        }

        #logo-image {
            width:              40px;
            height:             40px;
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
<body>
    <h1>Your report has been delivered</h1>

    <p>We appreciate your contribution to making Bootmark the very best it can be.</p>

    <h3>Our commitment to quality content</h3>
    <p>We believe in creating a community that consistently posts high quality, interesting content. There is no room for offensive or malicious content, and we work hard to ensure that this content is taken down as soon as it is reported.</p>

    <h3>We aim for quality experiences</h3>
    <p>The higher quality the content, the better and more uplifting the experience for you as the user. We love people that make an earnest effort to maintain a positive experience for other users. Users that have multiple posts reported as being offensive or malicious will be reviewed and may be banned from the app if necessary.</p>

    <h3>Thanks again</h3>
    <p>By reporting offensive and malicious content you are playing an important role in keeping this app awesome. You rock.</p>

    <p>Just as a reminder here is what you reported to us.</p>
    <p>"{{ $bodyMessage }}"</p>
    <p>Mike | Scott | Dave | Isaac | Cody</p>

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
