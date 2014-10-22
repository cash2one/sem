<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   产品使用状态
 *   
*/
class Cus_status_report extends CI_Controller {

	function __Construct()
	{
		parent::__Construct();
        //只允许CLI方式运行
        if(! $this->input->is_cli_request())
        {
            show_error('403 forbidden : no access to visit',403);
            exit ;
        }

        $this->load->library('service/stat/status_report_service');
        $this->load->library('service/stat/day_report_service');
	}

    public function index($date = NULL)
    {
        //计算客户日均消费
        if(is_null($date))
            $date = date('Y-m-d',time()-24*3600);

        //获取客户状态
        $cus_status = $this->status_report_service->get_cus_status();
        if(empty($cus_status))
        {
            $data = array();
        }
        else
        {
            //获取推广数据
            $user_info = $this->day_report_service->get_user_info($date);
            //竞价相关数据
            $bid_info = $this->status_report_service->get_bid_info();
            $data = $this->status_report_service->aggregate($cus_status,$user_info,$bid_info);
        }
        
        $csv_header = array('智投易账号','公司名称','注册时间','上次登录时间','状态','计划数','单元数','关键词数','日均消费','竞价中关键词数','竞价暂停关键词数','竞价中关键词日均占比','竞价暂停关键词日均占比');
        $this->load->helper('csv_util');
        $output = array_to_csv($csv_header,$data,';');
        //导出csv
        $filename='sem_cus_'.date('YmdHis',time()).'.csv';
        file_put_contents('/tmp/'.$filename,$output);
        echo "OK";
        return 0;
    }

}

/* End of file cus_status_report.php */
/* Location: ./application/controllers/statistics/cus_status_report.php */
