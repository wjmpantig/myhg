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
                <li><router-link to="/">Dashboard</router-link></li>
                <li><router-link to="/sections">Sections</router-link></li>
                <li><router-link to="/teachers">Teachers</router-link></li>
                <li><router-link to="/students">Students</router-link></li>
                <li><a href="javascript:$('#logout-form').submit()">Logout</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
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