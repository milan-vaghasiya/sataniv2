<?php
class NoticeBoardModel extends MasterModel{
    private $notice_board = "notice_board";

    public function getDTRows($data){
        $data['tableName'] = $this->notice_board;
        $data['customWhere'][] = "valid_from_date BETWEEN valid_from_date AND valid_to_date AND valid_to_date >= CURRENT_DATE()";
        $data['searchCol'][] = "title";
		$data['searchCol'][] = "description";
        $data['searchCol'][] = "valid_from_date";
        $data['searchCol'][] = "valid_to_date";
		$columns =array('','','description','attachment','valid_from_date','valid_to_date');
		if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
        return $this->pagingRows($data);
    }

    public function getNoticeBoardDetail($id){
        $data['where']['id'] = $id;
        $data['tableName'] = $this->notice_board;
        return $this->row($data);
    }

    public function save($data){         
        return $this->store($this->notice_board,$data,'Notice Board');         
    } 

    public function delete($id){
        return $this->trash($this->notice_board,['id'=>$id],'Notice Board');
    }
	
	public function getNoticeBoardData(){
        $data['tableName'] = $this->notice_board;
        $data['customWhere'][] = "valid_from_date BETWEEN valid_from_date AND valid_to_date AND valid_to_date >= CURRENT_DATE()";
        return $this->rows($data);
    }
}
?>