<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
				<div class="page-title-box">
                    <div class="float-start">
                        <ul class="nav nav-pills">
                            <li class="nav-item"> <button onclick="statusTab('extraHoursTable',0);" class=" btn waves-effect waves-light btn-outline-danger btn-pending active" style="outline:0px" data-toggle="tab" aria-expanded="false">Pending</button> </li>
                            <li class="nav-item"> <button onclick="statusTab('extraHoursTable',1);" class=" btn waves-effect waves-light btn-outline-success btn-approve" style="outline:0px" data-toggle="tab" aria-expanded="false">Approved</button> </li>
                        </ul>
                    </div>
					<div class="float-end">
                        <?php
                            $addParam = "{'modal_id' : 'bs-right-md-modal', 'call_function':'addExtraHours', 'form_id' : 'addExtraHours', 'title' : 'Add Extra Hours'}";
                        ?>
                        <button type="button" class="btn waves-effect waves-light btn-outline-dark permission-write float-right press-add-btn" onclick="modalAction(<?=$addParam?>);"><i class="fa fa-plus"></i> Add Extra Hours</button>
					</div>
				</div>
            </div>
		</div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id='extraHoursTable' class="table table-bordered ssTable ssTable-cf" data-url='/getDTRows'></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>   
    </div>
</div>
<div class="modal fade" id="xhrsApprovalModal" role="dialog" tabindex="-1" aria-labelledby="exampleModalLabel1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content animated slideDown">
            <div class="modal-header">
                <h4 class="modal-title">Extra Hours Approval</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn waves-effect waves-light btn-outline-secondary btn-close" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                <button type="button" class="btn waves-effect waves-light btn-outline-success btn-approveXHRS"><i class="fa fa-check"></i> Save</button>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('includes/footer'); ?>
