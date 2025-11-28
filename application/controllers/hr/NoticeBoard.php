<?php
    class NoticeBoard extends MY_Controller{
        private $index = "hr/notice_board/index";
        private $form = "hr/notice_board/form";
    
        public function __construct(){
            parent::__construct();
            $this->isLoggedin();
            $this->data['headData']->pageTitle = "Notice Board";
            $this->data['headData']->controller = "hr/noticeBoard";
        }
        
        public function index(){
            $this->data['tableHeader'] = getHrDtHeader("noticeBoard");
            $this->load->view($this->index,$this->data);
        }

        public function getDTRows(){
            $data = $this->input->post(); 
            $result = $this->noticeBoard->getDTRows($data);
            $sendData = array();$i=1;
            foreach($result['data'] as $row): 
                $row->sr_no = $i++;         
                $sendData[] = getNoticeBoardData($row);
            endforeach;
            $result['data'] = $sendData;
            $this->printJson($result);
        }

        public function addNoticeBoard(){
            $this->load->view($this->form,$this->data);
        }

        public function save(){
            $data = $this->input->post();
            $errorMessage = array();		
            if(empty($data['description']))
                $errorMessage['description'] = "Description is required.";
			if(empty($data['title']))
                $errorMessage['title'] = "Title is required.";
			if(empty($data['valid_from_date']))
                $errorMessage['valid_from_date'] = "Circular From is required.";
			if(empty($data['valid_to_date']))
                $errorMessage['valid_to_date'] = "Circular To is required.";

            if(!empty($errorMessage)):
                $this->printJson(['status'=>0,'message'=>$errorMessage]);
            else:
				if ($_FILES['attachment']['name'] != null || !empty($_FILES['attachment']['name'])) :
					$this->load->library('upload');
					$_FILES['userfile']['name']     = $_FILES['attachment']['name'];
					$_FILES['userfile']['type']     = $_FILES['attachment']['type'];
					$_FILES['userfile']['tmp_name'] = $_FILES['attachment']['tmp_name'];
					$_FILES['userfile']['error']    = $_FILES['attachment']['error'];
					$_FILES['userfile']['size']     = $_FILES['attachment']['size'];
		
					$imagePath = realpath(APPPATH . '../assets/uploads/notice_board/');
					$config = ['file_name' => time() . "_img_" . $_FILES['userfile']['name'], 'allowed_types' => '*', 'max_size' => 10240, 'overwrite' => FALSE, 'upload_path'    => $imagePath];
		
					$this->upload->initialize($config);
					if (!$this->upload->do_upload()) :
						$errorMessage['attachment'] = $this->upload->display_errors();
						$this->printJson(["status" => 0, "message" => $errorMessage]);
					else :
						$uploadData = $this->upload->data();
						$data['attachment'] = $uploadData['file_name'];
					endif;
				else :
					unset($data['attachment']);
				endif;
			
                $data['created_by'] = $this->session->userdata('loginId');
                $this->printJson($this->noticeBoard->save($data));
            endif;
        }
    
        public function edit(){     
            $id = $this->input->post('id');
            $this->data['dataRow'] = $this->noticeBoard->getNoticeBoardDetail($id);
            $this->load->view($this->form,$this->data);
        }
    
        public function delete(){
            $id = $this->input->post('id');
            if(empty($id)):
                $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
            else:
                $this->printJson($this->noticeBoard->delete($id));
            endif;
        }
    }
?>