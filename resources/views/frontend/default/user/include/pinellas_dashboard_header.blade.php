<header class="header-pinellas">
    <div class="d-flex align-items-center">
        <button class="btn btn-link text-white d-lg-none me-3 p-0" id="sidebarToggle" type="button">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        <div class="logo">
            <a href="{{ route('home') }}">
                <img src="https://www.pinellasfcu.org/templates/pinellas/images/logo.png" alt="Pinellas FCU" style="height: 36px;">
            </a>
        </div>
    </div>

    <div class="user-info d-flex align-items-center gap-3">
        <span class="user-name d-none d-md-inline" style="font-weight: 500;">
            {{ auth()->user()->full_name }}
        </span>
        <div class="dropdown">
            <button class="btn btn-link text-white text-decoration-none dropdown-toggle p-0" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-user-circle fa-lg"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-3" style="border-radius: 12px;">
                <li><a class="dropdown-item py-2" href="{{ route('user.setting.show') }}"><i class="fas fa-id-card me-2"></i> Profile</a></li>
                <li><a class="dropdown-item py-2 text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</header>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>
