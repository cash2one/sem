<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common_service {
    
    private $CI;
	
	public function __construct() {
	    $this->CI = & get_instance();
        $this->CI->load->model('agent_token_model');
        $this->CI->load->library('service/user_service');
	}


    //获取token信息
    public function get_token_info($user_id,$sem_id)
    {
        //废弃
        return array('username'=>'','password'=>'','token'=>'','target'=>'');

        if(empty($user_id))
            return array();

        $user_info = $this->CI->user_service->user_info($sem_id);
        if(empty($user_info))
            return array();

        //获取token信息
        $res = $this->CI->agent_token_model->get_token($user_id);
        if(empty($res[0]))
            return array();

        if($res[0]['token_type'] == '1')
        {
            $token['username'] = $res[0]['username'];
            $token['password'] = $res[0]['password'];
            $token['token'] = $res[0]['token'];
            $token['target'] = $user_info['username'];
        }
        else
        {
            $token['username'] = $user_info['username'];
            $token['password'] = $user_info['password'];
            $token['token'] = $res[0]['token'];
            $token['target'] = $user_info['username'];
        }

        return $token;
    }

    //获取token信息
    public function get_token($user_id,$username,$password)
    {
        //废弃
        return array('username'=>'','password'=>'','token'=>'','target'=>'');

        if(empty($user_id) || empty($username) || empty($password))
            return array();

        //获取token信息
        $res = $this->CI->agent_token_model->get_token($user_id);
        if(empty($res[0]))
            return array();

        if($res[0]['token_type'] == '1')
        {
            $token['username'] = $res[0]['username'];
            $token['password'] = $res[0]['password'];
            $token['token'] = $res[0]['token'];
            $token['target'] = $username;
        }
        else
        {
            $token['username'] = $username;
            $token['password'] = $password;
            $token['token'] = $res[0]['token'];
            $token['target'] = $username;
        }

        return $token;
    }

    public static function in_white_list($ip)	
    {
	    if(empty($ip))
	        return FALSE;
	
	    $white_str = file_get_contents(IP_WHITE_PATH);
	    $white_list = explode(PHP_EOL,trim($white_str));

	    if(!isset($white_list) || !is_array($white_list))
	        return FALSE;

	    return in_array($ip,$white_list);
    }

    //获取请求百度后端的host
    public function get_baidu_server_host()
    {
        $user_id = Auth_filter::current_sem_id();
        $server_conf = $this->CI->config->item('baidu_server_host');
        if(empty($user_id))
        {
            return $server_conf[array_rand($server_conf)];
        }
        return $server_conf[$user_id%SWAN_DB_COUNT];
    }

    //是否为客户端请求
    public function is_client()
    {
        $this->CI->load->library('user_agent');
        $ua = $this->CI->agent->agent_string();

        $pattern = '/(.*)'.CLIENT_UA.'(.*)/i';
        
        return preg_match($pattern,$ua);
    }
}

?>
