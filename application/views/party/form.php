<form>
    <div class="col-md-12">
        <div class="row">
            <input type="hidden" name="id" id="id" value="<?=(!empty($dataRow->id))?$dataRow->id:""; ?>" />

            <?php
                $party_category = (!empty($dataRow->party_category))?$dataRow->party_category:$party_category;
            ?>

            <div class="col-md-6 form-group">
                <label for="party_name">Company Name</label>
                <input type="text" name="party_name" id="party_name" class="form-control text-capitalize req" value="<?=(!empty($dataRow->party_name))?$dataRow->party_name:""; ?>" />
            </div>

			<div class="col-md-2 form-group hidden">
                <label for="party_category">Party Category</label>
                <select name="party_category" id="party_category" class="form-control select2 req">
                    <?php                        
                        foreach($this->partyCategory as $key => $name):
                            if($key <= 3):
                                $selected = (!empty($dataRow->party_category) && $dataRow->party_category == $key)?"selected":((!empty($party_category) && $party_category == $key)?"selected":"");
                                echo '<option value="'.$key.'" '.$selected.'>'.$name.'</option>';
                            endif;
                        endforeach;
                    ?>
				</select>
            </div>

            <div class="col-md-3 form-group">
                <label for="party_code">Party Code</label>
                <input type="text" name="party_code" id="party_code" class="form-control" value="<?= (!empty($dataRow->party_code)) ?$dataRow->party_code : $party_code; ?>" readonly/>
            </div>            

            <div class="col-md-3 form-group">
                <label for="sales_executive">Sales Executive</label>
                <select name="sales_executive" id="sales_executive" class="form-control select2" >
					<option value="">Sales Executive</option>
					<?php
						foreach($salesExecutives as $row):
							$selected = (!empty($dataRow->sales_executive) && $dataRow->sales_executive == $row->id)?"selected":"";
							echo '<option value="'.$row->id.'" '.$selected.'>'.$row->emp_name.'</option>';
						endforeach;
					?>
				</select>
            </div>

            <div class="col-md-3 form-group">
                <label for="contact_person">Contact Person</label>
                <input type="text" name="contact_person" id="contact_person" class="form-control text-capitalize" value="<?=(!empty($dataRow->contact_person))?$dataRow->contact_person:""?>" />
            </div>

            <div class="col-md-3 form-group">
                <label for="party_mobile">Contact No.</label>
                <input type="text" name="party_mobile" id="party_mobile" class="form-control numericOnly" value="<?=(!empty($dataRow->party_mobile))?$dataRow->party_mobile:""?>" />
            </div>
			
            <div class="col-md-3 form-group">
                <label for="party_email">Party Email</label>
                <input type="email" name="party_email" id="party_email" class="form-control" value="<?=(!empty($dataRow->party_email))?$dataRow->party_email:""; ?>" />
            </div>

            <div class="col-md-3 form-group">
                <label for="credit_days">Credit Days</label>
                <input type="text" name="credit_days" id="credit_days" class="form-control numericOnly" value="<?=(!empty($dataRow->credit_days))?$dataRow->credit_days:""?>" />
            </div>

            <div class="col-md-3 form-group">
                <label for="registration_type">Registration Type</label>
                <select name="registration_type" id="registration_type" class="form-control select2">
                    <?php
                        foreach($this->gstRegistrationTypes as $key=>$value):
                            $selected = (!empty($dataRow->registration_type) && $dataRow->registration_type == $key)?"selected":"";
                            echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
                        endforeach;
                    ?>
                </select>
            </div>
            
			<div class="col-md-3 form-group">
                <label for="gstin">Party GSTIN</label>
                <!--<span class="float-right">
                    <a class="text-primary font-bold" id="getGstinDetail" href="javascript:void(0)">Verify</a>
                </span>-->
                <input type="text" name="gstin" id="gstin" class="form-control text-uppercase req" value="<?=(!empty($dataRow->gstin))?$dataRow->gstin:""; ?>" />
            </div>	
            		
            <div class="<?=($party_category != 1)?"col-md-3":"col-md-2"?> form-group">
                <label for="pan_no">Party PAN</label>
                <input type="text" name="pan_no" id="pan_no" class="form-control text-uppercase" value="<?=(!empty($dataRow->pan_no))?$dataRow->pan_no:""?>" />
            </div>
			
            <div class="<?=($party_category != 1)?"col-md-3":"col-md-2"?> form-group">
                <label for="currency">Currency</label>
                <select name="currency" id="currency" class="form-control select2">
                    <option value="">Select Currency</option>
                    <?php $i=1; foreach($currencyData as $row):
                        $selected = (!empty($dataRow->currency) && $dataRow->currency == $row->currency)?"selected":"";
						if(empty($dataRow->currency) && $row->currency == "INR"){$selected = "selected";}
                    ?>
                        <option value="<?=$row->currency?>" <?=$selected?>><?=$row->currency?> [<?=$row->code2000?> - <?=$row->currency_name?>]</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 form-group <?=($party_category != 1)?"hidden":""?>">
                <label for="distance">Distance (Km)</label>
                <input type="text" name="distance" id="distance" class="form-control numericOnly" value="<?=(!empty($dataRow->distance))?floatVal($dataRow->distance):""?>">
            </div>
            
            <div class="col-md-3 form-group">
                <label for="country_id">Select Country</label>
                <select name="country_id" id="country_id" class="form-control country_list select2 req" data-state_id="state_id" data-selected_state_id="<?=(!empty($dataRow->state_id))?$dataRow->state_id:4030?>">
                    <option value="">Select Country</option>
                    <?php foreach($countryData as $row):
                        $selected = (!empty($dataRow->country_id) && $dataRow->country_id == $row->id)?"selected":((empty($dataRow) && $row->id == 101)?"selected":"");

                    ?>
                        <option value="<?=$row->id?>" <?=$selected?>><?=$row->name?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 form-group">
                <label for="state_id">Select State</label>
                <select name="state_id" id="state_id" class="form-control state_list select2 req" data-city_id="city_id" data-selected_city_id="<?=(!empty($dataRow->city_id))?$dataRow->city_id:""?>">
                    <option value="">Select State</option>
                </select>
            </div>
            
            <div class="col-md-3 form-group">
                <label for="city_id">Select City</label>
                <select name="city_id" id="city_id" class="form-control select2 req">
                    <option value="">Select City</option>
                </select>
            </div>

            <div class="col-md-3 form-group">
                <label for="party_pincode">Pincode</label>
                <input type="text" name="party_pincode" id="party_pincode" class="form-control req" value="<?=(!empty($dataRow->party_pincode))?$dataRow->party_pincode:""?>" />
            </div>

            <div class="col-md-12 form-group">
                <label for="party_address">Address</label>
                <textarea name="party_address" id="party_address" class="form-control req" rows="2"><?=(!empty($dataRow->party_address))?$dataRow->party_address:""?></textarea>
            </div>

            <div class="col-md-3 form-group">
                <label for="delivery_country_id">Delivery Country</label>
                <select name="delivery_country_id" id="delivery_country_id" class="form-control country_list select2" data-state_id="delivery_state_id" data-selected_state_id="<?=(!empty($dataRow->delivery_state_id))?$dataRow->delivery_state_id:""?>">
                    <option value="">Select Country</option>
                    <?php foreach($countryData as $row):
                        $selected = (!empty($dataRow->delivery_country_id) && $dataRow->delivery_country_id == $row->id)?"selected":"";
                    ?>
                        <option value="<?=$row->id?>" <?=$selected?>><?=$row->name?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 form-group">
                <label for="delivery_state_id">Delivery State</label>
                <select name="delivery_state_id" id="delivery_state_id" class="form-control state_list select2" data-city_id="delivery_city_id" data-selected_city_id="<?=(!empty($dataRow->delivery_city_id))?$dataRow->delivery_city_id:""?>">
                    <option value="">Select State</option>
                </select>
            </div>
            
            <div class="col-md-3 form-group">
                <label for="delivery_city_id">Select City</label>
                <select name="delivery_city_id" id="delivery_city_id" class="form-control select2">
                    <option value="">Select City</option>
                </select>
            </div>

            <div class="col-md-3 form-group">
                <label for="delivery_pincode">Delivery Pincode</label>
                <input type="text" name="delivery_pincode" id="delivery_pincode" class="form-control" value="<?=(!empty($dataRow->delivery_pincode))?$dataRow->delivery_pincode:""?>" />
            </div>  
            
            <div class="col-md-12 form-group">
                <label for="delivery_address">Delivery Address</label>
                <textarea name="delivery_address" id="delivery_address" class="form-control" rows="2"><?=(!empty($dataRow->delivery_address))?$dataRow->delivery_address:""?></textarea>
            </div>
        </div>        
    </div>
</form>
<script>
$(document).ready(function(){    
	//setTimeout(function(){ 
		$("#country_id").trigger('change');
		$("#delivery_country_id").trigger('change');
    //}, 1000);

    $(document).on('change','#party_category',function(){
        var party_category = $(this).val();
        $.ajax({
            url : base_url + 'parties/getPartyCode',
            type : 'post',
            data : {party_category:party_category},
            dataType: 'json'
        }).done(function(res){
            $("#party_code").val(res.party_code);
        });
    });

    $(document).on('click','#getGstinDetail',function(e){
        e.stopImmediatePropagation();
        var gstin = $("#gstin").val();

        $(".gstin").html("");
        if(gstin == ""){
            $(".gstin").html("Please enter gstin no.");
        }else{
            $.ajax({
                url : base_url + "ebill/getGstinDetail",
                type : 'POST',
                data : {gstin:gstin},
                dataType : 'json',
                success:function(response){
                    if(response.status != 1){
                        Swal.fire({ icon: 'error', title: response.message });
                    }else{
                        Swal.fire({ icon: 'success', title: response.message});

                        var gstDetail = response.data;
                        Swal.fire({
                            title: 'Confirm!',
                            text: "Are you sure want to update party details ?",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, Do it!',
                        }).then(function(result) {
                            if (result.isConfirmed){
                                
                                $("#party_name").val(gstDetail.party_name);
                                $("#party_address").val(gstDetail.party_address);
                                $("#party_pincode").val(gstDetail.party_pincode);
                                $("#country_id").val(gstDetail.country_id);
                                $("#country_id").attr("selected_state_id",gstDetail.state_id);
                                if(parseFloat(gstDetail.city_id) > 0){
                                    $("#state_id").data("selected_city_id",gstDetail.city_id);
                                }
                                $("#pan_no").val(gstDetail.pan_no);
                                //$("#gst_reg_date").val(gstDetail.gst_reg_date);

                                setTimeout(function(){ $("#country_id").trigger('change'); },500);
                            }
                        });
                    }
                }
            }); 
        }
    });
});
</script>