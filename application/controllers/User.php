<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function __construct(){
		parent::__construct();
		$this->load->database();
	}


	public function index()
	{
		$this->load->view('welcome_message');
	}

	public function register(){
	$registerRequest=array('name'=>$this->input->post('name'),
				'email'=>$this->input->post('email'),
				'password'=>$this->input->post('password')
			);
	$this->load->model('organization');
	$response = $this->organization->register($registerRequest);
	echo json_encode($response);
	}
	
	public function login(){
		$loginRequest=array('email'=>$this->input->post('email'),'password'=>$this->input->post('password'));
		$this->load->model('organization');
		$response = $this->organization->login($loginRequest);
		echo json_encode($response);
	}
	
	public function createform(){
		$formRequest;
		$reqObj = file_get_contents('php://input');
		$inputData = json_decode($reqObj);
		$formRequest['id']=$inputData->id;
		$formRequest['questions']=$inputData->form_data;
		$this->load->model('organization');
		$formResponse = $this->organization->createform($formRequest);
		$response['status']=1;
		$response['forn_data']=$formResponse;
		echo json_encode($response);
			
	}
	
	public function providefeedback(){
		$feedPlaceResponse;
		$feedbackRequest;
		$reqObj = file_get_contents('php://input');
		$inputData = json_decode($reqObj);
		$this->load->model('organization');
		$feedPlaceResponse = $this->organization->placefeed($inputData);
		if($feedPlaceResponse['status']==1){
			$feedPlaceResponse['message']='feed placed';
		}else{
			$feedPlaceResponse['message']='error placing feed';
	
		}
		echo json_encode($feedPlaceResponse);
	}


	public function getformfeedback(){
		$formResponse=array();
		$formid = $this->input->post('formid');
		$this->load->model('organization');
		$feeds = $this->organization->getFormAllFeeds($formid);
		if(!empty($feeds)){
			$formResponse['status']=1;
			$formResponse['message']='feedback available!';
			$formResponse['feeds']=$feeds;
		}else{
			$formResponse['status']=0;
			$formResponse['message']='no feedback available!';
			$formResponse['feeds']=array();
		}
	
		echo json_encode($formResponse);
	}
	
}
