<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
	<div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
				<div class="page-title-box">
                    <div class="float-start">
					    <ul class="nav nav-pills">
                            <li class="nav-item"> <button onclick="statusTab('empRecruitmentTable',1);" class=" nav-tab btn waves-effect waves-light btn-outline-success active" style="outline:0px" data-bs-toggle="tab" aria-expanded="false">New</button> </li>
                            <li class="nav-item"> <button onclick="statusTab('empRecruitmentTable',2);" class="nav-tab btn waves-effect waves-light btn-outline-success" style="outline:0px" data-bs-toggle="tab" aria-expanded="false">Interview</button> </li>
                            <li class="nav-item"> <button onclick="statusTab('empRecruitmentTable',3);" class=" nav-tab btn waves-effect waves-light btn-outline-success" style="outline:0px" data-bs-toggle="tab" aria-expanded="false">Rejected</button> </li>
                            <li class="nav-item"> <button onclick="statusTab('empRecruitmentTable',4);" class="nav-tab btn waves-effect waves-light btn-outline-success" style="outline:0px" data-bs-toggle="tab" aria-expanded="false">Confirmed</button> </li>
                        </ul>
					</div>
					<div class="float-end">
                        <?php
                            $addParam = "{'modal_id' : 'bs-right-xl-modal', 'call_function':'addEmpRecruitment', 'form_id' : 'addEmpRecruitment', 'title' : 'New Recruitment'}";
                        ?>
                        <button type="button" class="btn waves-effect waves-light btn-outline-dark permission-write float-right press-add-btn" onclick="modalAction(<?=$addParam?>);"><i class="fa fa-plus"></i> New Recruitment</button>
					</div>
                    
				</div>
            </div>
		</div>
        <div class="row">
            <div class="col-12">
				<div class="col-12">
					<div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id='empRecruitmentTable' class="table table-bordered ssTable ssTable-cf" data-url='/getDTRows'></table>
                            </div>
                        </div>
					</div>
				</div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('includes/footer'); ?>
<script src="<?php echo base_url();?>assets/js/custom/employee.js?v=<?=time()?>"></script>

<script>
    $(document).ready(function(){
    
    	<?php if(!empty($printID)): ?>
    		$("#printModel").attr('action',base_url + controller + '/printExperienceCertificate');
    		$("#printsid").val(<?=$printID?>);
    		$("#print_dialog").modal();
    	<?php endif; ?>
    
    	$(document).on("click",".printCertificate",function(){
    		$("#printModel").attr('action',base_url + controller + '/printExperienceCertificate');
    		$("#id").val($(this).data('id'));
    		$("#print_dialog").modal();
    	});		

        $(document).on('click',".changeEmpRecruitment",function(){
            var id = $(this).data('id');
            var val = $(this).data('val');
            var msg=$(this).data('msg');
            $.confirm({
                title: 'Confirm!',
                content: 'Are you sure want to '+ msg +' this Employee Recruitment?',
                type: 'green',
                buttons: {   
                    ok: {
                        text: "ok!",
                        btnClass: 'btn waves-effect waves-light btn-outline-success',
                        keys: ['enter'],
                        action: function(){
                            $.ajax({
                                url: base_url + controller + '/changeEmpRecruitment',
                                data: {id:id,val:val,msg:msg},
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
                                        toastr.success(data.message, 'Success', { "showMethod": "slideDown", "hideMethod": "slideUp", "closeButton": true, positionClass: 'toastr toast-bottom-center', containerId: 'toast-bottom-center', "progressBar": true });
                                        window.location.reload();
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
