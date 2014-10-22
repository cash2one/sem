<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* *
 * 竞价词套餐购买申请
 * */
class Apply extends CI_Controller
{
    private $user_id = 0;

    public function index() {
        if ( ! $this->_init()) {
            return FALSE;
        }
        $this->load->library('service/package_service');
        
        if (! isset($_REQUEST['apply_type']) OR
            ! isset($_REQUEST['apply_package'])) {
            $this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '11',
                'error_message' => '缺少请求参数！'))
            );
            return FALSE;
        }
        $req_type = empty($_REQUEST['req_type'])?'':$_REQUEST['req_type'];
        $apply_type = $_REQUEST['apply_type'];
        $apply_package = $_REQUEST['apply_package'];

        if (! $this->package_service->is_apply_type_legal($apply_type) OR
            ! $this->package_service->is_package_id_legal($apply_package)) {
            $this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '12',
                'error_message' => '非法请求参数！')));
            return FALSE;
        }

        list($status,$code,$msg) 
            = $this->package_service->is_apply_valid(
                $apply_type, $apply_package, $this->user_id);
        if ( ! $status) {
            $this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '13',
                'error_message' => $msg)));
            return FALSE;
        }

        $money = $this->package_service->calc_total(
            $this->user_id,
            $apply_type,
            $apply_package);

        if ($money < 0) {
            $this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '15',
                'message' => '总额计算错误！'))
            );
            return FALSE;
        }

        if ($req_type == 'apply') {
            $res = $this->package_service->add(
                $this->user_id,
                $apply_type,
                $apply_package,
                $money);
            if ($res) {
                $this->package_service->notify($this->user_id);
            }
        }

        $this->output->set_output(json_encode(array(
            'status' => 'success',
            'money' => $money)));
    }


    private function _init() {
        $this->load->library('auth_filter');
        list($success, $code, $message)
            = Auth_filter::api_check_userid(Auth_filter::current_sem_id());
        if ( ! $success) {
            $this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => $code,
                'message' => $message))
            );
            return FALSE;
        }

        $this->user_id = Auth_filter::current_userid();
        if (empty($this->user_id)) {
            $this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '11',
                'error_message' => '缺少必须请求参数：userid'))
            );
            return FALSE;
        }

        return TRUE;
    }
}

/* End of file del.php */
/* Location: ./application/controllers/autobid/del.php */
