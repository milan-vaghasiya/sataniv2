<form>
    <div class="col-md-12">
        <div class="row">
            <input type="hidden" name="id" id="id" value="<?=(!empty($dataRow->id))?$dataRow->id:""; ?>" />
            <div class="col-md-12 form-group">
                <label for="interview_date">Date</label>
                <input type="datetime-local" name="interview_date" id="interview_date" class="form-control req" value="<?=(!empty($dataRow->interview_date))?$dataRow->interview_date:date("Y-m-d");?>" />
            </div>
        </div>
    </div>
</form>   
