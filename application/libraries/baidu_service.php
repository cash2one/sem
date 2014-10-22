<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Baidu_service {

	private $host ;
	private $username;
	private $password;
	private $token;
	private $target;
	private $_ci = NULL;

    private function get_token($user_id, $baidu_id) {
		$this->_ci->load->library('service/common_service');
        $token = $this->_ci->common_service->get_token_info($user_id, $baidu_id);
        if (empty($token)) {
            throw new Exception("Token Exception: can not find valid token for this user_id:{$user_id}  baidu_id:{$baidu_id}");
        }
        return $token;
    }

	public function __construct($params = NULL) {
		$this->_ci =& get_instance();
		$this->_ci->load->library('curl');
        $this->_ci->load->library('service/common_service');

        $this->host = $this->_ci->common_service->get_baidu_server_host();
        if(!is_null($params))
        {
            $token = $this->get_token($params['user_id'], $params['sem_id']);
            $this->username = $token['username'];
            $this->password = $token['password'];
            $this->token = $token['token'];
            $this->target = $token['target'];
        }
	}

	// 计划
	public function baidu_campaign_add($campaign_name, $region_target, $show_prob, $price_ratio) {
		$params = array(
			'username'		=> $this->username,
			'password'		=> $this->password,
			'token'			=> $this->token,
			'target'		=> $this->target,
			'campaign_name'	=> $campaign_name,
			'region_target'	=> $region_target,
			'show_prob'		=> $show_prob,
			'price_ratio'	=> $price_ratio,
		);
		$result = $this->_ci->curl->simple_post($this->host . '/baidu/campaign/add', json_encode($params));
		$ret = json_decode($result, TRUE);
		empty($ret) && $ret = array('header' => array('error_code' => 1, 'error_msg' => 'json_decode error'));
		return $ret;
	}

	public function baidu_campaign_delete($campaign_ids) {
		$params = array(
			'username'		=> $this->username,
			'password'		=> $this->password,
			'token'			=> $this->token,
			'target'		=> $this->target,
			'campaign_ids'	=> $campaign_ids,
		);
		$result = $this->_ci->curl->simple_post($this->host . '/baidu/campaign/delete', json_encode($params));
		$ret = json_decode($result, TRUE);
		empty($ret) && $ret = array('header' => array('error_code' => 1, 'error_msg' => 'json_decode error'));
		return $ret;
	}

	public function baidu_campaign_update($campaigns) {
		$params = array(
			'username'		=> $this->username,
			'password'		=> $this->password,
			'token'			=> $this->token,
			'target'		=> $this->target,
			'campaigns'		=> $campaigns,		
		);
		$result = $this->_ci->curl->simple_post($this->host . '/baidu/campaign/update', json_encode($params));
		$ret = json_decode($result, TRUE);
		empty($ret) && $ret = array('header' => array('error_code' => 1, 'error_msg' => 'json_decode error'));
		return $ret;
	}

	// 单元
	public function baidu_adgroup_add($campaign_id, $adgroup_name, $max_price) {
		$params = array(
			'username'		=> $this->username,
			'password'		=> $this->password,
			'token'			=> $this->token,
			'target'		=> $this->target,
			'campaign_id'	=> $campaign_id,
			'adgroup_name'	=> $adgroup_name,
			'max_price'		=> $max_price,
		);
		$result = $this->_ci->curl->simple_post($this->host . '/baidu/adgroup/add', json_encode($params));
		$ret = json_decode($result, TRUE);
		empty($ret) && $ret = array('header' => array('error_code' => 1, 'error_msg' => 'json_decode error'));
		return $ret;
	}

	public function baidu_adgroup_delete($adgroup_ids) {
		$params = array(
			'username'		=> $this->username,
			'password'		=> $this->password,
			'token'			=> $this->token,
			'target'		=> $this->target,
			'adgroup_ids'	=> $adgroup_ids,
		);
		$result = $this->_ci->curl->simple_post($this->host . '/baidu/adgroup/delete', json_encode($params));
		$ret = json_decode($result, TRUE);
		empty($ret) && $ret = array('header' => array('error_code' => 1, 'error_msg' => 'json_decode error'));
		return $ret;
	}

	public function baidu_adgroup_update($adgroups) {
		$params = array(
			'username'		=> $this->username,
			'password'		=> $this->password,
			'token'			=> $this->token,
			'target'		=> $this->target,
			'adgroups'		=> $adgroups,
		);
		$result = $this->_ci->curl->simple_post($this->host . '/baidu/adgroup/update', json_encode($params));
		$ret = json_decode($result, TRUE);
		empty($ret) && $ret = array('header' => array('error_code' => 1, 'error_msg' => 'json_decode error'));
		return $ret;
	}

    public function baidu_keyword_add($keyword, $unit_id, $match_type) {
        $params = array(
            'username'		=> $this->username,
            'password'		=> $this->password,
            'token'			=> $this->token,
            'target'		=> $this->target,
            'keyword'	    => $keyword,
            'adgroup_id'	=> $unit_id,
            'match_type'	=> $match_type,
        );
        $result = $this->_ci->curl->simple_post($this->host . '/baidu/keyword/add', json_encode($params));
        return json_decode($result, TRUE);
    }

    public function baidu_keyword_delete($keyword_ids = array()) {
        $params = array(
            'username'		=> $this->username,
            'password'		=> $this->password,
            'token'			=> $this->token,
            'target'		=> $this->target,
            'keyword_ids'   => $keyword_ids,
        );
        $result = $this->_ci->curl->simple_post($this->host . '/baidu/keyword/delete', json_encode($params));
        return json_decode($result, TRUE);
    }

    public function baidu_keyword_update($keywords) {
        $default_params = array(
            'username'	=> $this->username,
            'password'	=> $this->password,
            'token'		=> $this->token,
            'target'	=> $this->target,
            'keywords'  => $keywords,
        );

        $result = $this->_ci->curl->simple_post($this->host . '/baidu/keyword/update', json_encode($default_params));
        return json_decode($result, TRUE);
    }

    public function baidu_creative_add($unit_id, $title, $ext_params) {
        $params = array(
            'username'		            => $this->username,
            'password'	            	=> $this->password,
            'token'			            => $this->token,
            'target'		            => $this->target,
            'adgroup_id'	            => $unit_id,
            'title'	                    => $title,
            'description1'	            => $ext_params['description1'],
            'description2'	            => $ext_params['description2'],
            'pc_destination_url'	    => $ext_params['pc_destination_url'],
            'pc_display_url'	        => $ext_params['pc_display_url'],
        );

        !empty($ext_params['mobile_destination_url']) && 
            $params['mobile_destination_url'] = $ext_params['mobile_destination_url'];
        !empty($ext_params['mobile_display_url']) && 
            $params['mobile_display_url'] = $ext_params['mobile_display_url'];
        $result = $this->_ci->curl->simple_post($this->host . '/baidu/creative/add', json_encode($params));
        return json_decode($result, TRUE);
    }

    public function baidu_creative_delete($creative_ids = array()) {
        $params = array(
            'username'		=> $this->username,
            'password'		=> $this->password,
            'token'			=> $this->token,
            'target'		=> $this->target,
            'creative_ids'  => $creative_ids,
        );
        $result = $this->_ci->curl->simple_post($this->host . '/baidu/creative/delete', json_encode($params));
        return json_decode($result, TRUE);
    }


    public function baidu_creative_update($creatives) {
        $default_params = array(
            'username'	=> $this->username,
            'password'	=> $this->password,
            'token'		=> $this->token,
            'target'	=> $this->target,
            'creatives' => $creatives,
        );

        $result = $this->_ci->curl->simple_post($this->host . '/baidu/creative/update', json_encode($default_params));
        return json_decode($result, TRUE);
    }

	// 绩效
	public function baidu_report_get($date, $type, $ids = array()) {
		$params = array(
			'username'	=> $this->username,
			'password'	=> $this->password,
			'target'	=> $this->target,
			'token'		=> $this->token,
			'date'		=> $date,
			'type'		=> $type,
			'ids'		=> $ids,
		);
		$result = $this->_ci->curl->simple_post($this->host . '/baidu/report/get', json_encode($params));
		// 适当处理
		return $result;
	}

    //修改用户信息
    public function baidu_user_modify($update_data)
    {
		$result = $this->_ci->curl->simple_post($this->host . '/baidu/account-info/update', json_encode($update_data));
		return $result;
    
    }

    //同步用户信息
    public function baidu_user_sync($sync_data)
    {
		$result = $this->_ci->curl->simple_post($this->host . '/baidu/all/get', json_encode($sync_data));
		return $result;
    }
    
    //获取用户信息
    public function baidu_user_info($token)
    {
		$result = $this->_ci->curl->simple_post($this->host . '/baidu/account-info/get', json_encode($token));
		return $result;
    }

    public function add_autobid_msg($data)
    {
        $host = $this->_ci->config->item('auto_server_host');
        $result = $this->_ci->curl->simple_post($host . '/sem/prior_keyword', json_encode($data));
        return $result;
    }
}

