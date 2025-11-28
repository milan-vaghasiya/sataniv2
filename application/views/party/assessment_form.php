<form class="supplierAssessment" enctype="multipart/form-data">
    <div class="col-md-12">
        <div class="row">
            <input type="hidden" name="id" id="id" value="<?=(!empty($id))?$id:""?>">
            
            <div class="col-md-12 form-group">
                <label for="asses">Assessment</label>
                <div class="input-group">
                    <div class="custom-file" style="width:100%;">
                        <input type="file" class="form-control custom-file-input" name="assessment_file" id="assessment_file" />
                    </div>
                </div>
                <div class="error assessment_file"></div>
            </div>
        </div>
    </div>
</form>