<form id="updatePrcQty" >
    <input type="hidden"  name="log_type" id="log_type" value="1">
    <div class="row">
        <div class="col-md-6 form-group">
            <label for="qty">PRC</label>
            <select name="prc_id" id="prc_id" class="form-control select2" style="mix-width:10%;">
                <option value="">Select PRC</option>
                <?php
                if(!empty($prcList)){
                    foreach($prcList AS $row){
                        ?><option value="<?=$row->id?>"><?=$row->prc_number?></option><?php
                    }
                }
                ?>
            </select>
            <input type="hidden" name="id" id="id" value="" />
            <input type="hidden" name="log_date" id="log_date" value="<?=date("Y-m-d")?>" />        
        </div>
        <div class="col-md-6 form-group">
            <label for="qty">Quantity</label>
            <input type="text" id="qty" name="qty" class="form-control numericOnly req" />
        </div>
    </div>
</form>