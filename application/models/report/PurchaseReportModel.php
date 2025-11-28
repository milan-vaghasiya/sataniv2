<?php 
class PurchaseReportModel extends MasterModel
{
    private $po_master = "po_master";
    private $po_trans = "po_trans";
    private $grn_master = "grn_master";
    private $grn_trans = "grn_trans";
	private $purchase_enquiry = "purchase_enquiry";
    private $purchase_quotation = "purchase_quotation";

    /* Purchase Monitoring Report */
    public function getPurchaseOrderMonitoring($data){
        $queryData = array();
		$queryData['tableName'] = $this->po_trans;
		$queryData['select'] = 'po_trans.*,po_trans.trans_main_id,po_master.trans_date,item_master.item_name,party_master.party_name,po_master.trans_number,po_master.remark';
        $queryData['join']['po_master'] = "po_master.id = po_trans.trans_main_id";
        $queryData['leftJoin']['item_master'] = "item_master.id = po_trans.item_id";
		$queryData['leftJoin']['party_master'] = 'party_master.id = po_master.party_id';
		if(!empty($data['item_type'])){
            $queryData['where']['item_master.item_type'] = $data['item_type'];
        }
		if(!empty($data['party_id'])){
            $queryData['where']['po_master.party_id'] = $data['party_id'];
        }
        $queryData['customWhere'][] = "po_master.trans_date BETWEEN '".$data['from_date']."' AND '".$data['to_date']."'";
		$queryData['order_by']['po_master.trans_date'] = 'ASC';
		$result = $this->rows($queryData);
        return $result;
    }

    public function getPurchaseReceipt($data){
        $queryData = array();
		$queryData['tableName'] = $this->grn_trans;
		$queryData['select'] = 'grn_master.trans_date,grn_master.trans_no,grn_master.trans_prefix,grn_master.trans_number,grn_master.inv_date,grn_master.inv_no,grn_trans.qty';
		$queryData['leftJoin']['grn_master'] = 'grn_master.id = grn_trans.mir_id';
		$queryData['where']['grn_trans.item_id'] = $data['item_id'];
		$queryData['where']['grn_trans.po_id'] = $data['po_id'];
		$queryData['where']['grn_trans.po_trans_id'] = $data['po_trans_id'];
		$queryData['order_by']['grn_master.trans_date'] = 'ASC';
		$result = $this->rows($queryData);
		return $result;
    }

    /* Purchase Inward Report */
    public function getPurchaseInward($data){
        $queryData = array();
		$queryData['tableName'] = $this->grn_trans;
		$queryData['select'] = 'grn_master.trans_date,grn_master.trans_number,grn_master.inv_no,grn_trans.qty,party_master.party_name,item_master.item_name,po_master.trans_number as po_number,po_master.trans_date as po_date,grn_trans.price';
		$queryData['leftJoin']['grn_master'] = 'grn_master.id = grn_trans.mir_id';
        $queryData['leftJoin']['item_master'] = 'item_master.id = grn_trans.item_id';
		$queryData['leftJoin']['party_master'] = 'party_master.id = grn_master.party_id';
		$queryData['leftJoin']['po_master'] = 'po_master.id = grn_trans.po_id';
		$queryData['leftJoin']['po_trans'] = 'po_trans.id = grn_trans.po_trans_id';
        if(!empty($data['item_type'])){
            $queryData['where']['item_master.item_type'] = $data['item_type'];
        }
        $queryData['customWhere'][] = "grn_master.trans_date BETWEEN '".$data['from_date']."' AND '".$data['to_date']."'";
		$result = $this->rows($queryData);
		return $result;
    }

    /* Supplier Wise Item Report */
    public function getSupplierWiseItem($data){
        $queryData = array();
		$queryData['tableName'] = $this->grn_trans;
		$queryData['select'] = 'grn_master.party_id,grn_trans.item_id,party_master.party_name,item_master.item_name,item_master.item_code';
		$queryData['leftJoin']['grn_master'] = 'grn_master.id = grn_trans.mir_id';
		$queryData['leftJoin']['party_master'] = 'party_master.id = grn_master.party_id';
		$queryData['leftJoin']['item_master'] = 'item_master.id = grn_trans.item_id';
		if(!empty($data['item_id'])){$queryData['where']['grn_trans.item_id'] = $data['item_id'];}
		if(!empty($data['party_id'])){$queryData['where']['grn_master.party_id'] = $data['party_id'];}
        $queryData['group_by'][] = 'grn_master.party_id';
        $queryData['group_by'][] = 'grn_trans.item_id';
		$result = $this->rows($queryData);
        return $result;
    }
}
?>