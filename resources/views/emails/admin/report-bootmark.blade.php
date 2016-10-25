<link href='http://fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>

<h1 style='font-family: "Lato" Open sans;font-weight: 800'>A bootmark was reported</h1>

<p>The bootmark was reported by the user with id: {{ $reporter_id }}</p>

<h3 style='font-family: "Lato" Open sans;font-weight: 600'>Report Information</h3>
<ul style="list-style-type: none">
    <li>Report ID: {{ $id }}</li>
    <li>Bootmark ID: {{ $bootmark_id }}</li>
    <li>Message: {{ $bodyMessage }}</li>
    <li>Current Status: {{ $status }}</li>
</ul>
