@section("title", 'Users List')
@section("label", 'Users List')
@section("url", 'users-list')
@include("admin.sections.header")
@include("admin.sections.navigation")
<!-- BEGIN: Content-->
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
                                <li class="breadcrumb-item"><a href="dashboard">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item active">Users List
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
                                                <div class="alert-body">{{ Session::get('alert-' . $msg) }}</div>
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
            <!-- Basic table -->
            <section id="basic-datatable">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <table class="datatables-basic table">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th>id</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Date Of Birth</th>
                                        <th>Register</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Modal to add new record -->
                <div class="modal modal-slide-in fade" id="modals-slide-in">
                    <div class="modal-dialog sidebar-sm">
                        <form class="add-new-record modal-content pt-0" method="post" action="save-user">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                            <div class="modal-header mb-1">
                                <h5 class="modal-title" id="exampleModalLabel">Add User</h5>
                            </div>
                            <input type="hidden" id="basic-icon-default-userId" name="user_id">
                            <div class="modal-body flex-grow-1">
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">Full Name</label>
                                    <input type="text" class="form-control dt-full-name" name="name" id="basic-icon-default-fullname" placeholder="Enter Fullname" aria-label="Enter Fullname" required="" />
                                    <span id="errorMessage" class="nameError"></span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-post">Mobile</label>
                                    <input type="text" id="basic-icon-default-mobile" onkeypress="return isNumberKey(event)" name="mobile" class="form-control dt-post" placeholder="Enter Mobile" maxlength="10" aria-label="Enter Mobile" required="" />
                                    <span id="errorMessage" class="mobileError"></span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-email">Email</label>
                                    <input type="email" id="basic-icon-default-email" name="email" class="form-control dt-email" placeholder="Enter Email" aria-label="Enter Email" required="" />
                                    <small class="form-text text-muted"> You can use letters, numbers & periods </small>
                                    <span id="errorMessage" class="emailError"></span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-date">Date Of Birth</label>
                                    <input type="text" class="form-control dt-date" name="dob" id="basic-icon-default-date" placeholder="MM/DD/YYYY" aria-label="MM/DD/YYYY" required="" />
                                    <span id="errorMessage" class="dobError"></span>
                                </div>
                                <div class="form-group">
                                    <label for="basicSelect">Gender</label>
                                    <select class="form-control" name="gender" id="basicSelect">
                                        <option value="0">Male</option>
                                        <option value="1">Female</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="exampleFormControlTextarea1">Bio</label>
                                    <textarea class="form-control" name="bio" id="exampleFormControlTextarea1" rows="3" placeholder="User Bio here"></textarea>
                                </div>
                                <div class="form-group mb-4">
                                    <label class="form-label" for="basic-icon-default-salary">Password</label>
                                    <input type="password" id="basic-icon-default-password" name="password" class="form-control dt-salary" placeholder="Enter Passsword" aria-label="Enter Passsword" required="" />
                                    <span id="errorMessage" class="passwordError"></span>
                                </div>
                                <button type="button" onclick="saveUser();" class="btn btn-primary mr-1">Submit</button>
                                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="modal modal-slide-in fade" id="modals-slide-in-image">
                    <div class="modal-dialog sidebar-sm">
                        <form class="add-new-record modal-content pt-0" action="{{ url('/upload-photo') }}" method="post" enctype="multipart/form-data">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                            <div class="modal-header mb-1">
                                <h5 class="modal-title" id="exampleModalLabel">Upload Image</h5>
                            </div>
                            <input type="hidden" id="basic-icon-default-userId" name="user_id">
                            <div class="modal-body flex-grow-1">
                                {{ csrf_field() }}
                                <input type="hidden" name="user_id_photo" id="user_id_photo">
                                <input type="hidden" name="user_photo_background" id="user_photo_background" value="1">
                                <div class="form-group">
                                        <label for="customFile">Profile Photo</label>
                                        <div class="custom-file">
                                            <input type="file" name="photo" accept="image/*" class="custom-file-input" id="customFile" />
                                            <label class="custom-file-label" for="customFile">Choose file</label>
                                        </div>

                                    <div class="form-group">
                                        <label for="customFile">Background</label>
                                        <div class="custom-file">
                                            <input type="file" name="background" accept="image/*" class="custom-file-input" id="customFile" />
                                            <label class="custom-file-label" for="customFile">Choose file</label>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mr-1">Submit</button>
                                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                            </div>
                      </form>   
                    </div>
                </div>
            </section>
            <!--/ Basic table -->
        </div>
    </div>
</div>
<!-- END: Content-->
<!-- END: Content-->
@include("admin.sections.footer")
<script>
$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});
function isNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        if (charCode == 46) {
            return true;
        } else {
            return false;
        }
    }
    return true;
}
function editUser(elem) {
    //console.log(elem);
    $("#basic-icon-default-fullname,#basic-icon-default-mobile,#basic-icon-default-email,#basic-icon-default-date,#basic-icon-default-password,#exampleFormControlTextarea1,#basic-icon-default-userId").val('');
    //$("#basic-icon-default-password").removeAttr("required");​​​​​
    $("#basic-icon-default-userId").val($(elem).attr('data-id'));
    $("#basic-icon-default-fullname").val($(elem).attr('data-fullname'));
    $("#basic-icon-default-mobile").val($(elem).attr('data-mobile'));
    $("#basic-icon-default-email").val($(elem).attr('data-email'));
    $("#basic-icon-default-date").val($(elem).attr('data-dob'));
    $("#basicSelect").val($(elem).attr('data-gender'));
    $("#exampleFormControlTextarea1").val($(elem).attr('data-bio'));
    $('#modals-slide-in').modal('show');
}
function uploadImage(elem){
    $("#user_id_photo").val($(elem).attr('data-id'));
    $('#modals-slide-in-image').modal('show');
}
function saveUser() {
    var userId = $("#basic-icon-default-userId").val();
    var fullName = $("#basic-icon-default-fullname").val();
    var mobile = $("#basic-icon-default-mobile").val();
    var email = $("#basic-icon-default-email").val();
    var dob = $("#basic-icon-default-date").val();
    var gender = $("#basicSelect").val();
    var bio = $("#exampleFormControlTextarea1").val();
    var password = $("#basic-icon-default-password").val();

    $(".nameError,.mobileError,.emailError,.dobError,.passwordError").text('');
    $.ajax({
        type: 'POST',
        url: 'save-user',
        data: {userId, fullName, mobile, email, dob, gender, bio, password, "_token": "{{ csrf_token() }}"},
        success: function (response) {
            //console.log("S"+response);
            //$('#main-overlay').fadeOut();
            //$('.loader').fadeOut();
            if (response.status == 1) {
                var message = "User added successfully.";
                var title = "Added!";
                if (userId > 0) {
                    var title = "Updated!";
                    message = "User updated successfully.";
                }
                Swal.fire({
                    icon: 'success',
                    title: title,
                    text: message,
                    customClass: {
                        confirmButton: 'btn btn-success'
                    }
                }).then(({value}) => {
                    location.reload(true);
                });
            } else {
                if (response.error.fullName) {
                    $(".nameError").text(response.error.fullName);
                }
                if (response.error.mobile) {
                    $(".mobileError").text(response.error.mobile);
                }
                if (response.error.email) {
                    $(".emailError").text(response.error.email);
                }
                if (response.error.dob) {
                    $(".dobError").text(response.error.dob);
                }
                if (response.error.password) {
                    $(".passwordError").text(response.error.password);
                }
            }
        }
    });
}
function deleteAllUsers(){
    var checkboxValues = [];
    $('input[name=userselect]:checked').map(function() {
        if($(this).val() > 0){
            checkboxValues.push($(this).val());
        }
    });
    console.log(checkboxValues.length);
    if(checkboxValues.length > 0){
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to delete selected users!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-outline-danger ml-1'
            },
            buttonsStyling: false
        }).then(function (result) {
            if (result.value) {
                $.ajax({
                    type: 'POST',
                    url: 'delete-records',
                    data: {checkboxValues,"table":"users", "_token": "{{ csrf_token() }}"},
                    success: function (response) {
                        if (response.status == 1) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Selected users has been deleted.',
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                }
                            }).then(({value}) => {
                                location.reload(true);
                            });
                        } else {
                            Swal.fire({
                                title: 'Cancelled',
                                text: 'Error while delete selected users :)',
                                icon: 'error',
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                }
                            });
                        }
                    }
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire({
                    title: 'Cancelled',
                    text: 'Selected users are safe :)',
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn btn-success'
                    }
                });
            }
        });
    }else{
        Swal.fire({
            title: 'Cancelled',
            text: 'Please select at least one user.',
            icon: 'error',
            customClass: {
                confirmButton: 'btn btn-primary'
            }
        });
    }
}
function deleteUser(elem) {
    var userId = $(elem).attr('data-id');
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to delete this user!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-outline-danger ml-1'
        },
        buttonsStyling: false
    }).then(function (result) {
        if (result.value) {
            $.ajax({
                type: 'POST',
                url: 'delete-user',
                data: {userId, "_token": "{{ csrf_token() }}"},
                success: function (response) {
                    if (response.status == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Your user has been deleted.',
                            customClass: {
                                confirmButton: 'btn btn-success'
                            }
                        }).then(({value}) => {
                            location.reload(true);
                        });
                    } else {
                        Swal.fire({
                            title: 'Cancelled',
                            text: 'Error while delete user :)',
                            icon: 'error',
                            customClass: {
                                confirmButton: 'btn btn-success'
                            }
                        });
                    }
                }
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            Swal.fire({
                title: 'Cancelled',
                text: 'Your user is safe :)',
                icon: 'error',
                customClass: {
                    confirmButton: 'btn btn-success'
                }
            });
        }
    });
}
</script>