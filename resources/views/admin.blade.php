@extends('layouts.app')
@section('content')

<div id="app">
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="/" class="navbar-item">
                <h1 class="title">{{ config('app.name', 'MyHG') }}</h1>                
            </a>
            <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false">
              <span aria-hidden="true"></span>
              <span aria-hidden="true"></span>
              <span aria-hidden="true"></span>
            </a>
        </div>
    </nav>
    <section class="section">
        
        
            <div class="columns" id="sidebar">
                <div class="column is-one-fifth">
                    @include('admin.menu')
                </div>
                 <div class="column">
                    <router-view></router-view>
                </div>
            </div>
           
        
        
    </section>

    
</div>
@endsection
@section('scripts')            
        <script src="js/admin_app.js"></script>

@endsection