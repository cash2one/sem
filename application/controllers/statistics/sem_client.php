<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * SEM客户端下载量和安装量统计
 *
 * */
class Sem_client extends CI_Controller {

	function __Construct()
	{
		parent::__Construct();
        //只允许CLI方式运行
        if(! $this->input->is_cli_request())
        {
            show_error('Forbidden!',403);
            exit ;
        }
	}


    public function index($num_of_last_days=1, $date_to=NULL, $version=NULL)
    {
        if (is_null($date_to)) {
            $yesterday = date('Y-m-d', time() - 24*3600);
            $date_to = $yesterday;
        }

        $minus_days = $num_of_last_days - 1;

        $date_from = date('Y-m-d',
            strtotime("-$minus_days day", strtotime($date_to)));

        $this->load->model('client_stat_model');
        $res1 = $this->client_stat_model->stat_download_and_install_total($date_from, $date_to, $version);
        $res2 = $this->client_stat_model->stat_install_total($date_from, $date_to, $version);
        $data = array();
        $data['download_and_install'] = $res1;
        $data['install'] = $res2;
        
        $html = $this->load->view('stat/sem_client',$data,TRUE);

        //发送邮件
        $this->load->library('service/stat/common_service');
        $config = $this->config->item('sem_client');
        $config['email_subject'] = $config['email_subject'].$date_to;
        $this->common_service->send_email($html,$config);
    }
}


/* End of file sem_client.php */
/* Location: ./application/controllers/statistics/sem_client.php */
