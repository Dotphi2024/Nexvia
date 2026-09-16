@include('adminlayouts.partials/head-css')

<div class="app-sidebar">
     <!-- Sidebar Logo -->
     <div class="logo-box">
          <a href="{{ route('admin.dashboard') }}" class="logo-dark text-decoration-none">
               @if(isset($settings->logo) && !empty($settings->logo))
                    <img src="{{ asset($settings->logo) }}" alt="logo" height="24">
               @else
                    <span class="fw-bold fs-22 text-uppercase" style="letter-spacing: 1.5px; font-weight: 800; color: #7c3aed;">NEXVIA</span>
               @endif
          </a>
          <a href="{{ route('admin.dashboard') }}" class="logo-light text-decoration-none">
               @if(isset($settings->logo) && !empty($settings->logo))
                    <img src="{{ asset($settings->logo) }}" alt="logo" height="24">
               @else
                    <span class="fw-bold fs-22 text-uppercase" style="letter-spacing: 1.5px; font-weight: 800; color: #a78bfa;">NEXVIA</span>
               @endif
          </a>
     </div>

     <div class="scrollbar" data-simplebar>
          <ul class="navbar-nav" id="navbar-nav">
               <li class="menu-title">Overview</li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:home-smile-outline"></iconify-icon>
                         </span>
                         <span class="nav-text">Dashboard</span>
                    </a>
               </li>

               <li class="menu-title">Users</li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:users-group-two-rounded-outline"></iconify-icon>
                         </span>
                         <span class="nav-text">Customers</span>
                    </a>
               </li>

               <li class="menu-title">Catalog</li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:widget-3-outline"></iconify-icon>
                         </span>
                         <span class="nav-text">Categories</span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:box-minimalistic-outline"></iconify-icon>
                         </span>
                         <span class="nav-text">Products</span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}" href="{{ route('admin.banners.index') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:gallery-wide-outline"></iconify-icon>
                         </span>
                         <span class="nav-text">Banners</span>
                    </a>
               </li>

               <li class="menu-title">Bookings</li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}" href="{{ route('admin.bookings.index') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:ticket-outline"></iconify-icon>
                         </span>
                         <span class="nav-text">Bookings</span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.transfers.*') ? 'active' : '' }}" href="{{ route('admin.transfers.audit') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:transfer-horizontal-outline"></iconify-icon>
                         </span>
                         <span class="nav-text">Transfers</span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.booking.engine.*') ? 'active' : '' }}" href="{{ route('admin.booking.engine.settings') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:settings-outline"></iconify-icon>
                         </span>
                         <span class="nav-text">Booking Settings</span>
                    </a>
               </li>

               <li class="menu-title">Referrals & Dealers</li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.self_dealers.index') || request()->routeIs('admin.self_dealers.show') ? 'active' : '' }}" href="{{ route('admin.self_dealers.index') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:user-id-outline"></iconify-icon>
                         </span>
                         <span class="nav-text">Self Dealers</span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.self_dealers.transactions') ? 'active' : '' }}" href="{{ route('admin.self_dealers.transactions') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:wallet-money-outline"></iconify-icon>
                         </span>
                         <span class="nav-text">Transactions</span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.self_dealers.fraud_flags') ? 'active' : '' }}" href="{{ route('admin.self_dealers.fraud_flags') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:danger-triangle-outline"></iconify-icon>
                         </span>
                         <span class="nav-text">Fraud Flags</span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.referral.config.*') ? 'active' : '' }}" href="{{ route('admin.referral.config.settings') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:tuning-2-outline"></iconify-icon>
                         </span>
                         <span class="nav-text">Referral Settings</span>
                    </a>
               </li>

               <li class="menu-title">Support</li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.service.requests.*') ? 'active' : '' }}" href="{{ route('admin.service.requests.index') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:shield-check-outline"></iconify-icon>
                         </span>
                         <span class="nav-text">Service Requests</span>
                    </a>
               </li>
          </ul>
     </div>

     <!-- Bottom Profile Card -->
     @if(Auth::guard('admin')->check())
     <div class="sidebar-user-card mx-3 mb-3 p-2 rounded border border-light-subtle d-flex align-items-center gap-2 bg-light">
          <div class="avatar-sm text-white rounded d-flex align-items-center justify-content-center fw-bold fs-14" style="width: 34px; height: 34px; min-width: 34px; background: #7c3aed;">
               {{ strtoupper(substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1)) }}
          </div>
          <div class="overflow-hidden">
               <h6 class="mb-0 fw-bold fs-13 text-dark text-truncate">{{ Auth::guard('admin')->user()->name ?? 'Admin User' }}</h6>
               <span class="text-muted fs-11">Administrator</span>
          </div>
     </div>
     @endif
</div>