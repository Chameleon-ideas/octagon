@section("title", 'Teams List')
@section("label", 'Teams List')
@section("url", 'teams-list')
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
                        <h2 class="content-header-title float-left mb-0">Teams List</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item active">Teams List
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
                            <table class="datatables-basic5 table-team-datatable">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th>id</th>
                                        <th>Team</th>
                                        <th>Name</th>
                                        <th>Logo</th>
                                        <th>Sports</th>
                                        <th>League</th>
                                        <th>Country</th>
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
                                <h5 class="modal-title" id="exampleModalLabel">Edit Team</h5>
                            </div>
                            <input type="hidden" id="basic-icon-default-teamId" name="team_id">
                            <div class="modal-body flex-grow-1">
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-teamname">Team Name</label>
                                    <input type="text" class="form-control dt-full-name" name="name" id="basic-icon-default-teamname" placeholder="Enter Team Name" aria-label="Enter Team Name" required="" />
                                    <span id="errorMessage" class="nameError"></span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-teamname">Sport</label>
                                    <input type="text" class="form-control dt-full-name" name="sport" id="basic-icon-default-sport" placeholder="Enter Sport" aria-label="Enter Sport" required="" />
                                    <span id="errorMessage" class="sportError"></span>
                                </div>
                                <button type="button" onclick="saveTeam();" class="btn btn-primary mr-1">Submit</button>
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
function editTeam(elem) {
    //console.log(elem);
    $("#basic-icon-default-teamname").val('');
    $("#basic-icon-default-teamId").val($(elem).attr('data-id'));
    $("#basic-icon-default-teamname").val($(elem).attr('data-teamname'));
    $('#modals-slide-in').modal('show');
}
function saveTeam() {
    var teamId = $("#basic-icon-default-teamId").val();
    var teamName = $("#basic-icon-default-teamname").val();
    $(".nameError").text('');
    $.ajax({
        type: 'POST',
        url: 'save-team',
        data: {teamId, teamName, "_token": "{{ csrf_token() }}"},
        success: function (response) {
            //console.log("S"+response);
            //$('#main-overlay').fadeOut();
            //$('.loader').fadeOut();
            if (response.status == 1) {
                var message = "Team added successfully.";
                var title = "Added!";
                if (teamId > 0) {
                    var title = "Updated!";
                    message = "Team updated successfully.";
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
            }
        }
    });
}
function deleteAllTeams(){
    var checkboxValues = [];
    $('input[name=teamselect]:checked').map(function() {
        if($(this).val() > 0){
            checkboxValues.push($(this).val());
        }
    });
    console.log(checkboxValues.length);
    if(checkboxValues.length > 0){
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to delete selected teams!",
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
                    data: {checkboxValues,"table":"team", "_token": "{{ csrf_token() }}"},
                    success: function (response) {
                        if (response.status == 1) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Selected teams has been deleted.',
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                }
                            }).then(({value}) => {
                                location.reload(true);
                            });
                        } else {
                            Swal.fire({
                                title: 'Cancelled',
                                text: 'Error while delete selected teams :)',
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
                    text: 'Selected teams are safe :)',
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
            text: 'Please select at least one team.',
            icon: 'error',
            customClass: {
                confirmButton: 'btn btn-primary'
            }
        });
    }
}
function deleteTeam(elem) {
    var teamId = $(elem).attr('data-id');
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to delete this team!",
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
                url: 'delete-team',
                data: {teamId, "_token": "{{ csrf_token() }}"},
                success: function (response) {
                    if (response.status == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Your team has been deleted.',
                            customClass: {
                                confirmButton: 'btn btn-success'
                            }
                        }).then(({value}) => {
                            location.reload(true);
                        });
                    } else {
                        Swal.fire({
                            title: 'Cancelled',
                            text: 'Error while delete team :)',
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
                text: 'Your team is safe :)',
                icon: 'error',
                customClass: {
                    confirmButton: 'btn btn-success'
                }
            });
        }
    });
}
</script>