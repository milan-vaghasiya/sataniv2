<form autocomplete="off">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-6">
                <label for="party_name">Party Name : <span id="party_name"></span></label>
            </div>
            <div class="col-md-3">
                <label for="enquiry_no">Enquiry No. : <span id="enquiry_no"></span></label>
            </div>
            <div class="col-md-3">
                <label for="enquiry_date">Enquiry Date : <span id="enquiry_date"></span></label>
            </div>
        
        </div>
        <input type="hidden" name="enq_id" id="enq_id" value="" />
        <hr>
        <div class="error item_name_error"></div>
        <div class="table-responsive">
            <table class="table table-bordered align-items-center">
                <thead class="thead-info">
                    <tr>
                        <th style="width:50px" class="text-center">#</th>
                        <th style="width:400px">Item Name</th>
                        <th style="width:100px">Qty</th>
                        <th style="width:100px">Feasible</th>
                        <th style="width:100px">Price</th>
                        <th style="width:150px">Quotation No</th>
                        <th style="width:100px">Quotation Date</th>
                        <th style="width:300px">Remark</th>
                    </tr>
                </thead>
                <tbody id="enquiryData">
                    <?php if(!empty($enquiryItems)): echo $enquiryItems; else:?>
                    <tr><td colspan="5" class="text-center">No data available in table</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>