<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
    <div class="container-fluid bg-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                        <div class="col-md-4">
							<ul class="nav nav-pills">
								<li class="nav-item"> <button onclick="statusTab('incrementPolicyTable',0);" class=" btn waves-effect waves-light btn-outline-info active" style="outline:0px" data-toggle="tab" aria-expanded="false">Pending</button> </li>
								<li class="nav-item"> <button onclick="statusTab('incrementPolicyTable',1);" class=" btn waves-effect waves-light btn-outline-success" style="outline:0px" data-toggle="tab" aria-expanded="false">Completed</button> </li>
							</ul>
                            </div>
                            <div class="col-md-4">
                                <h4 class="card-title text-center">Increment Policy</h4>
                            </div>
                            <div class="col-md-4">
                                <a href="<?=base_url($headData->controller."/addIncrementPolicy")?>" class="btn waves-effect waves-light btn-outline-primary float-right permission-write"><i class="fa fa-plus"></i> Add Increment Policy</a>
                            </div>                             
                        </div>                                         
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id='incrementPolicyTable' class="table table-bordered ssTable bt-switch1" data-url="/getDTRows"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </div>
</div>


<?php $this->load->view('includes/footer'); ?>
<script>
$(document).ready(function(){
    $(document).on('click',".policyApply",function(){
        var id = $(this).data('id');
        var val = $(this).data('val');
        var msg= $(this).data('msg');
        $.confirm({
            title: 'Confirm!',
            content: 'Are you sure want to Apply this Policy?',
            type: 'red',
            buttons: {   
                ok: {
                    text: "ok!",
                    btnClass: 'btn waves-effect waves-light btn-outline-success',
                    keys: ['enter'],
                    action: function(){
                        
                        var send_data = {id:id,val:val,msg:msg};
                        $.ajax({
                            url: base_url + controller + '/policyApply',
                            data: send_data,
                            type: "POST",
                            dataType:"json",
                            success:function(data)
                            { 
                                if(data.status==0)
                                {
                                    toastr.error(data.message, 'Sorry...!', { "showMethod": "slideDown", "hideMethod": "slideUp", "closeButton": true, positionClass: 'toastr toast-bottom-center', containerId: 'toast-bottom-center', "progressBar": true });
                                }
                                else
                                {
                                    initTable(); 
                                    toastr.success(data.message, 'Success', { "showMethod": "slideDown", "hideMethod": "slideUp", "closeButton": true, positionClass: 'toastr toast-bottom-center', containerId: 'toast-bottom-center', "progressBar": true });
                                }
                            }
                        });
                    }
                },
                cancel: {
                    btnClass: 'btn waves-effect waves-light btn-outline-secondary',
                    action: function(){

                    }
                }
            }
        });
    });
});

</script>
