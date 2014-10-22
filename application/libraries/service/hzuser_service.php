<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hzuser_service {
    
    private $CI;
	
	public function __construct() {
	    $this->CI = & get_instance();
        $this->CI->load->model('enterprise_user_model');
        $this->CI->load->model('enterprise_sem_user_model');
        $this->CI->load->model('account_bind_model');
        $this->CI->load->model('user_info_model');
        $this->CI->load->model('package_model');
        $this->CI->load->model('autobid_model');
	}

	//参数校验
	public function init($data)
	{
        $res = array('status' => 'success','error_code'=>'','error_msg' =>'');
        $username = isset($data['username']) ? $data['username'] : "";
        $password = isset($data['password']) ? $data['password'] : "";
        if (empty($username) || empty($password)) {
            $res['status'] = 'failed';
            $res['error_code'] = '1';
            $res['message'] = 'username and password required';
            return $res;
        }
        return $res;
	}

    //判断用户是否存在
    public function is_user($username)
    {
        $user_info = $this->CI->enterprise_user_model->get_user_by_name($username);
        return empty($user_info[0]) ? FALSE : $user_info[0] ;
    }
    
    //判断用户名密码
    public function login($username,$password)
    {
        $user_info = $this->CI->enterprise_user_model->get_user_by_name($username);
        if(empty($user_info))  
        {
            return array(FALSE,'2','username not exists');
        }
        if(is_null($user_info[0]['status_product']))
        {
            return array(FALSE,'3','have no permission');
        }
        //是否禁用
        if($user_info[0]['status_product'] == '1')
        {
            return array(FALSE,'4','user is locked');
        }
        else if($user_info[0]['status_product'] == '3')
        {
            return array(FALSE,'6','user is auditing');
        }
        else if($user_info[0]['status_product'] == '4')
        {
            return array(FALSE,'7','user is reject');
        }
        else if($user_info[0]['status_product'] == '5')
        {
            return array(FALSE,'8','user is cancel');
        }
        $this->CI->load->helper('account_util');
        $md5_pwd = encode_password($username, $password);
        $old_pwd = encode_password_old($username, $password);

        if ($md5_pwd === $user_info[0]['password'] || $old_pwd === $user_info[0]['password'])
        {
            $data = array(
                        'last_login'=>date("Y-m-d H:i:s", $_SERVER["REQUEST_TIME"]),
                        'last_login_ip'=>$this->CI->input->ip_address(),
                    );
            //非首次登录且调查问卷状态为初始0，调查问卷状态置为准备展示中
            if(empty($user_info[0]['questionnaire']) && ($user_info[0]['last_login'] != DEFAULT_LAST_LOGIN))
            {
                $sem_data['questionnaire'] = '1';
                $this->CI->enterprise_sem_user_model->update_user_by_id($user_info[0]['userid'],$sem_data);               
            }
            $this->CI->enterprise_user_model->update_user_by_id($user_info[0]['userid'],$data);
            $this->CI->session->set_userdata('userid',$user_info[0]['userid']);
            return array(TRUE,'data'=>$user_info[0]);
        }
        return array(FALSE,'5','username or password error');
    }

    //判断密码是否正确
    public function pwd_correct($user_id,$password)
    {
        if(empty($user_id) || empty($password))
            return FALSE;
        
        $user_info = $this->CI->enterprise_user_model->get_info_by_id($user_id);
        if(empty($user_info))
            return FALSE;

        $real_password = $user_info[0]['password'];
        $username = $user_info[0]['username'];

        $this->CI->load->helper('account_util');
        $md5_pwd = encode_password($username, $password);
        $old_pwd = encode_password_old($username, $password);

        if ($md5_pwd === $real_password || $old_pwd === $real_password)
        {
            return TRUE;
        }
        
        return FALSE;
    }

    //获取用户绑定信息
    public function default_bind_info($userid)
    {
        if(empty($userid))
            return NULL;
        $bind_user = $this->CI->account_bind_model->get_bind_info($userid);
        $id = empty($bind_user[0]['baidu_id']) ? NULL : $bind_user[0]['baidu_id'];
        $res = $this->CI->user_info_model->user_info($id,'user_id,init_flag');
        if(empty($res[0]))
            return NULL;
        return $res[0];
    }

    //获取用户手机号码
    public function get_mobile($user_id)
    {
        if(empty($user_id))
            return FALSE;

        $query = $this->CI->enterprise_user_model->get_info_by_id($user_id,"mobile");
        if(isset($query[0]['mobile']))
            return $query[0]['mobile'];

        return FALSE;
    }

    //判断手机是否被验证
    public function is_mob_bind($user_id)
    {
        if(empty($user_id))
            return FALSE;

        $query = $this->CI->enterprise_user_model->get_info_by_id($user_id,"verify_mobile");
        if(isset($query[0]['verify_mobile']) && $query[0]['verify_mobile'] == 1)
            return TRUE;

        return FALSE;
    }

    /**
      * 设置验证码
      */
    public function SetCaptchaCode($len = "4") {
        $code = md5(rand());
        $code = preg_replace('/0|o|1|i|8|b|d|9|g/i', '', $code);
        $code = strtoupper(substr($code, 0, $len));
        return $code;
    }

    //获取用户信息
    public function user_info($user_id)
    {
        if(empty($user_id)) {return array();}
        $date = date('Y-m-d', time());
        
        $res = $this->CI->enterprise_user_model->get_user_by_id($user_id);
        $opened_package_info = $this->CI->enterprise_sem_user_model->opened_package_info($user_id, $date);
        $applying_package_info = $this->CI->enterprise_sem_user_model->applying_package_info($user_id);
        $max_package_info = $this->CI->package_model->max_package();

        $bind_user = $this->CI->account_bind_model->get_bind_info($user_id);
        $baidu_id = empty($bind_user[0]['baidu_id']) ? 0 : $bind_user[0]['baidu_id'];
        $info = $this->CI->autobid_model->autobid_keyword_amount($baidu_id);
        $autobid_keyword_amount 
            = isset($info[0]['count']) ? $info[0]['count'] : '0';
        if (empty($res[0])) {
            return array();
        }
        $res[0]['applying_package_info'] = array();
        $res[0]['opened_package_info'] = array();
        $res[0]['bid_keyword_num'] = 100;
        $res[0]['bidword_package_id'] = 0;
        $res[0]['autobid_keyword_amount'] = $autobid_keyword_amount;
        $res[0]['apply_status'] = 0;

        if ( ! empty($applying_package_info)) {
            $res[0]['applying_package_info'] = $applying_package_info[0];
            $res[0]['apply_status'] |= 1;
        }

        if ( ! empty($opened_package_info[0])) {
            $res[0]['bid_keyword_num']
                = $opened_package_info[0]['bid_keyword_num'];
            $res[0]['bidword_package_id'] = $opened_package_info[0]['id'];
            $res[0]['opened_package_info'] = $opened_package_info[0];
            if ($opened_package_info[0]['id'] == $max_package_info[0]['id']) {
                $res[0]['opened_package_info']['is_max'] = '1';
            }
            $res[0]['apply_status'] |= 2;
        }
        
        return $res[0];
    }

    
    //判断sem用户是否被绑定
    //0 ：未知错误
    //1 ：未被绑定
    //2 ：已被绑定
    //3 ：重复绑定
    public function is_bind($user_id,$baidu_id)
    {
        if(empty($user_id) || empty($baidu_id))
            return 0;

        $bind_info = $this->CI->account_bind_model->get_by_baidu($baidu_id);
        if(empty($bind_info))
            return 1;
        else if(count($bind_info) > 1)
            return 2;
        else
        {
            if($bind_info[0]['user_id'] == $user_id)
                return 3;
            else
                return 2;
        }
    }

    //获取hz用户绑定用户数
    public function bind_count($user_id)
    {
        if(empty($user_id))
            return 0;

        $res = $this->CI->account_bind_model->get_count($user_id);
        
        return empty($res[0]['count']) ? 0 : $res[0]['count'];
    }

    //获取用户数据
    public function get_account($token)
    {
        if(empty($token))
            return array(FALSE,'11','');

        $this->CI->load->library('baidu_service');
        $sem_res = $this->CI->baidu_service->baidu_user_info($token);
        $arr = json_decode($sem_res,TRUE);
        if(empty($arr))
            return array(FALSE,'10','service return error');
        if($arr['header']['error_code'] == '0')
            return array(TRUE,'',$arr['body']);

        return array(FALSE,$arr['header']['error_code'],$arr['header']['error_msg']);
    }

    //绑定用户，插入数据库
    public function insert_bind($insert_bind_data,$insert_user_data)
    {
        if(empty($insert_bind_data) || empty($insert_user_data))
            return FALSE;

        //先插入绑定表
        if(!$this->CI->account_bind_model->insert_bind($insert_bind_data))
            return FALSE;
        return $this->CI->user_info_model->add($insert_user_data);
    }

    //获取hz所属代理商信息
    public function get_owner($user_id)
    {
        if(empty($user_id))
            return array();
        
        $owner = $this->CI->enterprise_user_model->get_owner_info($user_id);

        return empty($owner[0]) ? array() : $owner[0];
    }

    //获取用户权限
    public function user_access($user_id)
    {
        if(empty($user_id))
            return array();
        
        $this->CI->load->model('user_permission_model');
        $col = "dic_module.id,name,access";
        $condition = array('user_id'=>$user_id);
        $res = $this->CI->user_permission_model->get($condition,$col);
        array_walk($res,create_function('&$v', '$v["access"]=empty($v["access"]) ? 0 : $v["access"];'));
        //如果为代理操作
        $super_id = $this->CI->session->userdata('super_id');
        if(!empty($super_id))
        {
            $user_info = $this->user_info($user_id);
            if($user_info['sem_owner'] != $super_id)
            {
                foreach($res as &$value)
                {
                    if($value['access'] > 1)
                        $value['access'] = 1;
                }
            }
        }
        return $res;
    }

    //修改用户权限
    public function update_access($update_data)
    {
        if(empty($update_data))
            return FALSE;
        
        $this->CI->load->model('user_permission_model');
        return $this->CI->user_permission_model->update_batch($update_data);
    }
}

?>
