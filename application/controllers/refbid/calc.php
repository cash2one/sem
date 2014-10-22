<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Calc extends CI_Controller {
    private $user_id	= 0;
    private $sem_id 	= 0;
    private $keyword_ids= 0;
    private $min_bid	= 0;
    private $max_bid	= 0;
    private $bid_area	= '';
    private $target_rank= 0;

    private function _init() {
        $user_status = Auth_filter::api_check_userid(Auth_filter::current_sem_id());
        if(!isset($user_status[0]) || !$user_status[0]) {
            $this->output->set_error($user_status[1], $user_status[2]);
            return FALSE;
        }
        $this->user_id = Auth_filter::current_userid();
        $this->sem_id = Auth_filter::current_sem_id();

        if (empty($_REQUEST['keyword_ids'])) {
            $this->output->set_error(1003, 'necessary params required');
            return FALSE;
        }
        if (empty($_REQUEST['bid_area'])) {
            $this->output->set_error(1003, 'invalid bid_area');
            return FALSE;
        }
        $city_code = $_REQUEST['bid_area'];
        if (empty($city_code) || !is_numeric($city_code)) {
            $this->output->set_error(1003, 'invalid bid_area');
            return FALSE;
        }
        $this->bid_area = $city_code;

        $this->keyword_ids = explode(',', trim($_REQUEST['keyword_ids']));

        !empty($_REQUEST['min_bid']) && 
            $this->min_bid = floatval($_REQUEST['min_bid']);
        !empty($_REQUEST['max_bid']) && 
            $this->max_bid = floatval($_REQUEST['max_bid']);

        !empty($_REQUEST['bid_area']) && 
        !empty($_REQUEST['target_rank']) &&
            $this->target_rank = intval($_REQUEST['target_rank']);

        if ($this->target_rank < 11 || $this->target_rank > 28) {
            $this->output->set_error(1004, 'invalid target_rank');
            return FALSE;
        }

        $params = array('user_id' => $this->user_id, 'sem_id' => $this->sem_id);
        $this->load->library('service/refbid_service', $params);
        $this->load->library('redis/ExpireHelperRedis');

        return TRUE;
    }

    public function index() {
        if (!$this->_init()) {
            return FALSE;
        }
        //是否处于恢复期
        $redis_value = ExpireHelperRedis::get_refbid_calc($this->sem_id);
        if($redis_value)
        {
            //最后一个关键词开始计算时间加上11分钟再减去当前时间
            $minute = ($redis_value+660-time())/60;
            $minute = empty($minute) ? '1' : $minute;
            $this->output->set_json(
                array(
                    'status' => 'success',
                    'error_code'=>'1005',
                    'error_msg'=>'recover calc resource',
                    'minute'=>$minute,
                )
            );
            $this->output->set_error(1005, 'recover calc resource');
            return ;
        }
        //获取当前计算中的关键词个数
        $curr_calc_count = $this->refbid_service->current_calc_count();
        $curr_sum = count($this->keyword_ids) + $curr_calc_count;
        if($curr_sum > 15 )
        {
            $this->output->set_error(1006, 'calc keyword greater than 15');
            return ;
        }
       /* else if($curr_sum == 15)
        {
            ExpireHelperRedis::set_refbid_calc($this->sem_id);
        }*/
        $params = array();
        foreach ($this->keyword_ids as $keyword_id) {
            $params[] = array(
                'keyword_id'    => $keyword_id,
                'min_bid'       => $this->min_bid,
                'max_bid'       => $this->max_bid,
                'bid_area'      => $this->bid_area,
                'target_rank'   => $this->target_rank,
            );
        }

        $pass_count = $this->refbid_service->calculate($params);
        //如果有15个在计算中
        if($curr_calc_count + $pass_count == 15)
        {
            ExpireHelperRedis::set_refbid_calc($this->sem_id);
        }
        
        $this->output->set_json(
            array(
                'status' => 'success',
            )
        );
    }
}
