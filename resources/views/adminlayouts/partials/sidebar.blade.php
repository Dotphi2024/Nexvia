@include('adminlayouts.partials/head-css')

<div class="app-sidebar">
     <!-- Sidebar Logo -->

     @if(isset($settings->logo) && !empty($settings->logo))
     <div class="logo-box">
          <a href="{{ route('admin.dashboard') }}" class="logo-dark text-decoration-none">
               <span class="fw-bold fs-22 text-primary text-uppercase" style="letter-spacing: 1.5px; font-weight: 800;">NEXVIA</span>
          </a>
          <a href="{{ route('admin.dashboard') }}" class="logo-light text-decoration-none">
               <span class="fw-bold fs-22 text-white text-uppercase" style="letter-spacing: 1.5px; font-weight: 800;">NEXVIA</span>
          </a>
     </div>

     <div class="scrollbar" data-simplebar>

          <ul class="navbar-nav" id="navbar-nav">

               <li class="menu-title">Menu</li>

               <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.dashboard') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:home-smile-outline"></iconify-icon>
                         </span>
                         <span class="nav-text">Dashboard</span>
                    </a>
               </li>


           </ul>
     </div>

     <!-- Bottom Profile Card (Matching Reference UI) -->
     @if(Auth::guard('admin')->check())
     <div class="sidebar-user-card mx-3 mb-3 p-2.5 rounded-3 d-flex align-items-center gap-2.5" style="background: #f8fafc; border: 1px solid #f1f5f9; margin-top: auto;">
          <div class="avatar-sm bg-purple-soft text-purple rounded-3 d-flex align-items-center justify-content-center fw-bold fs-14" style="width: 38px; height: 38px; min-width: 38px;">
               {{ strtoupper(substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1)) }}
          </div>
          <div class="overflow-hidden">
               <h6 class="mb-0 fw-bold fs-13 text-dark text-truncate">{{ Auth::guard('admin')->user()->name ?? 'Admin User' }}</h6>
               <span class="text-uppercase text-muted fs-10 fw-semibold tracking-wider" style="letter-spacing: 0.05em;">
                    {{ Auth::guard('admin')->user()->roles->pluck('name')->first() ?? 'Administrator' }}
               </span>
          </div>
     </div>
     @endif
</div>