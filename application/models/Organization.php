<?php
class  Organization extends CI_Model{
	
	 public function __construct(){
                parent::__construct();
                $this->load->database();
        }
	

	public function register($data){
		$user= $this->getUser($data['email']);
		if(!is_array($user)){
			$this->db->insert('user',$data);
			http_response_code(201);
			return array('status'=>1,'message'=>'account creted');
		}else{
			http_response_code(422);		
			return array('status'=>0,'message'=>'account with this mail already exist');
		}
	
	}

	public function getUser($email){
		$user=null;
		$this->db->select('*');
		$this->db->from('user');	
		$this->db->where('email',$email);
		$query = $this->db->get();
		if(count($query->result())>0){
			$dbData = $query->result();
			$dbData= $dbData[0];
			$user=array('name'=>$dbData->name,'email'=>$dbData->email,'id'=>$dbData->id);
		}
		return $user;
	}
	
	public function login($data){
		$email = $data['email'];
		$loginResponse;
		$this->db->select('*');
		$this->db->from('user');	
		$this->db->where('email',$email);
		$query = $this->db->get();
		if(count($query->result())==1){
			$dbData = $query->result();
			$dbData= $dbData[0];
			if(!strcmp($dbData->password,$data['password'])){
				http_response_code(200);
				$loginResponse['status']=1;
				$loginResponse['message']="login success";
				$loginResponse['user']=$this->getUser($email);	
			}else{
				http_response_code(406);
				$loginResponse['status']=0;
				$loginResponse['message']="please provide correct password";
			}	
		}else{
				http_response_code(404);
				$loginResponse['status']=0;
				$loginResponse['message']="email not available";
		
		}		
		return $loginResponse;
	}
	
	public function createform($data){
		$createFormResponse;
		$id = $data['id'];
		$questions = $data['questions'];
		$formData = array();
		$formData['uid']=$id;
		$this->db->insert('form',$formData);
		$formid = $this->db->insert_id();
		$quest=array();
		foreach($questions as $q){	
			unset($quest);
			$quest['formid']= $formid;
			$quest['qtext']= $q;
			$this->db->insert('question',$quest);
		}	
		
		$questions = $this->getForm($formid);	
		$createFormResponse['formid']=$formid;
		$createFormResponse['question']=$questions;
		http_response_code(200);
		return $createFormResponse;
	
	}
	
	public function getForm($id){
		$this->db->select('*');
                $this->db->from('question');
                $this->db->where('formid',$id);
                $query = $this->db->get();
		$rows = $query->result();
		return $rows;	
	}
}

?>
