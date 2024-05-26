<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ucfirst(config('app.env')) }} Regsuite Backend</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;600&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 900;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 90vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }
    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    <div class="content">
        <h2 style="text-align: center;"> Welcome To </h2>
        <h1 style="text-align: center;"> {{ ucfirst(config('app.env')) }} Digital Wallet API</h1>
    </div>
</div>
</body>
</html>
