@section("title", 'Dashboard')
@section("label", 'Dashboard')
@section("url", 'dashboard')
@include("admin.sections.header")
@include("admin.sections.navigation")
<!-- BEGIN: Content-->
<div class="app-content content ">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-left mb-0">Statistics</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item active">Dashboard
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
        <div class="content-body">
            <!-- Dashboard Ecommerce Starts -->
            <section id="dashboard-ecommerce">
                <div class="row match-height">
                    <!-- Medal Card -->
                    
                    <!--/ Medal Card -->
                    <!-- Statistics Card -->
                    <div class="col-xl-12 col-md-6 col-12">
                        <div class="card card-statistics">
                            <div class="card-header">
                                <h4 class="card-title">Statistics</h4>
                                <!--<div class="d-flex align-items-center">
                                    <p class="card-text font-small-2 mr-25 mb-0">Updated 1 month ago</p>
                                </div>-->
                            </div>
                            <div class="card-body statistics-body">
                                <div class="row">
                                    <div class="col-xl-4 col-sm-6 col-12 mb-2 mb-xl-0">
                                        <a href="users-list">
                                            <div class="media">
                                                <div class="avatar bg-light-primary mr-2">
                                                    <div class="avatar-content">
                                                        <i data-feather="users" class="avatar-icon"></i>
                                                    </div>
                                                </div>
                                                <div class="media-body my-auto">
                                                    <h4 class="font-weight-bolder mb-0">{{$users}}</h4>
                                                    <p class="card-text font-small-3 mb-0">Users</p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-xl-4 col-sm-6 col-12 mb-2 mb-xl-0">
                                        <a href="posts-list">
                                            <div class="media">
                                                <div class="avatar bg-light-info mr-2">
                                                    <div class="avatar-content">
                                                        <i data-feather="message-square" class="avatar-icon"></i>
                                                    </div>
                                                </div>
                                                <div class="media-body my-auto">
                                                    <h4 class="font-weight-bolder mb-0">{{$posts}}</h4>
                                                    <p class="card-text font-small-3 mb-0">Posts</p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-xl-4 col-sm-6 col-12 mb-2 mb-sm-0">
                                        <a href="sports-list">
                                            <div class="media">
                                                <div class="avatar bg-light-danger mr-2">
                                                    <div class="avatar-content">
                                                        <i data-feather="aperture" class="avatar-icon"></i>
                                                    </div>
                                                </div>
                                                <div class="media-body my-auto">
                                                    <h4 class="font-weight-bolder mb-0">{{$sports}}</h4>
                                                    <p class="card-text font-small-3 mb-0">Sports</p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--/ Statistics Card -->
                </div>
            </section>
            <!-- Dashboard Ecommerce ends -->
        </div>
    </div>
</div>
<!-- END: Content-->
@include("admin.sections.footer")