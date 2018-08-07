 <ul class="menu vertical">
    <router-link tag="li" to="/" exact><a>Dashboard</a></router-link>
    <router-link tag="li" to="/sections"><a>Sections</a></router-link>
    <!-- <router-link tag="li" to="/teachers"><a>Teachers</a></router-link> -->
    <router-link tag="li" to="/students"><a>Students</a></router-link>
    <router-link tag="li" to="/settings"><a>Settings</a></router-link>      
    <li><a href="#" @click="logout()">Logout</a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hide">
        @csrf
    </form>
    </li>
</ul>