<aside class="main-sidebar sidebar-light-primary elevation-1"> 
    <a href="{{ url('/') }}" class="brand-link">
        <img src="/dist/img/logo.png" alt="{{ config('app.name', 'Laravel') }}" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">{{ config('app.name', 'Laravel') }}</span>
    </a> 
    <div class="sidebar"> 
        <div class="user-panel mt-2 pb-2 mb-3 d-flex align-items-center">
            <div class="pull-left image">
                <img src="/dist/img/avatar.png" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="pull-left info">
                <a href="#" class="d-block"> {{Auth()->user()->name!=null ? Auth()->user()->name : "Administrator"}} </a>
				<a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div> 

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{!! url('/home') !!}" class="nav-link "><i class="nav-icon fa fa-bars"></i> DASHBOARD</a>
                </li> 
                <li class="nav-header">ADMIN NAVIGATION</li>
				<li class="nav-item">
					<a href="{{ url('/') }}" class="nav-link "><i class="nav-icon fa fa-cogs"></i> Settings</a>
				</li>
            </ul>
        </nav> 
    </div> 
</aside>
