<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
    <div class="container-fluid bg-container" id="storeBoard">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <div class="float-start">
                        <ul class="nav nav-pills">							
							<li class="nav-item" role="presentation">
								<a class="btn btn-outline-info stageFilter <?=($status == 1) ? "active" : ""?>" data-postdata='{"status":"pending"}' data-bs-toggle="tab" href="javascript:void(0)" role="tab" datatip="Pending" flow="down" > Pending</a>
							</li>
							<li class="nav-item" role="presentation">
								<a class="btn btn-outline-info stageFilter <?=($status == 2) ? "active" : ""?>" data-postdata='{"status":"closed"}' data-bs-toggle="tab" href="javascript:void(0)" role="tab" datatip="Pending" flow="down" > Close</a>
							</li>
							<li class="nav-item" role="presentation">
								<a href="<?=base_url($headData->controller."/returnIndex/3")?>" class="btn waves-effect waves-light btn-outline-info mr-1 <?=($status == 2) ? "active" : "" ?>"> Returnable </a>
							</li>
                        </ul>
                    </div>
                    <div class="float-end">
                        <?php
                            $addParam = "{'modal_id' : 'bs-right-lg-modal', 'call_function':'addRequisition', 'form_id' : 'addRequisition', 'title' : 'Add Requisition','js_store_fn':'customStore','res_function':'loadDesk','form_close':'YES'}";
                        ?>
                        <button type="button" class="btn waves-effect waves-light btn-outline-dark permission-write float-right press-add-btn" onclick="modalAction(<?=$addParam?>);"><i class="fa fa-plus"></i> Add Requisition</button>
					</div>
                    
                </div>
            </div>
        </div> 
		<div class="card">
			<div class="card-body">
				<div class="row">
					<div class="col-md-12">
						<input type="text" id="cd-search" name="cd-search" class="form-control quicksearch float-right" placeholder="Search Here..." >       
					</div>
					<div class="col-md-12">
						<div class="table-responsive">
                            <table id="requisitionTable" class="table table-bordered">
								<thead class="thead-info">
									<tr>
										<th>Action</th>
										<th>#</th>
										<th>Req. No.</th>
										<th>Req. Date</th>
										<th>Item Name</th>
										<th>Req. Qty.</th>
										<th>Issue Qty.</th>
										<th>Batch/PRC No.</th>
									</tr>
								</thead>
								<tbody class="storeList"></tbody>
							</table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="next_page" value="0" /><a href="#" class="next_page" type="button" data-next_page="0" ></a>
<?php $this->load->view('includes/footer'); ?>
<script>
$(document).ready(function(){

	var reqListPageLimit = 15;
    setTimeout(function(){ loadDesk(); }, 50);
    
	$(document).on('click','.stageFilter',function(){
        var postdata = $(this).data('postdata') || {};
		$('#next_page').val('0');
		postdata.start = 0;
		postdata.length = parseFloat(reqListPageLimit);
		postdata.page = 0;
		loadHtmlData({'fnget':'getRequisitionList','rescls':'storeList','postdata':postdata});
	});
	
	$('.quicksearch').keyup(delay(function (e) {
		e.preventDefault();
		$('#next_page').val('0');
		var postdata = $('.stageFilter.active').data('postdata') || {};
		delete postdata.page;delete postdata.start;delete postdata.length;
		postdata.limit = parseFloat(reqListPageLimit);
		postdata.skey = $(this).val();
		loadHtmlData({'fnget':'getRequisitionList','rescls':'storeList','postdata':postdata});
	}));
	
	const scrollEle = $('#sopBoard .simplebar-content-wrapper');
	var ScrollDebounce = true;
	$(scrollEle).scroll(function() {
		if($(this).scrollTop() + $(this).innerHeight() >= ($(this)[0].scrollHeight - 10)) {
			if(ScrollDebounce){
				ScrollDebounce = false;
				var postdata = $('.stageFilter.active').data('postdata') || {};
    			var np = parseFloat($('#next_page').val()) || 0;
    			postdata.start = np * parseFloat(reqListPageLimit);
    			postdata.length = reqListPageLimit;
    			postdata.page = np;
    			loadHtmlData({'fnget':'getRequisitionList','rescls':'storeList','postdata':postdata,'scroll_type':1});
				setTimeout(function () { ScrollDebounce = true; }, 500);		
			}
		}
	});
});

/***** GET DYNAMIC DATA *****/
function loadHtmlData(data){
	var postData = data.postdata || {};
	var fnget = data.fnget || "";
	var controllerName = data.controller || controller;
	var rescls = data.rescls || "dynamicData";
	var scrollType = data.scroll_type || "";
	
	$.ajax({
		url: base_url + controllerName + '/' + fnget,
		data:postData,
		type: "POST",
		dataType:"json",
		global:false,
	}).done(function(res){
		$("#next_page").val(res.next_page);
		if(!scrollType){$("."+rescls).html(res.storeList);}else{$("."+rescls).append(res.storeList);}
		loading = true;
	});
}

function delay(callback, ms=500) {
	var timer = 0;
	return function() {
		var context = this, args = arguments;
		clearTimeout(timer);
		timer = setTimeout(function () { callback.apply(context, args); }, ms || 0);
	};
}

function loadDesk(){
	$(".stageFilter.active").trigger("click");
}
</script>