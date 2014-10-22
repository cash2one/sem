<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * 统计客户每日（前一天）的推广数据和竞价关键词效果数据
 */
class Customer_daily_report extends CI_Controller {

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
            $date = date('Y-m-d',time() - 24*3600);
        }

        // 获取推广数据
        $promote_info = $this->day_report_service->get_user_info($date);
        
        // 竞价关键词效果统计
        $bid_keyword_stat = $this->day_report_service->get_bid_keyword_stat($date,$date);

        // 获取用户详情
        $customer_basic_info = $this->enterprise_sem_user_model->customer_info();
        
        // 聚合用户数据
        $customer_bid_data = $this->day_report_service->aggregate_customer_bid_info($date,$customer_basic_info,$bid_keyword_stat, $promote_info);

        $this->stat_model->update_batch_customer($customer_bid_data);

        echo "Done!";
    }
}

/* End of file customer_daily_report.php */
/* Location: ./application/controllers/statistics/customer_daily_report.php */
