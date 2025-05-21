@section("title", 'Post Report List')
@section("label", 'Post Report List')
@section("url", 'post-report-list')
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
                            <h2 class="content-header-title float-left mb-0">Post Report List</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item active">Post Report List
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="content-body">
                
                <!-- Basic table -->
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <table class="datatables-basic4 table-post-report-datatable">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th>id</th>
                                            <th>User</th>
                                            <th>Post</th>
                                            <th>Report</th>
                                            <th>Reported By</th>
                                            <th>Creates</th>
                                            <!--<th>Action</th>-->
                                        </tr>
                                    </thead>
                                </table>
                            </div>
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
    $(document).ready(function(){
        $.ajaxSetup({
            headers:{
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    });
    function deleteAllPostReports(){
        var checkboxValues = [];
        $('input[name=postrepotselect]:checked').map(function() {
            if($(this).val() > 0){
                checkboxValues.push($(this).val());
            }
        });
        console.log(checkboxValues.length);
        if(checkboxValues.length > 0){
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to delete selected post report!",
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
                        data: {checkboxValues,"table":"postreport", "_token": "{{ csrf_token() }}"},
                        success: function (response) {
                            if (response.status == 1) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'Selected post report has been deleted.',
                                    customClass: {
                                        confirmButton: 'btn btn-success'
                                    }
                                }).then(({value}) => {
                                    location.reload(true);
                                });
                            } else {
                                Swal.fire({
                                    title: 'Cancelled',
                                    text: 'Error while delete selected post report :)',
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
                        text: 'Selected post report are safe :)',
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
                text: 'Please select at least one post report.',
                icon: 'error',
                customClass: {
                    confirmButton: 'btn btn-primary'
                }
            });
        }
    }
    function deletePostReport(elem) {
        var postId = $(elem).attr('data-id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to delete this post report!",
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
                    url: 'delete-post-report',
                    data: {postId, "_token": "{{ csrf_token() }}"},
                    success: function (response) {
                        if (response.status == 1) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Your post report has been deleted.',
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                }
                            }).then(({value}) => {
                                location.reload(true);
                            });
                        } else {
                            Swal.fire({
                                title: 'Cancelled',
                                text: 'Error while delete post report :)',
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
                    text: 'Your post report is safe :)',
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn btn-success'
                    }
                });
            }
        });
    }
</script>