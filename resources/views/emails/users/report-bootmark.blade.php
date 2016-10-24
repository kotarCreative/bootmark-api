<head>
    <link href='http://fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
</head>
<body>
    <h1 style='font-family: "Lato" Open sans;font-weight: 800' >Your report has been delivered</h1>

    <p>We appreciate your contribution to making Bootmark the very best it can be.</p>

    <h3 style='font-family: "Lato" Open sans;font-weight: 600'>Our commitment to quality content</h3>
    <p>We believe in creating a community that consistently posts high quality, interesting content. There is no room for offensive or malicious content, and we work hard to ensure that this content is taken down as soon as it is reported.</p>

    <h3 style='font-family: "Lato" Open sans;font-weight: 600'>We aim for quality experiences</h3>
    <p>The higher quality the content, the better and more uplifting the experience for you as the user. We love people that make an earnest effort to maintain a positive experience for other users. Users that have multiple posts reported as being offensive or malicious will be reviewed and may be banned from the app if necessary.</p>

    <h3 style='font-family: "Lato" Open sans;font-weight: 600'>Thanks again</h3>
    <p>By reporting offensive and malicious content you are playing an important role in keeping this app awesome. You rock.</p>

    <span>Mike | Scott | Dave | Isaac | Cody</span>

    <div style='display: inline-block;'>
        <img style='width: 50px; height: 50px;' src='{{ $message->embed(public_path() . '/img/tree-logo.png') }} ' width='50' height='50' style='border-right: 1px solid #333'/>
    </div>
    <div style='display: inline-block;'>
        <img style='width: 200px; height: 50px;' src='{{ $message->embed(public_path() . '/img/wordmark.png') }}' width='200' height='50' /><br />
        <span>www.bootmark.ca</span>
    </div>
</body>
