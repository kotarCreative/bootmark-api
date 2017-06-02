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
            margin-top:auto;
            margin-bottom:auto;
            margin-left: auto;
            margin-right: auto;
            width: 250px;
        }


        .form-control {
            box-sizing:border-box;
            margin-top: 10px;
            border-radius:5px;
            border:solid 1px grey;
            font-size: 12px;
            font-family:Arial;
            padding: 5px;
            width: 200px;
        }


        .container {
            text-align: left;
            font-size: 12px;
            width:200px;
        }

        .btn {
            margin-top: 10px;
            height: 30px;
            background-color: dimgrey;
            color: white;
            font-size: 12px;
            font-family: Arial;
            border-radius: 5px;
            width: 200px;
        }

        .navbar-header{
            margin-top:50%;
            height:40px;
        }

        #bootmark-wordmark {
            max-width: 100%;
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
                        <img src="/img/wordmark.png" alt="Bootmark Wordmark Icon" id="bootmark-wordmark">
                    </a>
                </div>
            </div>
            <br>
        </nav>

        @yield('content')
    </div>
</body>
</html>
