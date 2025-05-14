@section("title", 'Dashboard')
@section("label", 'Dashboard')
@section("url", 'dashboard')
@include("admin.sections.header")
@include("admin.sections.navigation")
<!-- BEGIN: Content-->
    <style>
        #errorMessage {
        color:red;
    }
    </style>
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-left mb-0">Users List</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active"> Account Settings
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                @foreach (['danger', 'warning', 'success', 'info'] as $msg)
                    @if(Session::has('alert-' . $msg))
                        <!-- Basic Alerts start -->
                        <section id="basic-alerts">
                            <div class="row">
                                <div class="col-xl-12 col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="demo-spacing-0">
                                                <div class="alert alert-{{ $msg }}" role="alert">
                                                    <div class="alert-body"><strong>Success!</strong> {{ Session::get('alert-' . $msg) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <!-- Basic Alerts end -->
                    @endif
                @endforeach
                <!-- account setting page -->
                <section id="page-account-settings">
                    <div class="row">
                        <!-- left menu section -->
                        <div class="col-md-3 mb-2 mb-md-0">
                            <ul class="nav nav-pills flex-column nav-left">
                                <!-- general -->
                                <li class="nav-item">
                                    <a class="nav-link active" id="account-pill-general" data-toggle="pill" href="#account-vertical-general" aria-expanded="true">
                                        <i data-feather="user" class="font-medium-3 mr-1"></i>
                                        <span class="font-weight-bold">General</span>
                                    </a>
                                </li>
                                <!-- change password -->
                                <li class="nav-item">
                                    <a class="nav-link" id="account-pill-password" data-toggle="pill" href="#account-vertical-password" aria-expanded="false">
                                        <i data-feather="lock" class="font-medium-3 mr-1"></i>
                                        <span class="font-weight-bold">Change Password</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!--/ left menu section -->
                        <input type="hidden" name="user_id" value="{{$userdetails['id']}}" id="user_id">
                        <!-- right content section -->
                        <div class="col-md-9">
                            <div class="card">
                                <div class="card-body">
                                    <div class="tab-content">
                                        <!-- general tab -->
                                        <div role="tabpanel" class="tab-pane active" id="account-vertical-general" aria-labelledby="account-pill-general" aria-expanded="true">
                                            <form action="{{ url('/upload-photo') }}" method="post" enctype="multipart/form-data">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="user_id_photo" value="{{$userdetails['id']}}" id="user_id_photo">
                                                <!-- header media -->
                                                <div class="media">
                                                    <a href="javascript:void(0);" class="mr-25">
                                                        <img src="{{$userdetails['photo']}}" id="account-upload-img" class="rounded mr-50" alt="Profile image" height="80" width="80" />
                                                    </a>
                                                    <!-- upload and reset button -->
                                                    <div class="media-body mt-75 ml-1">
                                                        <label for="customFile1">Profile pic</label>
                                                        <div class="custom-file">
                                                            <input type="file" name="photo" class="custom-file-input" id="customFile1" accept="image/*" required="">
                                                            <label class="custom-file-label" for="customFile1">Choose profile pic</label>
                                                        </div>
                                                    </div>
                                                    <div class="media-body mt-75 ml-1">
                                                        <!--<label for="account-upload" class="btn btn-sm btn-primary mb-75 mr-75">Upload</label>
                                                        <input type="file" id="account-upload" name="photo" class="custom-file-input" id="customFile1" required="" hidden accept="image/*" />-->
                                                        <button class="btn btn-sm btn-primary mb-75 mr-75">Save</button>
                                                        <p>Allowed JPG, GIF or PNG. Max size of 800kB</p>
                                                    </div>
                                                    <!--/ upload and reset button -->
                                                </div>
                                                <!--/ header media -->
                                            </form>
                                            <!-- form -->
                                            <form class="validate-form mt-2">
                                                <div class="row">
                                                    <div class="col-12 col-sm-6">
                                                        <div class="form-group">
                                                            <label for="account-name">FullName</label>
                                                            <input type="text" class="form-control" id="account-name" name="name" placeholder="Enter FullName" value="{{$userdetails['name']}}" />
                                                            <span id="errorMessage" class="nameError"></span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-12 col-sm-6">
                                                        <div class="form-group">
                                                            <label for="account-email">E-mail</label>
                                                            <input type="email" class="form-control" id="account-email" name="email" placeholder="Enter Email" value="{{$userdetails['email']}}" />
                                                            <span id="errorMessage" class="emailError"></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-4">
                                                        <div class="form-group">
                                                            <label for="account-mobile">Mobile</label>
                                                            <input type="text" class="form-control" id="account-mobile" placeholder="Enter Mobile" value="{{$userdetails['mobile']}}" onkeypress="return isNumberKey(event)" maxlength="10" name="mobile" />
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-4">
                                                        <div class="form-group">
                                                            <label for="account-gender">Gender</label>
                                                            <select class="form-control" name="gender" id="basicSelect">
                                                                <option value="0">Male</option>
                                                                <option value="1">Female</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                     <div class="col-12 col-sm-4">
                                                        <div class="form-group">
                                                            <label class="form-label" for="basic-icon-default-date">Date Of Birth</label>
                                                            <input type="text" class="form-control dt-date" name="dob" id="basic-icon-default-date" placeholder="MM/DD/YYYY" aria-label="MM/DD/YYYY" value="{{$userdetails['dob']}}" required="" />
                                                            <span id="errorMessage" class="dobError"></span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="accountTextarea">Bio</label>
                                                            <textarea class="form-control" id="accountTextarea" rows="4" name="bio" placeholder="Your Bio data here...">{{$userdetails['bio']}}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <button type="button" onclick="saveGeneral();" class="btn btn-primary mt-2 mr-1">Save changes</button>
                                                        <button type="reset" class="btn btn-outline-secondary mt-2">Cancel</button>
                                                    </div>
                                                </div>
                                            </form>
                                            <!--/ form -->
                                        </div>
                                        <!--/ general tab -->

                                        <!-- change password -->
                                        <div class="tab-pane fade" id="account-vertical-password" role="tabpanel" aria-labelledby="account-pill-password" aria-expanded="false">
                                            <!-- form -->
                                            <form class="validate-form">
                                                <div class="row">
                                                    <div class="col-12 col-sm-6">
                                                        <div class="form-group">
                                                            <label for="account-current-password">Current Password</label>
                                                            <div class="input-group form-password-toggle input-group-merge">
                                                                <input type="password" class="form-control" id="account-current-password" name="password" placeholder="Current Password" />
                                                                <div class="input-group-append">
                                                                    <div class="input-group-text cursor-pointer">
                                                                        <i data-feather="eye"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <span id="errorMessage" class="currentpwdError"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12 col-sm-6">
                                                        <div class="form-group">
                                                            <label for="account-new-password">New Password</label>
                                                            <div class="input-group form-password-toggle input-group-merge">
                                                                <input type="password" id="account-new-password" name="new-password" class="form-control" placeholder="New Password" />
                                                                <div class="input-group-append">
                                                                    <div class="input-group-text cursor-pointer">
                                                                        <i data-feather="eye"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <span id="errorMessage" class="newpwdError"></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <div class="form-group">
                                                            <label for="account-confirm-new-password">Confirm New Password</label>
                                                            <div class="input-group form-password-toggle input-group-merge">
                                                                <input type="password" class="form-control" id="account-confirm-new-password" name="confirm-new-password" placeholder="New Password" />
                                                                <div class="input-group-append">
                                                                    <div class="input-group-text cursor-pointer"><i data-feather="eye"></i></div>
                                                                </div>
                                                            </div>
                                                            <span id="errorMessage" class="confirmnewpwdError"></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <button type="button" onclick="changePassword();" class="btn btn-primary mr-1 mt-1">Save changes</button>
                                                        <button type="reset" class="btn btn-outline-secondary mt-1">Cancel</button>
                                                    </div>
                                                </div>
                                            </form>
                                            <!--/ form -->
                                        </div>
                                        <!--/ change password -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--/ right content section -->
                    </div>
                </section>
                <!-- / account setting page -->

            </div>
        </div>
    </div>
    <!-- END: Content-->
@include("admin.sections.footer")
<script>
    $(document).ready(function(){
        $.ajaxSetup({
            headers:{
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    });
    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57)){
            if(charCode ==46){
                return true;
            }else{
                return false;
            }
        }
        return true;
    }
    function saveGeneral(){
        var userId = $("#user_id").val();
        var fullName = $("#account-name").val();
        var email = $("#account-email").val();
        var mobile = $("#account-mobile").val();
        var gender = $("#basicSelect").val();
        var dob = $("#basic-icon-default-date").val();
        var bio = $("#accountTextarea").val();
        $(".nameError,.emailError").text('');
        $.ajax({
            type:'POST',
            url:'save-user',
            data:{userId,fullName,email,mobile,gender,dob,bio,"_token": "{{ csrf_token() }}"},
            success:function(response){
                if(response.status == 1) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated',
                        text: 'Profile updated successfully.',
                        customClass: {
                            confirmButton: 'btn btn-success'
                        }
                    }).then(({value}) => {
                        location.reload(true);
                    });
                } else {
                    if(response.error.fullName) {
                        $(".nameError").text(response.error.fullName);
                    }
                    if(response.error.email) {
                        $(".emailError").text(response.error.email);
                    }
                }
            }
        });
    }
    function changePassword(){
        var userId = $("#user_id").val();
        var currentPassword = $("#account-current-password").val();
        var newPassword = $("#account-new-password").val();
        var confirmNewPassword = $("#account-confirm-new-password").val();
        $(".currentpwdError,.newpwdError,.confirmnewpwdError").text('');
        /*if(currentPassword == ""){
            $(".currentpwdError").text("Please enter current Password.");
            return false;
        }
        if(newPassword == ""){
            $(".newpwdError").text("Please enter new Password.");
            return false;
        }
        if(confirmNewPassword == ""){
            $(".confirmnewpwdError").text("Please enter confirm new Password.");
            return false;
        }
        if(newPassword != confirmNewPassword){
            $(".confirmnewpwdError").text("New password and confirm new password must be same.");
            return false;
        }
        if(currentPassword != newPassword){
            $(".newpwdError").text("Current password and new password must be different.");
            return false;
        }*/
        $.ajax({
            type:'POST',
            url:'change-password',
            data:{userId,currentPassword,newPassword,confirmNewPassword,"_token": "{{ csrf_token() }}"},
            success:function(response){
                //console.log("S"+response);
                //$('#main-overlay').fadeOut();
                //$('.loader').fadeOut();
                if(response.status == 1) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Changed',
                        text: 'Password changed successfully.',
                        customClass: {
                            confirmButton: 'btn btn-success'
                        }
                    }).then(({value}) => {
                        location.reload(true);
                    });
                } else {
                    if(response.error.currentPassword) {
                        $(".currentpwdError").text(response.error.currentPassword);
                    }
                    if(response.error.newPassword) {
                        $(".newpwdError").text(response.error.newPassword);
                    }
                    if(response.error.confirmNewPassword) {
                        $(".confirmnewpwdError").text(response.error.confirmNewPassword);
                    }
                }
            }
        });
    }
</script>