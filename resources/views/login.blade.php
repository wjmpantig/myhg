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
            <div class="grid-x">
                <div class="cell small-12">
                    <form v-on:submit.prevent="onSubmit()">
                        <h2>Login</h2>
                        <label>User Name
                            <input type="text" placeholder="User name">
                        </label>
                        <label>Password
                            <input type="password" placeholder="User name">
                        </label>
                        <button type="submit" class="button large">Log in</button>
                    </form>
                </div>
            </div>

        </div>
      
        <script src="js/login_app.js"></script>
     
    </body>
</html>
