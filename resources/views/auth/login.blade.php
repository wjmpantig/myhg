@extends('layouts.app')

@section('content')
<div class="grid-container" id="app">
        <div class="grid-x">
            <div class="cell small-12">
                <form action="login" method="post" v-on:submit="onSubmit()">
                    @csrf

                    <h2>Login</h2>
                    
                    @if ($errors->has('email'))
                        <span class="label alert" role="alert">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif    
                    
                    <input type="text" name="email" id="email" placeholder="Email">
                     
                    
                    @if ($errors->has('password'))
                        <span class="label alert" role="alert">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                    

                    <input type="password" id="password" name="password" placeholder="Password">
                   
                    
                    <button type="submit" class="button large">Log in</button>
                </form>
            </div>
        </div>
</div>
@endsection
@section('scripts')            
<!--         <script src="js/login_app.js"></script>
 -->
@endsection