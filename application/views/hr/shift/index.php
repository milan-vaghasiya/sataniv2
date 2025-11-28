<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
	<div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="card-title">Shift</h4>
                            </div>
                            <div class="col-md-6">
                                <?php
                                    $addParam = "{'modal_id' : 'bs-right-md-modal', 'call_function':'addShift', 'form_id' : 'addShift', 'title' : 'Add Shift'}";
                                ?>
                                <button type="button" class="btn waves-effect waves-light btn-outline-primary float-right" onclick="modalAction(<?=$addParam?>);"><i class="fa fa-plus"></i> Add Shift</button>
                            </div>                             
                        </div>                                         
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id='shiftTable' class="table table-bordered ssTable" data-url='/getDTRows'></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </div>
</div>

<?php $this->load->view('includes/footer'); ?>