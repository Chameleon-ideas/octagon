<div class="main-menu-content">
    <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
        <li class="{{ (request()->is('dashboard')) ? 'active' : '' }} nav-item"><a class="d-flex align-items-center" href="/dashboard"><i data-feather="home"></i><span class="menu-title text-truncate" data-i18n="Dashboards">Dashboards</span><span class="badge badge-light-warning badge-pill ml-auto mr-1"></span></a>
        </li>
        <li class="{{ (request()->is('users-list')) ? 'active' : '' }} nav-item"><a class="d-flex align-items-center" href="/users-list"><i data-feather="users"></i><span class="menu-title text-truncate" data-i18n="Dashboards">User Listing</span><span class="badge badge-light-warning badge-pill ml-auto mr-1"></span></a>
        </li>
        <li class="{{ (request()->is('posts-list')) ? 'active' : '' }} nav-item"><a class="d-flex align-items-center" href="{{ route('posts-list') }}"><i data-feather="message-square"></i><span class="menu-title text-truncate" data-i18n="Dashboards">Post Listing</span><span class="badge badge-light-warning badge-pill ml-auto mr-1"></span></a>
        </li>
        <li class="{{ (request()->is('post-report-list')) ? 'active' : '' }} nav-item"><a class="d-flex align-items-center" href="{{ route('post-report-list') }}"><i data-feather="message-square"></i><span class="menu-title text-truncate" data-i18n="Dashboards">Post Report Listing</span><span class="badge badge-light-warning badge-pill ml-auto mr-1"></span></a>
        </li>
        <li class="{{ (request()->is('sports-list')) ? 'active' : '' }} nav-item"><a class="d-flex align-items-center" href="/sports-list"><i data-feather="aperture"></i><span class="menu-title text-truncate" data-i18n="Dashboards">Sports Listing</span><span class="badge badge-light-warning badge-pill ml-auto mr-1"></span></a>
        </li>
        <li class="{{ (request()->is('teams-list')) ? 'active' : '' }} nav-item"><a class="d-flex align-items-center" href="/teams-list"><i data-feather="users"></i><span class="menu-title text-truncate" data-i18n="Dashboards">Team Listing</span><span class="badge badge-light-warning badge-pill ml-auto mr-1"></span></a>
        </li>
    </ul>
</div>
</div>
<!-- END: Main Menu-->