@extends('layouts.app')
@section('content')
<div id="app">
<div class="top-bar">
    <div class="top-bar-left">
        <ul class="menu">
            <li class="menu-text">{{ config('app.name', 'MyHG') }}</li>
            
        </ul>

    </div>
    <div class="top-bar-right hide-for-large">
        <ul class="menu dropdown" data-dropdown-menu>
            <li>
                <a href="#"><font-awesome-icon :icon="['fas','bars']"></font-awesome-icon></a>
                @include('admin.menu')   
            </li>
           
        </ul>
        

    </div>
</div>

<div class="grid-container fluid">
    <div class="grid-x grid-padding-x grid-padding-y">
        <div class="cell show-for-large large-3" id="sidebar">
           @include('admin.menu');
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