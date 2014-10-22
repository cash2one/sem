<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * 汇总统计用户每天（前一天）的推广数据和竞价关键词效果数据
 */
class Customers_sum_daily_report extends CI_Controller {

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
            $date = date('Y-m-d',time() - 24*3600);
        }

        // 获取推广数据
        $user_info = $this->day_report_service->get_user_info($date);
        
        // 竞价关键词效果统计
        $bid_keyword_stat = $this->day_report_service->get_bid_keyword_stat(
            $date,$date);

        /* 总代理 */
        // 获取总代理客户汇总数据
        $agency_info = $this->day_report_service->get_agency_info($date, 0);
        $agency_data = $this->day_report_service->aggregate_bid_keyword_stat(
            $agency_info, $bid_keyword_stat, $user_info, $date); 
        $this->stat_model->update_batch_agency($agency_data);

        /* 管理员 */
        $admin_info = $this->enterprise_sem_user_model->admin_info();
        $admin_data = $this->day_report_service->aggregate_bid_4admin(
            $admin_info, $agency_data, $date);
        $this->stat_model->update_batch_admin($admin_data);
        
        /* 超级管理员 */
        $sadmin_data = $this->day_report_service->aggregate_bid_4sadmin(
            $admin_info, $agency_data, $date);
        $this->stat_model->update_batch_sadmin($sadmin_data);
        
        /* 客服 */
        $agency_ids = $this->day_report_service->get_agency_ids();
        foreach($agency_ids as $req_agency_id) {
            // 获取代理商下属客服的客户汇总数据
            $agency_info = $this->day_report_service->get_agency_info(
                $date, $req_agency_id);
            $data = $this->day_report_service->aggregate_bid_keyword_stat(
                $agency_info, $bid_keyword_stat, $user_info, $date);

            $this->stat_model->update_batch_agent($data);
        }

        /* 分公司 */
        foreach(array(1,2,3,4,5) as $branch_level) {
            $branch_info = $this->day_report_service->get_branch_info(
                $date,$branch_level);
            $data = $this->day_report_service->aggregate_bid_keyword_stat(
                $branch_info,$bid_keyword_stat, $user_info, $date);
            
            $this->stat_model->update_batch_branch($data);
        }

        echo "Done !";
    }
}

/* End of file cus_day_report.php */
/* Location: ./application/controllers/statistics/cus_day_report.php */
