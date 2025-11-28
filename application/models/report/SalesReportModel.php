<?php
class SalesReportModel extends MasterModel{

    public function getOrderMonitoringData($data){
        $queryData = array();
        $queryData['tableName'] = "trans_child";
        $queryData['select'] = "trans_main.trans_date,trans_main.trans_number,trans_main.party_name,trans_child.item_name,trans_child.qty,GROUP_CONCAT(DATE_FORMAT(inv_main.trans_date,'%d-%m-%Y') SEPARATOR ',<br>') as inv_date,GROUP_CONCAT(inv_main.trans_number SEPARATOR ',<br>') as inv_number,GROUP_CONCAT(CAST(inv_child.qty AS DECIMAL)  SEPARATOR ',<br>') as inv_qty,(CASE WHEN trans_child.cod_date IS NOT NULL THEN DATEDIFF(MAX(inv_main.trans_date), trans_child.cod_date) ELSE '' END) as deviation_days,(trans_child.qty - SUM(inv_child.qty)) as deviation_qty";

        $queryData['leftJoin']['trans_main'] = "trans_child.trans_main_id = trans_main.id";
        $queryData['leftJoin']['trans_child as inv_child'] = "trans_child.id = inv_child.ref_id";
        $queryData['leftJoin']['trans_main as inv_main'] = "inv_child.trans_main_id = inv_main.id";
        
        $queryData['where']['trans_main.entry_type'] = 20;
        $queryData['where']['trans_main.trans_date >='] = $data['from_date'];
        $queryData['where']['trans_main.trans_date <='] = $data['to_date'];
        if(!empty($data['party_id'])):
            $queryData['where']['trans_main.party_id'] = $data['party_id'];
        endif;

        $queryData['group_by'][] = "trans_child.id";
        $queryData['order_by']['trans_main.trans_date'] = "ASC";
        $queryData['order_by']['trans_main.trans_no'] = "ASC";

        $result = $this->rows($queryData);
        return $result;
    }

    public function getSalesAnalysisData($data){
        $queryData = array();
        if($data['report_type'] == 1):
            $queryData['tableName'] = "trans_main";
            $queryData['select'] = "party_name,SUM(taxable_amount) as taxable_amount,SUM(gst_amount) as gst_amount,SUM(net_amount) as net_amount";

            $queryData['where']['trans_date >='] = $data['from_date'];
            $queryData['where']['trans_date <='] = $data['to_date'];
            $queryData['where']['vou_name_s'] = "Sale";

            $queryData['group_by'][] = 'party_id';
            $queryData['order_by']['SUM(taxable_amount)'] = $data['order_by'];

            $result = $this->rows($queryData);
        else:
            $queryData['tableName'] = "trans_child";
            $queryData['select'] = "trans_child.item_name,trans_child.brand_name,SUM(trans_child.qty) as qty,SUM(trans_child.taxable_amount) as taxable_amount,ROUND((SUM(trans_child.taxable_amount) / SUM(trans_child.qty)),2) as price";

            $queryData['leftJoin']['trans_main'] = "trans_child.trans_main_id = trans_main.id";
            $queryData['where']['trans_main.trans_date >='] = $data['from_date'];
            $queryData['where']['trans_main.trans_date <='] = $data['to_date'];
            $queryData['where']['trans_main.vou_name_s'] = "Sale";

            $queryData['group_by'][] = 'trans_child.item_id,trans_child.brand_id';
            $queryData['order_by']['SUM(trans_child.taxable_amount)'] = $data['order_by'];

            $result = $this->rows($queryData);
        endif;
        
        return $result;
    }
}
?>