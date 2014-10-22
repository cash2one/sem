<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * 用户汇总实时统计
 */
class Customers_sum_rt_report extends CI_Controller {

	function __Construct()
	{
        parent::__Construct();

        //只允许CLI方式运行
        if(! $this->input->is_cli_request()) {
            show_error('403 forbidden : no access to visit',403);
            exit ;
        }

        $this->load->library('service/stat/day_report_service');
        $this->load->model('stat_model');
        $this->load->model('enterprise_sem_user_model');
    }


    public function index($date=NULL)
    {
        if (empty($date)) {
            $date = date('Y-m-d',time());
        }

        // 获取推广数据
        $user_info = $this->day_report_service->get_user_info($date);
        // 获取所有激活竞价用户
        $activate_bid_user = $this->day_report_service->get_activate_bid_user();
        // 获取当日活跃用户
        $today_active_user = $this->day_report_service->get_active_user($date);
        
        // 获取核心用户（即达标客户）
        $start_date_r = $date;
        $end_date_r = date('Y-m-d',strtotime('-15 Day',strtotime($date)));
        $num_active_days = 10;
        $num_active_keywords = 5;
        $core_user = $this->day_report_service->get_core_user(
            $num_active_days, $num_active_keywords,
            $end_date_r, $start_date_r);
            
        // 获取流失客户。所谓流失客户是值有过出价记录，
        // 但3天及其3天以上没有没有出价记录
        $lost_user= $this->day_report_service->get_lost_user($date);
        
        // 竞价相关数据
        $bid_info = $this->day_report_service->get_bid_info();
            
        // 竞价关键词效果统计
        $bid_keyword_stat = array();

        /* 总代理 */
        $agency_info = $this->day_report_service->get_agency_info($date, 0);
        
        // 聚合数据
        $agency_data = $this->day_report_service->aggregate(
            $agency_info,$user_info,$activate_bid_user,
            $today_active_user,$core_user,$lost_user,$bid_info,
            $bid_keyword_stat,$date,0);
        
        $this->stat_model->insert_batch_agency($agency_data);
        
        /* 管理员 */
        $admin_info = $this->enterprise_sem_user_model->admin_info();
        $admin_data = $this->day_report_service->aggregate4admin(
            $admin_info, $agency_data, $date);
        $this->stat_model->insert_batch_admin($admin_data);
        
        /* 超级管理员 */
        $sadmin_data = $this->day_report_service->aggregate4sadmin(
            $admin_info, $agency_data, $date);
        $this->stat_model->insert_batch_sadmin($sadmin_data);

        /* 客服 */
        $agency_ids = $this->day_report_service->get_agency_ids();
        foreach($agency_ids as $req_agency_id) {
            // 获取代理商下属客服的客户数据
            $agency_info = $this->day_report_service->get_agency_info(
                $date, $req_agency_id);
            // 聚合数据
            $data = $this->day_report_service->aggregate(
                $agency_info,$user_info,$activate_bid_user,
                $today_active_user,$core_user,$lost_user,$bid_info,
                $bid_keyword_stat,$date,$req_agency_id);
            
            $this->stat_model->insert_batch_agent($data);
        }

        /* 分公司 */
        foreach(array(1,2,3,4,5) as $branch_level) {
            $branch_info = $this->day_report_service->get_branch_info(
                $date, $branch_level);
            // 聚合数据
            $data = $this->day_report_service->aggregate(
                $branch_info,$user_info,$activate_bid_user,$today_active_user,
                $core_user,$lost_user,$bid_info,$bid_keyword_stat,$date,0);
            $this->stat_model->insert_batch_branch($data);
        }
        echo "Done!";
    }
}

/* End of file customers_sum_rt_report.php */
/* Location: ./application/controllers/statistics/customers_sum_rt_report.php */
