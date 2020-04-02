<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Broadcast Redis Socket io Tutorial - ItSolutionStuff.com</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>Broadcast Redis Socket IO - Sabungdai.com</h1>

    <div id="notification"></div>
</div>
</body>

<script>
    window.laravel_echo_port = '{{config('services.socketio.echo.port')}}';
</script>
<script src="//{{ Request::getHost() }}:{{config('services.socketio.echo.port')}}/socket.io/socket.io.js"></script>
<script src="{{ url('/js/laravel-echo-setup.js') }}" type="text/javascript"></script>

<script type="text/javascript">
    var i = 0;
    var Echo = window.Echo;
    function showMessage(data) {
        i++;
        $("#notification").append('<div class="alert alert-success">' + i + '.' + data.title + '</div>');
    }
    Echo.channel('user-print-channel')
        .listen('.UserPrintEvent', (data) => {
            showMessage(data);
        });

    Echo.private('user-print-channel')
        .listen('.UserPrintEvent', (data) => {
            showMessage(data);
        });
</script>
</html>