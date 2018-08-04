@extends('layouts.app')
@section('content')
<div id="app">
<div class="top-bar">
    <div class="top-bar-left">
        <ul class="menu">
            <li class="menu-text">{{ config('app.name', 'MyHG') }}</li>
            
        </ul>

    </div>
</div>

<div class="grid-container">
    <div class="grid-x grid-padding-x grid-padding-y">
        <div class="cell show-for-large large-3" id="sidebar">
            <ul class="menu vertical">
                <router-link tag="li" to="/" exact><a>Dashboard</a></router-link>
                <router-link tag="li" to="/sections"><a>Sections</a></router-link>
                <router-link tag="li" to="/teachers"><a>Teachers</a></router-link>
                <router-link tag="li" to="/students"><a>Students</a></router-link>
                <li><a href="#" @click="logout()">Logout</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hide">
                    @csrf
                </form>
                </li>
            </ul>
        </div>
        <div class="cell large-9">
            <router-view></router-view>
        </div>
    </div>
</div>
</div>
@endsection
@section('scripts')            
        <script src="js/admin_app.js"></script>

@endsection