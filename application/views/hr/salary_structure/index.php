<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
    <div class="container-fluid bg-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
							<div class="col-md-4">
								<a href="<?=base_url("hr/salaryStructure")?>" class="btn btn-outline-primary waves-effect waves-light active">CTC Format</a>
								<a href="<?=base_url("hr/salaryStructure/heads")?>" class="btn btn-outline-primary waves-effect waves-light">Salary Heads</a>
							</div>
                            <div class="col-md-4 text-center">
                                <h4 class="card-title">CTC Format</h4>
                            </div>
                            <div class="col-md-4">
								<?php
									$addParam = "{'modal_id' : 'modal-lg', 'call_function':'addCtcFormat', 'form_id' : 'addCtcFormat', 'title' : 'Add CTC Format'}";
								?>
								<button type="button" class="btn waves-effect waves-light btn-outline-dark permission-write float-right press-add-btn" onclick="modalAction(<?=$addParam?>);"><i class="fa fa-plus"></i> Add CTC Format</button>
                            </div>                             
                        </div>                                         
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id='ctcFormatTable' class="table table-bordered ssTable bt-switch1" data-url="/getDTRows"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </div>
</div>
<?php $this->load->view('includes/footer'); ?>