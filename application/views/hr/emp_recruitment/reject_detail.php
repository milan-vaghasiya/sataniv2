<div class="col-md-12">
    <form>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <b>Joining Date :</b><?=(!empty($dataRow->joining_date))?formatDate($dataRow->joining_date):""?>
                    <hr style="width:100%">
                    <b>Remark :</b><?=(!empty($dataRow->remark))?$dataRow->remark:""?>
                </div>
            </div>
        </div>
    </form>
</div>