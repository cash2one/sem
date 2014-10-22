<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *   SEM代理商结算条件不符的客户名单
 *   
*/
class Customer_consume extends CI_Controller {

	function __Construct()
	{
		parent::__Construct();
        //只允许CLI方式运行
        if(! $this->input->is_cli_request())
        {
            show_error('403 forbidden : no access to visit',403);
            exit ;
        }

        $this->load->library('service/stat/cus_consume_service');
        $this->load->library('service/stat/common_service');
	}

    public function index($e_date = NULL)
    {
        //计算客户日均消费
        if(is_null($e_date))
            $e_date = date('Y-m-d',time()-24*3600);
        $s_date = $this->config->item('stat_s_date');
        $threshold = $this->config->item('stat_threshold');

        $consume = $this->cus_consume_service->get_consume($s_date,$e_date,$threshold);
        if(empty($consume))
        {
            $data['data'] = array();
            log_message('notice','customer consume : no customer consume less than '.$threshold);
        }
        else
        {
            //根据百度id获取所属代理商信息
            $ids = array_keys($consume);
            $level_info = $this->cus_consume_service->get_info($ids);
            $data['data'] = $this->cus_consume_service->combian($consume,$level_info);
        }
        $html = $this->load->view('stat/consume',$data,TRUE);

        //发送邮件
        $config = $this->config->item('cus_consume_mail');
        $config['email_subject'] = $config['email_subject'].$e_date;
        $this->common_service->send_email($html,$config);
        log_message('notice','customer consume : execute success!');
        
        echo "OK";
        return 0;
    }

}

/* End of file statistics.php */
/* Location: ./application/controllers/quota/statistics.php */
