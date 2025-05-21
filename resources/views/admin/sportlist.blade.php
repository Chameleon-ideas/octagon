@section("title", 'Sports List')
@section("label", 'Sports List')
@section("url", 'sports-list')
@include("admin.sections.header")
@include("admin.sections.navigation")
<!-- BEGIN: Content-->
<!-- BEGIN: Content-->
    <style>
        #errorMessage {
        color:red;
    }
    tr td img {
        max-width: 200px;
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
                            <h2 class="content-header-title float-left mb-0">Sports List</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item active">Sports List
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
                                <table class="datatables-basic1 table-sport-datatable">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th>id</th>
                                            <th>Name</th>
                                            <th>Thumb</th>
                                            <th>Created</th>
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
                            <form class="add-new-record modal-content pt-0" method="post" action="save-sport">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                                <div class="modal-header mb-1">
                                    <h5 class="modal-title" id="exampleModalLabel">Add Sport</h5>
                                </div>
                                <input type="hidden" id="basic-icon-default-sportId" name="sport_id">
                                <div class="modal-body flex-grow-1">
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-idSport">Sport Id</label>
                                        <input type="text" id="basic-icon-default-idSport" onkeypress="return isNumberKey(event)" name="idSport" class="form-control dt-post" placeholder="Sport Id" maxlength="10" aria-label="Sport Id" required="" />
                                        <span id="errorMessage" class="idSportError"></span>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-strSport">Sport Name</label>
                                        <input type="text" class="form-control dt-full-name" name="strSport" id="basic-icon-default-strSport" placeholder="Enter Sport Name" aria-label="Enter Sport Name" required="" />
                                        <span id="errorMessage" class="strSportError"></span>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-strFormat">Sport Format</label>
                                        <input type="text" class="form-control dt-full-name" name="strFormat" id="basic-icon-default-strFormat" placeholder="Enter Format" aria-label="Enter Format" required="" />
                                        <span id="errorMessage" class="strFormatError"></span>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-strSportThumb">Sport Thumb URL</label>
                                        <input type="text" class="form-control dt-full-name" name="strSportThumb" id="basic-icon-default-strSportThumb" placeholder="Enter Sport Thumb URL" aria-label="Enter Sport Thumb URL" required="" />
                                        <span id="errorMessage" class="strSportThumbError"></span>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-strSportIconGreen">Sport Icon URL</label>
                                        <input type="text" class="form-control dt-full-name" name="strSportIconGreen" id="basic-icon-default-strSportIconGreen" placeholder="Enter Sport Icon URL" aria-label="Enter Sport Icon URL" required="" />
                                        <span id="errorMessage" class="strSportIconGreenError"></span>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleFormControlTextarea1">Sport Description</label>
                                        <textarea class="form-control" name="strSportDescription" id="exampleFormControlTextarea1" rows="3" placeholder="Sport Description"></textarea>
                                    </div>
                                    <button type="button" onclick="saveSport();" class="btn btn-primary mr-1">Submit</button>
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
    function editSport(elem){
        console.log(elem);
        $("#basic-icon-default-sportId,#basic-icon-default-idSport,#basic-icon-default-strSport,#basic-icon-default-strFormat,#basic-icon-default-strSportThumb,#basic-icon-default-strSportIconGreen,#exampleFormControlTextarea1").val('');
        //$("#basic-icon-default-password").removeAttr("required");​​​​​
        $("#basic-icon-default-sportId").val($(elem).attr('data-id'));
        $("#basic-icon-default-idSport").val($(elem).attr('data-idSport'));
        $("#basic-icon-default-strSport").val($(elem).attr('data-strSport'));
        $("#basic-icon-default-strFormat").val($(elem).attr('data-strFormat'));
        $("#basic-icon-default-strSportThumb").val($(elem).attr('data-strSportThumb'));
        $("#basic-icon-default-strSportIconGreen").val($(elem).attr('data-strSportIconGreen'));
        $("#exampleFormControlTextarea1").val($(elem).attr('data-strSportDescription'));
        $('#modals-slide-in').modal('show');
    }
    function saveSport(){
        var Id = $("#basic-icon-default-sportId").val();
        var sportId = $("#basic-icon-default-idSport").val();
        var sportName = $("#basic-icon-default-strSport").val();
        var sportFormat = $("#basic-icon-default-strFormat").val();
        var thumbUrl = $("#basic-icon-default-strSportThumb").val();
        var iconUrl = $("#basic-icon-default-strSportIconGreen").val();
        var description = $("#exampleFormControlTextarea1").val();

        $(".idSportError,.strSportError,.strFormatError,.strSportThumbError,.strSportIconGreenError").text('');
        $.ajax({
            type:'POST',
            url:'save-sport',
            data:{Id,sportId,sportName,sportFormat,thumbUrl,iconUrl,description,"_token": "{{ csrf_token() }}"},
            success:function(response){
                if(response.status == 1) {
                    var message = "Sport added successfully.";
                    var title = "Added!";
                    if (Id > 0) {
                        var title = "Updated!";
                        message = "Sport updated successfully.";
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
                    if(response.error.sportId) {
                        $(".idSportError").text(response.error.sportId);
                    }
                    if(response.error.sportName) {
                        $(".strSportError").text(response.error.sportName);
                    }
                    if(response.error.sportFormat) {
                        $(".strFormatError").text(response.error.sportFormat);
                    }
                    if(response.error.thumbUrl) {
                        $(".strSportThumbError").text(response.error.thumbUrl);
                    }
                    if(response.error.iconUrl) {
                        $(".strSportIconGreenError").text(response.error.iconUrl);
                    }
                }
            }
        });
    }
    function deleteSport(elem) {
        var sportId = $(elem).attr('data-id');
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
                    url: 'delete-sport',
                    data: {sportId, "_token": "{{ csrf_token() }}"},
                    success: function (response) {
                        if (response.status == 1) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Your sport has been deleted.',
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                }
                            }).then(({value}) => {
                                location.reload(true);
                            });
                        } else {
                            Swal.fire({
                                title: 'Cancelled',
                                text: 'Error while delete sport :)',
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
                    text: 'Your sport is safe :)',
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn btn-success'
                    }
                });
            }
        });
    }
</script>
