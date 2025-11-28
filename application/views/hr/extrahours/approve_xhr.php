<form>
    <div class="col-md-12">
        <div class="row">
            <div class="table-responsive">
                <input type="hidden" id="id" name="id" value="<?=$xData->id?>">
                <table class="table jpExcelTable"> 
                    <?php
                    $tsign = ($xData->xtype == 1) ? "+ " : "- ";
                    echo '<tr><th colspan="2">'.$xData->emp_name.'</th></tr>';
                    echo '<tr><th style="width:25%">Attendance Date</th><td>'.formatDate($xData->attendance_date).'</td></tr>';
                    echo '<tr><th>Extra Time</th><td>'.$tsign.formatSeconds((($xData->ex_hours * 3600) + ($xData->ex_mins * 60))).'</td></tr>';
                    echo '<tr><th>Created By</th><td>'.$xData->createdBy.'</td></tr>';
                    echo '<tr><th>Created Time</th><td>'.date('d-m-Y H:i:s',strtotime($xData->created_at)).'</td></tr>';
                    echo '<tr><td colspan="2"><b>Remark:</b><br>'.$xData->remark.'</td></tr>';
                    ?>
                </table>
            </div>
        </div>
    </div>
</form>