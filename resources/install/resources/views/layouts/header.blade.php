  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-light navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{ url('/flights') }}" class="nav-link">Flight</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{ url('/home') }}" class="nav-link">Home</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Fulscreen Toggle Menu -->
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link" data-toggle="dropdown">
          <img src="/dist/img/avatar.png" class="user-image" alt="User Image">
          <span class="hidden-xs">{{Auth()->user()->name}}</span>
        </a>
          <ul class="dropdown-menu">
            <!-- User image -->
            <li class="user-header">
              <img src="/dist/img/avatar.png" class="img-circle" alt="User Image">

              <p>
                {{Auth()->user()->name}} - Web Developer
                <small class="text-info">{{Auth()->user()->email}}</small>
                <small>Member since {{Auth()->user()->created_at->format('M, Y')}}</small>
              </p>
            </li>
            <!-- Menu Body -->
            <li class="user-body">
              <div class="row px-2 d-flex justify-content-between align-items-center">
                  <div>
                    <button href="#" class="btn btn-info"><i class="fa fa-user"></i> Profile</button>
                  </div>
                  <div class="align-items-center">
                   <button class="btn btn-success" href="{{ route('login') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"> <i class="fa fa-lock"></i>
                   {{ __('Logout') }} </button>
                  <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none"> @csrf </form>
                  </div>
              </div>
              <!-- /.row -->
            </li>
          </ul>
      </li>
    </ul>
  </nav>