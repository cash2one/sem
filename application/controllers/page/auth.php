<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 授权登录页面
*/
class Auth extends CI_Controller {

    private $super_id = '';
    private $user_id = '';
    private $timestamp = '';

    function __Construct()
    {
        parent::__construct();
        $this->load->library('service/hzuser_service');
        $this->load->library('smarty_service');
    }

	public function index() {

        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
        //初始化
        $check = $this->_init($_REQUEST);
        if($check['status'] == 'failed')
        {
		    $this->smarty_service->view('auth.tpl',$check);
            return FALSE;
        }
        //获取权限
        $user_info = $this->hzuser_service->user_info($this->user_id);
        if(empty($user_info))
        {
            $res['error_code'] = '2';
            $res['error_msg']= 'user not exists';
		    $this->smarty_service->view('auth.tpl',$res);
            return FALSE;
        }
        if(!isset($user_info['status_product']) || !in_array($user_info['status_product'], array('0')))
        {
            $res['error_code'] = '3';
            $res['error_msg']= 'user locked';
		    $this->smarty_service->view('auth.tpl',$res);
            return FALSE;
        }
        if(time() > strtotime($user_info['expiration']))
        {
            $res['error_code'] = '4';
            $res['error_msg']= 'user expiration';
		    $this->smarty_service->view('auth.tpl',$res);
            return FALSE;
        }
        $own_id = $user_info['sem_owner'];
        $user_access = $this->hzuser_service->user_access($this->user_id);
        if(empty($user_access))
        {
            $res['error_code'] = '9';
            $res['error_msg']= 'no permission';
		    $this->smarty_service->view('auth.tpl',$res);
            return FALSE;
        }
        //判断用户权限
        $flag = 0 ;
        foreach($user_access as $value)
        {
            if(in_array($value['access'],array('1','2')))
                $flag = 1;
        }
        if(!$flag)
        {
            $res['error_code'] = '9';
            $res['error_msg']= 'no read permission';
		    $this->smarty_service->view('auth.tpl',$res);
            return FALSE;
        }
        //赋session
        $this->session->set_userdata('super_id',$this->super_id);
        $this->session->set_userdata('userid',$this->user_id);

        $res['status'] = 'success';
		$this->smarty_service->view('auth.tpl',$res);
	}

    private function _init($data)
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
        if(empty($data['info'])) 
        {
            $res['error_code'] = '7';
            $res['error_msg'] = 'auth failed : info is null';
            return $res;
        }
        $this->load->library('des_service');
        $data['info'] = str_replace(" ","+",$data['info']);
        $decrypt_str = trim(Des_service::des_decrypt(URL_DECRYPT_KEY,$data['info']));
        
        $decrypt_arr = empty($decrypt_str) ? '' : json_decode($decrypt_str,TRUE);
        if(empty($decrypt_arr) || empty($decrypt_arr['super_id']) || empty($decrypt_arr['user_id']) || empty($decrypt_arr['timestamp']))
        {
            $res['error_code'] = '7';
            $res['error_msg'] = 'auth failed : info is null';
            return $res;
        }
        
        if(time() - $decrypt_arr['timestamp'] > AUTH_TIMEOUT)
        {
            $res['error_code'] = '8';
            $res['error_msg'] = 'auth failed : time out';
            return $res;
        }

        $this->super_id = $decrypt_arr['super_id'];
        $this->user_id = $decrypt_arr['user_id'];
        $this->timestamp = $decrypt_arr['timestamp'];

        $res['status'] = 'success';
        return $res;
    }
}
