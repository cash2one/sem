<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class keyword_add extends CI_Controller {

    private $user_id	= '';
    private $sem_id 	= '';
    private $plan_id    = '';
    private $unit_id    = '';
    private $insert_data = array();

    public function index() {
        if (!$this->_init()) {
            return FALSE;
        }
        //判断计划或者单元是否存在
        $this->load->model('unit_model');
        $params = array('user_id'=>$this->sem_id,'plan_id'=>$this->plan_id,'unit_id'=>$this->unit_id);
        $unit_plan = $this->unit_model->get($params);

        if(empty($unit_plan[0]))
        {
            //调用数据更新接口
            $this->load->library('service/user_service');
            $update_data = array('baiduid'=>$this->sem_id);
            $sem_res = $this->user_service->sem_sync($update_data);
            if(!$sem_res[0])
            {
                $this->output->set_error(1012, 'update user data failed');
                return FALSE;
            }
        }
        else
        {
            $this->keyword_service->add_keyword_batch($this->insert_data);
        }

        $this->output->set_json(
            array(
                'status' => 'success',
                'error_code' => '',
                'error_msg' => '',
            )
        );
    }

    private function _init() {
        $user_status = Auth_filter::api_check_userid(Auth_filter::current_sem_id());
        if(!isset($user_status[0]) || !$user_status[0]) {
            $this->output->set_error($user_status[1], $user_status[2]);
            return FALSE;
        }
        $this->user_id = Auth_filter::current_userid();
        $this->sem_id = Auth_filter::current_sem_id();

        if (empty($_REQUEST['keywords'])) {
            $this->output->set_error(1000, 'keywords invalid');
            return FALSE;
        }
        $keywords = explode(',', trim($_REQUEST['keywords']));
        if (empty($_REQUEST['keyword_ids'])) {
            $this->output->set_error(1001, 'keyword_ids invalid');
            return FALSE;
        }
        $keyword_ids = explode(',', trim($_REQUEST['keyword_ids']));

        if (empty($_REQUEST['unit_id'])) {
            $this->output->set_error(1002, 'unit_id invalid');
            return FALSE;
        }
        $this->unit_id = intval($_REQUEST['unit_id']);

        if (empty($_REQUEST['plan_id'])) {
            $this->output->set_error(1003, 'plan_id invalid');
            return FALSE;
        }
        $this->plan_id = intval($_REQUEST['plan_id']);

        if (empty($_REQUEST['price'])) {
            $this->output->set_error(1004, 'price invalid');
            return FALSE;
        }
        $price = explode(',', trim($_REQUEST['price']));

        if (empty($_REQUEST['pc_destination_url'])) {
            $this->output->set_error(1005, 'pc_destination_url invalid');
            return FALSE;
        }
        $pc_destination_url = explode(',', trim($_REQUEST['pc_destination_url']));

        if (empty($_REQUEST['mobile_destination_url'])) {
            $this->output->set_error(1006, 'mobile_destination_url invalid');
            return FALSE;
        }
        $mobile_destination_url = explode(',', trim($_REQUEST['mobile_destination_url']));


        if (empty($_REQUEST['match_type'])) {
            $this->output->set_error(1007, 'match_type invalid');
            return FALSE;
        }
        $match_type = explode(',', trim($_REQUEST['match_type']));

        if (empty($_REQUEST['quality'])) {
            $this->output->set_error(1008, 'quality invalid');
            return FALSE;
        }
        $quality = explode(',', trim($_REQUEST['quality']));

        if (empty($_REQUEST['quality_reason'])) {
            $this->output->set_error(1009, 'quality_reason invalid');
            return FALSE;
        }
        $quality_reason = explode(',', trim($_REQUEST['quality_reason']));

        if (!isset($_REQUEST['pause'])) {
            $this->output->set_error(1010, 'pause invalid');
            return FALSE;
        }
        $pause = explode(',', trim($_REQUEST['pause']));

        if (empty($_REQUEST['status'])) {
            $this->output->set_error(1011, 'status invalid');
            return FALSE;
        }
        $status = explode(',', trim($_REQUEST['status']));

        if(count($keywords) != count($keyword_ids))
        {
            $this->output->set_error(1001, 'keyword_ids not match keywords');
            return FALSE;
        }
        for($count = 0;$count < count($keywords) ; ++$count)
        {
            $param = array();
            $param['keyword_id'] = $keyword_ids[$count];
            $param['keyword'] = $keywords[$count];
            $param['unit_id'] = $this->unit_id;
            $param['user_id'] = $this->sem_id;
            $param['price'] = empty($price[$count]) ? '' : $price[$count];
            $param['pc_destination_url'] = empty($pc_destination_url[$count]) ? '' : $pc_destination_url[$count];
            $param['mobile_destination_url'] = empty($mobile_destination_url[$count]) ? '' : $mobile_destination_url[$count];
            $param['match_type'] = empty($match_type[$count]) ? '' : $match_type[$count];
            $param['quality'] = empty($quality[$count]) ? '' : $quality[$count];
            $param['quality_reason'] = empty($quality_reason[$count]) ? '' : $quality_reason[$count];
            $param['pause'] = empty($pause[$count]) ? '' : $pause[$count];
            $param['status'] = empty($status[$count]) ? '' : $status[$count];

            $this->insert_data[] = $param;
        }

        $this->load->library('service/keyword_service');
        return TRUE;
    }

}
