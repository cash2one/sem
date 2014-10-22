<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * 用户的竞价相关统计 
 */
class Customer_rt_report extends CI_Controller {

	function __Construct()
	{
		parent::__Construct();
        
        //只允许CLI方式运行
        if( ! $this->input->is_cli_request()) {
            show_error('403 forbidden : no access to visit',403);
            exit ;
        }

        $this->load->library('service/stat/day_report_service');
        $this->load->model('enterprise_sem_user_model');
        $this->load->model('stat_model');
    }

    public function index($date=NULL)
    {
        if (empty($date)) {
            $date = date('Y-m-d',time());
        }

        // 获取当日活跃用户
        $today_active_user = $this->day_report_service->get_active_user($date);
        // 获取当日不活跃用户
        $today_inactive_user = $this->day_report_service->get_inactive_user($date);
        // 获取流失客户。所谓流失客户是指有过出价记录，
        // 但3天及其3天以上没有没有出价记录
        $lost_user= $this->day_report_service->get_lost_user($date);
        // 获取核心用户（即达标客户）
        $start_date_r = $date;
        $end_date_r = date('Y-m-d',strtotime('-15 Day',strtotime($date)));
        $num_active_days = 10;
        $num_active_keywords = 5;
        $core_user = $this->day_report_service->get_core_user(
            $num_active_days, $num_active_keywords,
            $end_date_r, $start_date_r);

        // 竞价相关数据
        $bid_info = $this->day_report_service->get_bid_info();
        // 获取推广数据
        $promote_info = $this->day_report_service->get_user_info($date);
        // 竞价关键词效果统计
        //$bid_keyword_stat = $this->day_report_service->get_bid_keyword_stat($date,$date);
        $bid_keyword_stat = array();

        // 开启竞价时间信息
        $activate_bid_info = $this->day_report_service->get_activate_bid_user();

        // 获取用户详情
        $customer_basic_info = $this->enterprise_sem_user_model->customer_info();
        
        $customer_stat_data = $this->day_report_service->aggregate_customer_info(
            $date,$customer_basic_info,$today_active_user,$today_inactive_user,
            $lost_user,$bid_info,$promote_info,$bid_keyword_stat,
            $activate_bid_info,$core_user);

        $this->stat_model->insert_batch_customer($customer_stat_data);

        echo "Done!";
    }
}

/* End of file customer_rt_report.php */
/* Location: ./application/controllers/statistics/customer_rt_report.php */
