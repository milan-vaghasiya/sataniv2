<form>
    <div class="col-md-12">
        <div class="row">
			<div class="col-md-12 form-group">
				<label for="month">Month</label>
				<select name="month" id="month" class="form-control select2 req">
					<option value="">Select Month</option>
					<?php
						foreach($monthList as $row):
							echo '<option value="'.$row.'">'.date("F-Y",strtotime($row)).'</option>';
						endforeach;
					?>
				</select>
				<div class="error month"></div>
			</div>
            <div class="col-md-12 form-group">
                <label for="emp_salary">Import File</label>
                <span class="float-right"><a href="<?=base_url('hr/payroll/downloadSalarySheet')?>" target="_blanck" style="color: #557ef8;">Download Example File</a></span>
                <input type="file" name="emp_salary" id="emp_salary" class="form-control-file">
				<div class="error emp_salary"></div>
            </div>
        </div>
    </div>
</form>