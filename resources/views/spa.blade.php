<!DOCTYPE html>
<html lang="en">
<head>
    <title>Image Gallery</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" type="text/css" href="{{ mix('assets/css/app.css') }}" crossorigin="anonymous">
</head>
<body>
<div id="pageBackground">
    <img data-src="{{ asset('/assets/img/background.jpg') }}" alt="Background image of website" uk-img>
</div>
<div id="app">
    <app></app>
</div>
<script src="{{ mix('assets/js/app.js') }}" crossorigin="anonymous"></script>
</body>
</html>
