@extends('layouts.app')

@section('content')
<div class="hero is-fullheight">
    <div class="hero-body">
        <div class="container" id="app">
            <div class="column">
                  <form action="login" method="post" v-on:submit="onSubmit()">
                    @csrf
                    <h1 class="title">{{ config('app.name', 'MyHG') }}</h1>
                    <h2 class="subtitle">Login</h2>
                    
                   
                    <div class="field">
                        <input type="text" name="email" id="email" class="input" placeholder="Email">
                     @if ($errors->has('email'))
                        <p class="help is-danger">
                            
                                {{ $errors->first('email') }}
                        </p>
                    @endif      
                    </div>
                      
                    <div class="field">
                        <input type="password" id="password" name="password"  class="input" placeholder="Password">     
                         @if ($errors->has('password'))
                        <p class="help is-danger">
                                {{ $errors->first('password') }}
                        </p>
                        @endif
                    </div>
                    
                   
                    
                    <div class="field">
                        <div class="control">
                            <button type="submit" class="button is-large is-primary">Log in</button>   
                        </div>
                    </div>
                  
                    
                </form>
            
               
            </div>
        </div>
    </div>
</div>

</div>
@endsection
@section('scripts')            
<!--         <script src="js/login_app.js"></script>
 -->
@endsection