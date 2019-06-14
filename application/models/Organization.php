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
				$uuId = $dbData->id;
				$loginResponse['status']=1;
				$loginResponse['message']="login success";
				$loginResponse['user']=$this->getUser($email);
				$loginResponse['forms']=$this->getallform($uuId);	
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

	public function getallform($userid){
		$f=array();
		$this->db->select('*');
		$this->db->from('form');
		$this->db->where('uid',$userid);
		$query = $this->db->get();
		$rows = $query->result();
		foreach($rows as $fr){
			$feedbackCount = $this->getFormFeedBackCount($fr->formid);	
			$f[] = array('formid'=>$fr->formid,'feed_count'=>$feedbackCount,'formdata'=>$this->getForm($fr->formid));
		}	
		return $f;
	}
	
	public function getFormFeedBackCount($formId){
		$this->db->select('count(*) as feedback');
		$this->db->from('feedback_stream');
		$this->db->where('formid',$formId);
		$query = $this->db->get();
		$rows = $query->result();
		$row = $rows[0];
		return $row->feedback;
	}

	public function placefeed($feed){
		$placefeedStatus=array();
		$fid = $feed->formid;
		$feedStream = array();
		$feedStream['formid']=$fid;
		$this->db->insert('feedback_stream',$feedStream);
		$feedid = $formid = $this->db->insert_id();
		$feedStreamInfo=array();
		$feedStreamInfo['feedid']=$feedid;
		$feedStreamInfo['feeddata']=$feed->feeddata;
		$this->placefeedDetail($feedStreamInfo);	
		$placefeedStatus['status']=1;
		return $placefeedStatus;
	}
	
	public function placefeedDetail($feedStreamInfo){
		$feedid = $feedStreamInfo['feedid'];
		$feedData = $feedStreamInfo['feeddata'];
		foreach($feedData as $eachQstnFeed){
			unset($QstnFeed);
			$QstnFeed = array();
			$QstnFeed['fdid']=$feedid;
			$QstnFeed['qid']=$eachQstnFeed->qid;
			$QstnFeed['qtext']=$eachQstnFeed->qtext;
			$this->db->insert('feedback_stream_info',$QstnFeed);
		}		
	}

	public function getFormAllFeeds($formid){
		$formFeeds=array();
		$qid=array();
		$sql="SELECT * FROM form INNER JOIN question ON form.formid=question.formid WHERE form.formid='$formid'";
		$query = $this->db->query($sql);
		$result = $query->result();
		foreach($result as $qstn){
			$qid[]=array('qid'=>$qstn->qid,'qtext'=>$qstn->qtext);
		}
		
		foreach($qid as $q){
			unset($qtnFeed);
			$qtnFeed=array();
			$qid =$q['qid'];
			$sqlFeed = "SELECT * FROM feedback_stream_info WHERE qid='$qid' ORDER BY fdid DESC";
			$feedQuery = $this->db->query($sqlFeed);
			$feedResult = $feedQuery->result();
			foreach($feedResult as $fr){
				$qtnFeed[]=array('fdid'=>$fr->fdid,'qtext'=>$fr->qtext);
			}
			$formFeeds[]=array('qid'=>$q['qid'],'qtext'=>$q['qtext'],'feeds'=>$qtnFeed);
		}
		return $formFeeds;

	}

	
}

?>
