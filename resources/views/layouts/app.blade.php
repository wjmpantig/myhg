<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>myhg</title>


        <link rel="stylesheet" type="text/css" href="css/app.css">
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
    <body >
       <div id="app" class="grid-container">
        @yield('content')    
       </div>
       
       
       @yield('scripts')
     
    </body>
</html>
