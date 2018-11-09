<aside class="menu">
     <ul class="menu-list">
        <li><router-link to="/" exact>Dashboard</router-link></li>
        <li><router-link to="/sections">Sections</router-link></li>
        <!-- <li><router-link to="/teachers"><a>Teachers</a></router-link></li> -->
        <li><router-link to="/students">Students</router-link></li>
        <li><router-link to="/export">Export</router-link></li>
        <li><router-link to="/settings">Settings</router-link></li>      
        <li><a href="#" @click="logout()">Logout</a>
        <form id="logout-form" ref="logout_form" action="{{ route('logout') }}" method="POST" class="hide">
            @csrf
        </form>
        </li>
    </ul>
</aside>