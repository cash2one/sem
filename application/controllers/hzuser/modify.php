<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   修改账户信息
 *   
*/
class Modify extends CI_Controller {

    private $name = '';
    private $contact = '';
    private $address = '';
    private $email = '';
    
	function __Construct()
	{
		parent::__Construct();
        $this->load->model('enterprise_user_model');
        $this->load->library('service/hzuser_service');
	}

    public function index()
    {
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
            
        //修改信息
        $update_data = array(
                'name'=>$this->name,
                'contact'=>$this->contact,
                'address'=>$this->address,
                'email'=>$this->email
            );
        if(!$this->enterprise_user_model->update_user_by_id(auth_filter::current_userid(),$update_data))
        {
            $res['error_code'] = '9';
            $res['error_msg'] = 'db error';
            $this->output->set_output(json_encode($res));
            return ;
        }
        $res['status'] = 'success';
        $this->output->set_output(json_encode($res));
        return ;
    }

    private function _init($data)
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');

        $check = Auth_filter::api_check_userid();
        if(!isset($check[0]) || !$check[0])
        {
            $res['error_code'] = $check[1];
            $res['error_msg'] = $check[2];
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        if(empty($data['name']) || strlen($data['name']) > 255)
        {
            $res['error_code'] = '5';
            $res['error_msg'] = 'name invalid';
            $this->output->set_output(json_encode($res));
			return FALSE;
        }
        $this->name = $data['name'];
        if(empty($data['contact']) || strlen($data['contact']) > 255)
        {
            $res['error_code'] = '6';
            $res['error_msg'] = 'contact invalid';
            $this->output->set_output(json_encode($res));
			return FALSE;
        }
        $this->contact = $data['contact'];

        if(empty($data['address']) || strlen($data['address']) > 255)
        {
            $res['error_code'] = '7';
            $res['error_msg'] = 'address invalid';
            $this->output->set_output(json_encode($res));
			return FALSE;
        }
        $this->address = $data['address'];

        $pattern = '/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/';
        if(empty($data['email']) || !preg_match($pattern,$data['email']) || strlen($data['email']) > 255)
        {
            $res['error_code'] = '8';
            $res['error_msg'] = 'email invalid';
            $this->output->set_output(json_encode($res));
			return FALSE;
        }
        $this->email = $data['email'];
        return TRUE;
    }

}

/* End of file mpdify.php */
/* Location: ./application/controllers/hzuser/momdify.php */
