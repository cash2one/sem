<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   删除白名单
 *   
*/
class Del_whitelist extends CI_Controller {

    private $sem_user = '';
    private $white_list = array();

	function __Construct()
	{
		parent::__Construct();
        $this->load->library('service/user_service');
	}

    public function index()
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');
        //初始化
        if(!$this->_init($_REQUEST))
        {
            return FALSE;
        }
        //删除
        $del_res = $this->user_service->del_whitelist($this->sem_user,$this->white_list);
        if(!$del_res)
        {
            $res['error_code'] = '9';
            $res['error_msg'] = "db error";
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $res['status'] = 'success';
        $this->output->set_output(json_encode($res));
        return ;
    }

    private function _init($data)
    {
        $res = array('status'=>'failed','error_code'=>'','error_msg'=>'');

        if(empty($data['user_id']))
        {
            $res['error_code'] = '7';
            $res['error_msg'] = 'user_id invalid';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $this->sem_user = $data['user_id'];
        $check = Auth_filter::api_check_userid($this->sem_user);
        if(!isset($check[0]) || !$check[0])
        {
            $res['error_code'] = $check[1];
            $res['error_msg'] = $check[2];
            $this->output->set_output(json_encode($res));
            return FALSE;
        }

        //校验white_list
        if(empty($data['domains']))
        {
            $res['error_code'] = '8';
            $res['error_msg'] = 'domains is null';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $domain_arr = explode(',',$data['domains']);
        //去重
        $domain_arr = array_unique($domain_arr);
        if(count($domain_arr) > 5 )
        {
            $res['error_code'] = '8';
            $res['error_msg'] = 'domains input greater than 5';
            $this->output->set_output(json_encode($res));
            return FALSE;
        }
        $white = array();
        foreach($domain_arr as $value)
        {
            $this->load->helper('url');
            if(!filter_var(prep_url($value), FILTER_VALIDATE_URL))
            {
                $res['error_code'] = '8';
                $res['error_msg'] = "domain:{$value} invalid";
                $this->output->set_output(json_encode($res));
                return FALSE;
            }
            $white[] = rtrim(ltrim($value, 'http://'), '/');
        }
        $this->white_list = $white;
        return TRUE;
    }
}

/* End of file del_whitelist.php */
/* Location: ./application/controllers/user/del_whitelist.php */
