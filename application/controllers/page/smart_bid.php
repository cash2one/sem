<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 智能竞价页面
*/
class Smart_bid extends CI_Controller {

    function __Construct()
    {
        parent::__construct();
        Auth_filter::check_auth();
    }

	public function index() {

        //is_tip   0:不提示   1：展示新手引导  2：提示24小时竞价
        $this->load->library('service/hzuser_service');
        $this->load->model('enterprise_sem_user_model');
        $user_info = $this->hzuser_service->user_info(Auth_filter::current_userid());
        $is_tip = 0;

        if(!empty($user_info))
        {
            $tips = $user_info['tips'];
            if(in_array($tips,array('0','1')))
            {
                $this->load->library('service/user_service');
                $bind_user = $this->user_service->get_sem_user(Auth_filter::current_userid());
                if(!empty($bind_user))
                {
                    $this->load->library('service/keyword_service');
                    $res = $this->keyword_service->bid_alive($bind_user);
                    if($res)
                    {
                        $is_tip = ($tips == '0') ? 1 : 2;
                        //更新为已提示
                        $update_data = array('tips'=>$is_tip);
                        $this->enterprise_sem_user_model->update_user_by_id(Auth_filter::current_userid(),$update_data);
                    }
                }
            }
        }
        $data['is_tip'] = $is_tip;
        //是否提示解除锁定
        if(!empty($user_info['lock']))
        {
            $update = array('`lock`'=>'0');
            $this->enterprise_sem_user_model->update_user_by_id(Auth_filter::current_userid(),$update);
            $data['unlock_tip'] = 1;
        }
        else
            $data['unlock_tip'] = 0;

        //是否展示调查问卷,show_questionnaire,1:显示  2:不显示
        /*if($user_info['questionnaire'] == 1) 
        {
            $update = array('`questionnaire`'=>'2');
            $this->enterprise_sem_user_model->update_user_by_id(Auth_filter::current_userid(),$update);
            $data['show_questionnaire'] = 1;
            //插入结果表，默认是未回应
            $add = array('user_id'=>Auth_filter::current_userid(),'result'=>'0');
            $this->load->model('questionnaire_res_model');
            $this->questionnaire_res_model->add($add);
        }
        else
            $data['show_questionnaire'] = '2';*/

        $data['show_questionnaire'] = '2';
        $this->load->library('smarty_service');
		$this->smarty_service->view('smart_bid.tpl',$data);
	}
}
