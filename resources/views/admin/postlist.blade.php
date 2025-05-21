@section("title", 'Posts List')
@section("label", 'Posts List')
@section("url", 'posts-list')
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
                            <h2 class="content-header-title float-left mb-0">Posts List</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item active">Posts List
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
                                <table class="datatables-basic1 table-post-datatable">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th>id</th>
                                            <th>User</th>
                                            <th>Post</th>
                                            <th>Creates</th>
                                            <th>Updated</th>
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
                            <form class="add-new-record modal-content pt-0" method="post" action="save-post">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                                <div class="modal-header mb-1">
                                    <h5 class="modal-title" id="exampleModalLabel">Add Post</h5>
                                </div>
                                <input type="hidden" id="basic-icon-default-postId" name="post_id">
                                <div class="modal-body flex-grow-1">
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-post">Post</label>
                                        <input type="text" class="form-control dt-full-name" name="name" id="basic-icon-default-post" placeholder="Enter Post" aria-label="Enter Post" required="" />
                                        <span id="errorMessage" class="postError"></span>
                                    </div>
                                    <div class="form-group">
                                        <label for="basicSelect">Select</label>
                                        <select class="form-control" name="gender" id="basicSelect">
                                            <option value="">Select user</option>
                                            @foreach($userlist as $user)
                                                <option value="{{$user['id']}}">{{$user['name']}}</option>
                                            @endforeach
                                        </select>
                                        <span id="errorMessage" class="userError"></span>
                                    </div>
                                    <button type="button" onclick="savePost();" class="btn btn-primary mr-1">Submit</button>
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
    $(document).ready(function(){
        $.ajaxSetup({
            headers:{
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    });
    function editPost(elem){
        console.log(elem);
        $("#basic-icon-default-postId,#basic-icon-default-post,#basicSelect").val('');
        //$("#basic-icon-default-password").removeAttr("required");​​​​​
        $("#basic-icon-default-postId").val($(elem).attr('data-id'));
        $("#basic-icon-default-post").val($(elem).attr('data-post'));
        $("#basicSelect").val($(elem).attr('data-user'));
        $('#modals-slide-in').modal('show');
    }
    function savePost(){
        var postId = $("#basic-icon-default-postId").val();
        var postName = $("#basic-icon-default-post").val();
        var userId = $("#basicSelect").val();
        
        $(".postError,.userError").text('');
        $.ajax({
            type:'POST',
            url:'save-post',
            data:{postId,postName,userId,"_token": "{{ csrf_token() }}"},
            success:function(response){
                //console.log("S"+response);
                //$('#main-overlay').fadeOut();
                //$('.loader').fadeOut();
                if(response.status == 1) {
                    var message = "Post added successfully.";
                    var title = "Added!";
                    if (postId > 0) {
                        var title = "Updated!";
                        message = "Post updated successfully.";
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
                    if(response.error.postName) {
                        $(".postError").text(response.error.postName);
                    }
                    if(response.error.userId) {
                        $(".userError").text(response.error.userId);
                    }
                }
            }
        });
    }
    function deleteAllPosts(){
        var checkboxValues = [];
        $('input[name=postselect]:checked').map(function() {
            if($(this).val() > 0){
                checkboxValues.push($(this).val());
            }
        });
        console.log(checkboxValues.length);
        if(checkboxValues.length > 0){
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to delete selected posts!",
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
                        data: {checkboxValues,"table":"posts", "_token": "{{ csrf_token() }}"},
                        success: function (response) {
                            if (response.status == 1) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'Selected posts has been deleted.',
                                    customClass: {
                                        confirmButton: 'btn btn-success'
                                    }
                                }).then(({value}) => {
                                    location.reload(true);
                                });
                            } else {
                                Swal.fire({
                                    title: 'Cancelled',
                                    text: 'Error while delete selected posts :)',
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
                        text: 'Selected posts are safe :)',
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
                text: 'Please select at least one post.',
                icon: 'error',
                customClass: {
                    confirmButton: 'btn btn-primary'
                }
            });
        }
    }
    function deletePost(elem) {
        var postId = $(elem).attr('data-id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to delete this post!",
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
                    url: 'delete-post',
                    data: {postId, "_token": "{{ csrf_token() }}"},
                    success: function (response) {
                        if (response.status == 1) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Your post has been deleted.',
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                }
                            }).then(({value}) => {
                                location.reload(true);
                            });
                        } else {
                            Swal.fire({
                                title: 'Cancelled',
                                text: 'Error while delete post :)',
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
                    text: 'Your post is safe :)',
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn btn-success'
                    }
                });
            }
        });
    }
</script>