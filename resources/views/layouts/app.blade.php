<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Bootmark') }}</title>

    <!-- Scripts -->
    <script>
        window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>
    </script>

    <style>
        html {
            margin: 0;
            padding: 0;
        }

        #app {
            margin-left: auto;
            margin-right: auto;
            width: 250px;
            align-content: center;
            border: medium solid rebeccapurple;
            border-radius: 5%;
        }

        .navbar {
            text-align: center;
            border-bottom: medium dashed rebeccapurple;
            height: 200px;
        }

        .container {
            margin-top: 10px;
            text-align: center;
            font-size: larger;
        }

        .btn {
            margin: 10px;
            height: 40px;
            background-color: rebeccapurple;
            color: white;
            font-size: 16px;
        }

        .panel-heading {
            font-size: larger;
        }
    </style>

</head>
<body>
    <div id="app">
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">

                    <!-- Branding Image -->
                    <a class="top-bar" href="http://www.bootmark.ca">
                        <img src="/img/bootmark_logo.png" alt="Bootmark Logo Icon" style="width:200px;height:200px;">
                    </a>
                </div>
            </div>
            <br>
        </nav>

        @yield('content')
    </div>
</body>
</html>
