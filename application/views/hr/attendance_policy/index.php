<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
	<div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="card-title">Attendance Policy</h4>
                            </div>
                            <div class="col-md-6">
                                <?php
                                    $attParam = "{'modal_id' : 'bs-right-md-modal', 'call_function':'addAttendancePolicy', 'form_id' : 'addAttendancePolicy', 'title' : 'Add Attendance Policy'}";
                                    $canteenParam = "{'modal_id' : 'bs-right-md-modal', 'call_function':'addEmpCharges', 'form_id' : 'addEmpCharges', 'title' : 'Canteen Charge','fnsave':'saveEmpCharges'}";
                                ?>
                                <button type="button" class="btn waves-effect waves-light btn-outline-primary float-right" onclick="modalAction(<?=$attParam?>);"><i class="fa fa-plus" ></i> Add Attendance Policy</button>

                                <button type="button" class="btn waves-effect waves-light btn-outline-primary float-right" onclick="modalAction(<?=$canteenParam?>);"><i class="fa fa-plus"></i> Canteen Charge</button>
                            </div>                             
                        </div>                                         
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id='attendancePolicyTable' class="table table-bordered ssTable" data-url='/getDTRows'></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </div>
</div>
<?php $this->load->view('includes/footer'); ?>