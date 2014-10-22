<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * 产品用户使用情况统计 
 */
class Cus_day_report extends CI_Controller {

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
    }


    public function index($date=NULL)
    {
        if (empty($date)) {
            $date = date('Y-m-d',time());
        }

        //获取推广数据
        $user_info = $this->day_report_service->get_user_info($date);
        //获取所有激活竞价用户
        $activate_bid_user = $this->day_report_service->get_activate_bid_user();
        //获取当日活跃用户
        $today_active_user = $this->day_report_service->get_active_user($date);
        
        // 获取核心用户（即达标客户）
        // TODO(性能优化huangshitao)
        $start_date_r = $date;
        $end_date_r = date('Y-m-d',strtotime('-15 Day',strtotime($date)));
        $num_active_days = 10;
        $num_active_keywords = 5;
        $core_user = $this->day_report_service->get_core_user(
            $num_active_days, $num_active_keywords,
            $end_date_r, $start_date_r);
            
        // 获取流失客户。所谓流失客户是值有过出价记录，
        // 但3天及其3天以上没有没有出价记录
        $end_date = date('Y-m-d',strtotime('-3 Day',strtotime($date)));;
        $lost_user= $this->day_report_service->get_lost_user($end_date);
        
        //竞价相关数据
        $bid_info = $this->day_report_service->get_bid_info();
            
        //竞价关键词效果统计
        // TODO(性能优化 huangshitao)
        $bid_keyword_stat = $this->day_report_service->get_bid_keyword_stat($date,$date);
            
        //获取总代理--客户数据
        $agency_info = $this->day_report_service->get_agency_info($date, 0);
        
        // 聚合数据
        $data = $this->day_report_service->aggregate($agency_info,
            $user_info,$activate_bid_user,$today_active_user,
            $core_user,$lost_user,$bid_info,
            $bid_keyword_stat,$date,0);
        
        // 插入管理员旗下所有代理商的统计数据
        $this->stat_model->insert_batch_agency($data);

        $agency_ids = $this->day_report_service->get_agency_ids();
        foreach($agency_ids as $req_agency_id) {
            //获取代理商下属客服--客户数据
            $agency_info = $this->day_report_service->get_agency_info($date, $req_agency_id);
            // 聚合数据
            $data = $this->day_report_service->aggregate($agency_info,
                $user_info,$activate_bid_user,$today_active_user,
                $core_user,$lost_user,$bid_info,
                $bid_keyword_stat,$date,$req_agency_id);
            
            // 插入代理商旗下客服统计数据
            $this->stat_model->insert_batch_agent($data);
        }
        echo "Done!";
    }


    // 更新竞价关键词效果统计结果
    // 每天运行一次
    public function update_bid_keyword_stat($date=NULL)
    {
        if (empty($date)) {
            $date = date('Y-m-d',time() - 24*3600);
        }

        //竞价关键词效果统计
        $bid_keyword_stat = $this->day_report_service->get_bid_keyword_stat($date,$date);
            
        //获取总代理--客户数据
        $agency_info = $this->day_report_service->get_agency_info($date, 0);
        $data = $this->day_report_service->aggregate_bid_keyword_stat($agency_info, $bid_keyword_stat, $date);
        
        $this->stat_model->update_batch_agency($data);

        $agency_ids = $this->day_report_service->get_agency_ids();
        foreach($agency_ids as $req_agency_id) {
            //获取代理商下属客服--客户数据
            $agency_info = $this->day_report_service->get_agency_info($date, $req_agency_id);
            $data = $this->day_report_service->aggregate_bid_keyword_stat($agency_info, $bid_keyword_stat, $date);

            $this->stat_model->update_batch_agent($data);
        }
        echo "update_bid_keyword_stat Done !";
    }
}

/* End of file cus_day_report.php */
/* Location: ./application/controllers/statistics/cus_day_report.php */
