<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
	<div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
				<div class="page-title-box">
                    <div class="float-start">
                        <ul class="nav nav-pills">
                            <li class="nav-item"> 
                                <button  data-status="0" class=" btn waves-effect waves-light btn-outline-success statusTb active tabFilter" style="outline:0px" data-toggle="tab" aria-expanded="false">Active</button> 
                            </li>
                            <li class="nav-item"> 
                                <button  data-status="1" class=" btn waves-effect waves-light btn-outline-danger statusTb tabFilter" style="outline:0px" data-toggle="tab" aria-expanded="false">Inactive</button> 
                            </li>
                        </ul>
                        
                    </div>
					<div class="float-end">
					    <div class="input-group">
					        <div class="input-group-append" style="width:60%">
        			            <select name="cm_id" id="cm_id" class="form-control single-select tabFilter" >
        						    <option value="0">All Unit</option>
        							<?php
        								foreach($companyList as $row):
        									echo '<option value="'.$row->id.'">'.$row->company_name.'</option>';
        								endforeach;
        							?>
        						</select>
					        </div>
					        <div class="input-group-append" style="width:40%">
					            <?php
                                    $addParam = "{'modal_id' : 'bs-right-xl-modal', 'call_function':'addEmployee', 'form_id' : 'addEmployee', 'title' : 'Add Employee'}";
                                ?>
                                <button type="button" class="btn waves-effect waves-light btn-outline-dark permission-write float-right press-add-btn" onclick="modalAction(<?=$addParam?>);"><i class="fa fa-plus"></i> Add Employee</button>
				            </div>
					    </div>
                       
					</div>
                    <h4 class="card-title text-center">Employee</h4>
				</div>
            </div>
		</div>
        <div class="row">
            <div class="col-12">
				<div class="col-12">
					<div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id='employeeTable' class="table table-bordered ssTable ssTable-cf" data-url="/getDTRows"></table>
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
        $("#cm_id").on("click",function(){ return false;});
        $(document).on('change click','.tabFilter', function() {
            var cm_id = $("#cm_id").val();
            var status = $(".statusTb.active").data('status');
           
            var param = status+'/'+cm_id;
            console.log(param);
            statusTab('employeeTable',param)
        });
    });
</script>


