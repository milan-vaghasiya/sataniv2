<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
    <div class="container-fluid bg-container">
        <div class="row">
            <div class="col-12">
			    <div class="page-title-box">
					<div class="row">
                            <div class="col-md-4">
                                <h4 class="card-title pageHeader"><?=$pageHeader?></h4>
                            </div>        
                        </div>                                         
                    </div>
                    <div class="card">
                        <div class="card-body reportDiv" style="min-height:75vh">
                            <div class="table-responsive">
                                <table id='reportTable' class="table table-bordered">
                                    <thead class="thead-info" id="theadData">
                                        <tr class="text-center">
                                            <th>#</th>
                                            <th>Size</th>
                                            <?php
                                                foreach ($materialGrade as $mgRow) {
                                                    $mGrade = $mgRow->material_grade;
                                                    echo '<th>'.$mGrade.'</th>';
                                                }
                                            ?>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyData"><?=$tableBody?></tbody>
                                </table>
                            </div>
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
	reportTable();
    $(document).on('click','.loaddata',function(e){
		$(".error").html("");
		var valid = 1;
		var party_id = $('#party_id').val();
		var from_date = $('#from_date').val();
		var to_date = $('#to_date').val();
		if($("#from_date").val() == ""){$(".fromDate").html("From Date is required.");valid=0;}
		if($("#to_date").val() == ""){$(".toDate").html("To Date is required.");valid=0;}
		if($("#to_date").val() < $("#from_date").val()){$(".toDate").html("Invalid Date.");valid=0;}
		if(valid)
		{
            $.ajax({
                url: base_url + controller + '/getGrnTracking',
                data: {party_id:party_id,from_date:from_date, to_date:to_date},
				type: "POST",
				dataType:'json',
				success:function(data){
                    $("#reportTable").dataTable().fnDestroy();
					$("#tbodyData").html(data.tbody);
					reportTable();
                }
            });
        }
    });   
});

</script>