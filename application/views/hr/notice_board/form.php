<form enctype="multpart/form-data">
    <div class="col-md-12">
        <div class="row">
            <input type="hidden" name="id" value="<?= (!empty($dataRow->id)) ? $dataRow->id : ""; ?>" />
            
            <div class="col-md-12 form-group">
                <label for="title">Title</label>
                <input type="text" name="title" class="form-control req" value="<?= (!empty($dataRow->title)) ? $dataRow->title : "" ?>" />
            </div>

            <div class="col-md-12 form-group">
                <label for="description">Description</label>
                <textarea name="description" class="form-control req" rows="2"><?=(!empty($dataRow->description))?$dataRow->description:""?></textarea>
            </div>

            <div class="col-md-12 form-group">
                <label for="attachment">Attachment</label>
                <div data-repeater-list="repeater-group">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" name="attachment" id="customFile">
                        <label class="custom-file-label" for="customFile">Choose file</label>
                    </div>
                </div>
            </div>

            <div class="col-md-6 form-group">
                <label for="valid_from_date">Circular From</label>
                <input type="date" name="valid_from_date" class="form-control req" value="<?= (!empty($dataRow->valid_from_date)) ? $dataRow->valid_from_date : "" ?>" />
            </div>

            <div class="col-md-6 form-group">
                <label for="valid_to_date">Circular To</label>
                <input type="date" name="valid_to_date" class="form-control req" value="<?= (!empty($dataRow->valid_to_date)) ? $dataRow->valid_to_date : "" ?>" />
            </div>           
        </div>
    </div>
</form>
