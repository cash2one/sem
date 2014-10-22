<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   MCC quota统计
 *   
*/
class Statistics extends CI_Controller {

	function __Construct()
	{
		parent::__Construct();
        //只允许CLI方式运行
        if(! $this->input->is_cli_request())
        {
            show_error('403 forbidden : no access to visit',403);
            exit ;
        }

        $this->load->library('service/quota_service');
	}

    public function index($date=NULL)
    {
        if(is_null($date))
            $date = date('Y-m-d',time());
        //1.获取mcc账号基本数据
        $base = $this->quota_service->mcc_base_data($date);
        if(empty($base))
        {
            $this->ext_log_data = array('result'=>'null mcc info');
            return 0;
        }
        //2.一周内聚合数据(自然周)
        $s_week = date('Y-m-d',strtotime("last Sunday")+24*3600);
        $week_data = $this->quota_service->mcc_week_data($s_week,$date);
        //3. 一个月的平均消耗
        $s_month = date('Y-m-d',strtotime("-30 days"));
        $month_data = $this->quota_service->mcc_month_data($s_month,$date);
        //4. 各个mcc账号下的客户数
        //$user_amount = $this->quota_service->user_calc();
        $data['data'] = $this->quota_service->combian($base,$date,$week_data,$month_data);

        $this->ext_log_data = array('result'=>'success');
        $html = $this->load->view('quota_stat/stat',$data,TRUE);

        //发送邮件
        $this->quota_service->send_email($html,$date);
        
        echo "OK";
        return 0;
    }

}

/* End of file statistics.php */
/* Location: ./application/controllers/quota/statistics.php */
