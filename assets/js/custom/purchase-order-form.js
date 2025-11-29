$(document).ready(function(){
	setPlaceHolder();
	$(".ledgerColumn").hide();
	$(".summary_desc").attr('style','width: 60%;');

    $(document).on('click', '.add-item', function () {
		$('#itemForm')[0].reset();
		$("#itemForm input:hidden").val('');
		$('#itemForm #row_index').val("");
        $("#itemForm .error").html();

		var party_id = $('#party_id').val();
		$(".party_id").html("");
		$("#itemForm #row_index").val("");
		if(party_id){
			$("#itemModel").modal('show');
			$(".btn-close-item").show();
			$(".btn-save-item").show();
			
			setTimeout(function(){ 
				$("#itemForm #item_id").focus();
				setPlaceHolder();initSelect2();
			},500);
		}else{ 
            $(".party_id").html("Party name is required."); 
			$("#itemModel").modal('hide'); 
        }
	});

    $(document).on('click', '.saveItem', function () {
        
		var fd = $('#itemForm').serializeArray();
		var formData = {};
		$.each(fd, function (i, v) {
			formData[v.name] = v.value;
		});
        $("#itemForm .error").html("");

        if (formData.item_id == "") {
			$(".item_id").html("Item Name is required.");
		}
        if (formData.qty == "" || parseFloat(formData.qty) == 0) {
            $(".qty").html("Qty is required.");
        }
        if (formData.price == "" || parseFloat(formData.price) == 0) {
            $(".price").html("Price is required.");
        }
        
        if(formData.schedule_type == 1){
            if (formData.delivery_date == "" || parseFloat(formData.delivery_date) == 0) {
                $(".delivery_date").html("Delivery Date is required.");
            }
    	}else{
    		if (formData.sch_label == "" || parseFloat(formData.sch_label) == 0) {
                $(".sch_label").html("Delivery On is required.");
            }
    	}
    
        var item_ids = $(".item_id").map(function () { return $(this).val(); }).get();
        if ($.inArray(formData.item_id, item_ids) >= 0 && formData.row_index == "") {
            $(".item_name").html("Item already added.");
        }
		
        var errorCount = $('#itemForm .error:not(:empty)').length;

		if (errorCount == 0) {
			formData.item_name = $('#item_id :selected').text();
			formData.unit_name = $('#unit_id :selected').text();

            var itemData = calculateItem(formData);

            AddRow(itemData);
            $('#itemForm')[0].reset();
            $("#itemForm input:hidden").val('')
            $('#itemForm #row_index').val("");
            initSelect2('itemModel');
            if ($(this).data('fn') == "save") {
                $("#itemForm #item_id").focus();
            } else if ($(this).data('fn') == "save_close") {
                $("#itemModel").modal('hide');
            }

        }
	});

    $(document).on('click', '.btn-item-form-close', function () {
		$('#itemForm')[0].reset();
		$("#itemForm input:hidden").val('')
		$('#itemForm #row_index').val("");
		$("#itemForm .error").html("");
		initSelect2('itemModel');
	});   

	$(document).on('change','#hsn_code',function(){
		$("#gst_per").val(($("#hsn_code :selected").data('gst_per') || 0));
		initSelect2();
	});

	$(document).on("change","#cm_id",function(){
		var cm_id = $(this).val();
		if(cm_id){			
            $.ajax({
				url: base_url + controller + '/nextPoNoByCmId',
				data:{cm_id:cm_id},
				type: "POST",
				dataType:"json",
            }).done(function(data){	console.log(data);					
				$("#trans_no").val("");	
				$("#trans_no").val(data.po_no);
			});                              
		}
	});
	
	$(document).on('change','#schedule_type',function(){
		var sch_type = $(this).val();
		if(sch_type == 1){
			$('.date_delivery').removeAttr('style');
			$('.manual_delivery').attr('style', 'display:none;');
		}else{
			$('.manual_delivery').removeAttr('style');
			$('.date_delivery').attr('style', 'display:none;');
		}
	});
});

function AddRow(data) {
    var tblName = "purchaseOrderItems";
	var data = calculateItem(data);

    //Remove blank line.
	$('table#'+tblName+' tr#noData').remove();

	//Get the reference of the Table's TBODY element.
	var tBody = $("#" + tblName + " > TBODY")[0];

	//Add Row.
	if (data.row_index != "") {
		var trRow = data.row_index;
		//$("tr").eq(trRow).remove();
		$("#" + tblName + " tbody tr:eq(" + trRow + ")").remove();
	}
	var ind = (data.row_index == "") ? -1 : data.row_index;
	row = tBody.insertRow(ind);

    //Add index cell
	var countRow = (data.row_index == "") ? ($('#' + tblName + ' tbody tr:last').index() + 1) : (parseInt(data.row_index) + 1);
	var cell = $(row.insertCell(-1));
	cell.html(countRow);
	cell.attr("style", "width:5%;");

    var idInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][id]", value: data.id });
    var itemIdInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][item_id]", class:"item_id", value: data.item_id });
    var formEnteryTypeInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][from_entry_type]", value: data.from_entry_type });
	var refIdInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][ref_id]", value: data.ref_id });
	var reqIdInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][req_id]", value: data.req_id });
    cell = $(row.insertCell(-1));
    cell.html(data.item_name);
    cell.append(idInput);
    cell.append(itemIdInput);
    cell.append(formEnteryTypeInput);
    cell.append(refIdInput);
    cell.append(reqIdInput);

    var hsnCodeInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][hsn_code]", value: data.hsn_code });
	cell = $(row.insertCell(-1));
	cell.html(data.hsn_code);
	cell.append(hsnCodeInput);
	
	var deliveryDateInput = $("<input/>",{type:"hidden",name:"itemData["+countRow+"][delivery_date]",value:data.delivery_date});
	var scllblInput = $("<input/>",{type:"hidden",name:"itemData["+countRow+"][sch_label]",value:data.sch_label});
	var schTypeInput = $("<input/>",{type:"hidden",name:"itemData["+countRow+"][schedule_type]",value:data.schedule_type});
	cell = $(row.insertCell(-1));
	if(data.schedule_type == 1){
		cell.html(data.delivery_date);
	}else{
		cell.html(data.sch_label);
	}
	cell.append(deliveryDateInput);
	cell.append(scllblInput);
	cell.append(schTypeInput);

    var qtyInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][qty]", class:"item_qty", value: data.qty });
	var qtyErrorDiv = $("<div></div>", { class: "error qty" + countRow });
	cell = $(row.insertCell(-1));
	cell.html(data.qty);
	cell.append(qtyInput);
	cell.append(qtyErrorDiv);

    var unitIdInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][unit_id]", value: data.unit_id });
	cell = $(row.insertCell(-1));
	cell.html(data.unit_name);
	cell.append(unitIdInput);

    var priceInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][price]", value: data.price});
	var priceErrorDiv = $("<div></div>", { class: "error price" + countRow });
	cell = $(row.insertCell(-1));
	cell.html(data.price);
	cell.append(priceInput);
	cell.append(priceErrorDiv);

    var discPerInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][disc_per]", value: data.disc_per});
	var discAmtInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][disc_amount]", value: data.disc_amount });
	cell = $(row.insertCell(-1));
	cell.html(data.disc_amount + '(' + data.disc_per + '%)');
	cell.append(discPerInput);
	cell.append(discAmtInput);

    var cgstPerInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][cgst_per]", value: data.cgst_per });
	var cgstAmtInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][cgst_amount]", class:'cgst_amount', value: data.cgst_amount });
	cell = $(row.insertCell(-1));
	cell.html(data.cgst_amount + '(' + data.cgst_per + '%)');
	cell.append(cgstPerInput);
	cell.append(cgstAmtInput);
	cell.attr("class", "cgstCol");

	var sgstPerInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][sgst_per]", value: data.sgst_per });
	var sgstAmtInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][sgst_amount]", class:"sgst_amount", value: data.sgst_amount });
	cell = $(row.insertCell(-1));
	cell.html(data.sgst_amount + '(' + data.sgst_per + '%)');
	cell.append(sgstPerInput);
	cell.append(sgstAmtInput);
	cell.attr("class", "sgstCol");

	var gstPerInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][gst_per]", class:"gst_per", value: data.gst_per });
	var igstPerInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][igst_per]", value: data.igst_per });
	var gstAmtInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][gst_amount]", class:"gst_amount", value: data.gst_amount });
	var igstAmtInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][igst_amount]", class:"igst_amount", value: data.igst_amount });
	cell = $(row.insertCell(-1));
	cell.html(data.igst_amount + '(' + data.igst_per + '%)');
	cell.append(gstPerInput);
	cell.append(igstPerInput);
	cell.append(gstAmtInput);
	cell.append(igstAmtInput);
	cell.attr("class", "igstCol");

    var amountInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][amount]", class:"amount", value: data.amount });
    var taxableAmountInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][taxable_amount]", class:"taxable_amount", value: data.taxable_amount });
	cell = $(row.insertCell(-1));
	cell.html(data.taxable_amount);
	cell.append(amountInput);
	cell.append(taxableAmountInput);
	cell.attr("class", "amountCol");

	var netAmtInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][net_amount]", value: data.net_amount });
	cell = $(row.insertCell(-1));
	cell.html(data.net_amount);
	cell.append(netAmtInput);
	cell.attr("class", "netAmtCol");

    var itemRemarkInput = $("<input/>", { type: "hidden", name: "itemData["+countRow+"][item_remark]", value: data.item_remark});
	cell = $(row.insertCell(-1));
	cell.html(data.item_remark);
	cell.append(itemRemarkInput);

    //Add Button cell.
	cell = $(row.insertCell(-1));
	var btnRemove = $('<button><i class="mdi mdi-trash-can-outline"></i></button>');
	btnRemove.attr("type", "button");
	btnRemove.attr("onclick", "Remove(this);");
	btnRemove.attr("style", "margin-left:4px;");
	btnRemove.attr("class", "btn btn-sm btn-outline-danger waves-effect waves-light");

	var btnEdit = $('<button><i class="mdi mdi-square-edit-outline"></i></button>');
	btnEdit.attr("type", "button");
	btnEdit.attr("onclick", "Edit(" + JSON.stringify(data) + ",this);");
	btnEdit.attr("class", "btn btn-sm btn-outline-warning waves-effect waves-light");
    
    // if (parseFloat(data.dispatch_qty) == 0) {
    	cell.append(btnEdit);
    	cell.append(btnRemove);
    	cell.attr("class", "text-center");
    	cell.attr("style", "width:10%;");
    // }

    claculateColumn();
}

function Edit(data, button) {
	var row_index = $(button).closest("tr").index();
	$("#itemModel").modal('show');
	$(".btn-close-item").hide();
	$(".btn-save-item").hide();
	$.each(data, function (key, value) {
		$("#itemForm #" + key).val(value);
	});
	$("#itemForm #schedule_type").trigger('change');
	initSelect2('itemModel');
	$("#itemForm #row_index").val(row_index);
}

function Remove(button) {
    var tableId = "purchaseOrderItems";
	//Determine the reference of the Row using the Button.
	var row = $(button).closest("TR");
	var table = $("#"+tableId)[0];
	table.deleteRow(row[0].rowIndex);
	$('#'+tableId+' tbody tr td:nth-child(1)').each(function (idx, ele) {
		ele.textContent = idx + 1;
	});
	var countTR = $('#'+tableId+' tbody tr:last').index() + 1;
	if (countTR == 0) {
		$("#tempItem").html('<tr id="noData"><td colspan="14" align="center">No data available in table</td></tr>');
	}

	claculateColumn();
}

function resPartyDetail(response = ""){
    var html = '<option value="">Select GST No.</option>';
    if(response != ""){
        var partyDetail = response.data.partyDetail;
		$("#party_state_code").val(partyDetail.state_code);
        $("#master_t_col_3").val(partyDetail.delivery_address);
        $("#master_t_col_4").val(partyDetail.delivery_pincode);
		if(partyDetail.gstin != ""){
			html += '<option value="'+partyDetail.gstin+'" selected>'+partyDetail.gstin+'</option>';
		}
    }else{
		$("#party_state_code").val("");
        $("#master_t_col_3").val("");
        $("#master_t_col_4").val("");
    }
    $("#gstin").html(html);
	initSelect2('itemModel');
	gstin();
}

function resItemDetail(response = ""){
    if(response != ""){
        var itemDetail = response.data.itemDetail;
        // $("#itemForm #item_id").val(itemDetail.item_name);
        // $("#itemForm #unit_id").val(itemDetail.unit_name);
        $("#itemForm #unit_id").val(itemDetail.unit_id);
        $("#itemForm #disc_per").val(itemDetail.defualt_disc);
        $("#itemForm #price").val(itemDetail.price);
        $("#itemForm #hsn_code").val(itemDetail.hsn_code);
        $("#itemForm #gst_per").val(parseFloat(itemDetail.gst_per).toFixed(0));
    }else{
        $("#itemForm #unit_id").val("");
		$("#itemForm #disc_per").val("");
        $("#itemForm #price").val("");
        $("#itemForm #hsn_code").val("");
        $("#itemForm #gst_per").val(0);
    }
	initSelect2('itemModel');
}

function resSaveOrder(data,formId){
    if(data.status==1){
        $('#'+formId)[0].reset();
		Swal.fire({ icon: 'success', title: data.message});
        window.location = base_url + controller;
    }else{
        if(typeof data.message === "object"){
            $(".error").html("");
            $.each( data.message, function( key, value ) {$("."+key).html(value);});
        }else{
			Swal.fire({ icon: 'error', title: data.message });
        }			
    }	
}