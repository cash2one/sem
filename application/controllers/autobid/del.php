<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Del extends CI_Controller {

    private $user_id            = 0;

    public function clear() {
        // 清空指定用户的所有智能竞价词
        if ( ! $this->_init()) {
            return FALSE;
        }
        $this->load->library('service/autobid_service');
        $status = $this->autobid_service->clear_autobid_keywords($this->user_id);
        
        if ( ! $status) {
            $this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '13',
                'message' => '清空所有智能竞价关键词失败'))
            );
            return FALSE;
        }

        $this->output->set_output(json_encode(array(
            'status' => 'success'))
        );
        return TRUE;
    }


    /* *
     * 删除指定用户的指定智能竞价关键词外的所有智能竞价词
     * */
    public function except() {
        if (!$this->_init()) {
            return FALSE;
        }
        
        if (!isset($_REQUEST['keyword_ids'])) {
            $this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '11',
                'error_message' => '缺少必须请求参数：keyword_ids'))
            );
            return FALSE;
        }
        $keyword_ids = array_filter(explode(',', $_REQUEST['keyword_ids']));
        
        if (empty($keyword_ids)) {
            $this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '12',
                'error_message' => '请求参数：keyword_ids 格式错误'))
            );
            return FALSE;
        }

        if (count($keyword_ids) > 100) {
            $this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '14',
                'message' => '保留竞价关键词数过多，不能大于100个。'))
            );
            return FALSE;
        }

        // 删除除keyword_ids中指定的关键词之外的智能竞价词
        $this->load->model('autobid_model');
        $all_autobid_keyword_ids = $this->autobid_model->get_all_autobid_keywords($this->user_id);
        $excepted_autobid_keyword_ids = array_diff($all_autobid_keyword_ids,
            $keyword_ids);
        $status = $this->autobid_model->delete_autobid_keywords($excepted_autobid_keyword_ids);

        if (!$status) {
            $this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '13',
                'message' => '删除智能竞价关键词失败'))
            );
            return FALSE;
        }

        $this->output->set_output(json_encode(array(
            'status' => 'success'))
        );
    }


    public function index() {
        // 删除指定用户的指定智能竞价关键词
        if ( ! $this->_init()) {
            return FALSE;
        }
        
        if ( ! isset($_REQUEST['keyword_ids'])) {
            $this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '11',
                'error_message' => '缺少必须请求参数：keyword_ids'))
            );
            return FALSE;
        }
        $keyword_ids = array_filter(explode(',', $_REQUEST['keyword_ids']));
        
        if (empty($keyword_ids)) {
            $this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '12',
                'error_message' => '请求参数：keyword_ids 格式错误'))
            );
            return FALSE;
        }

        // 删除智能竞价表中的关键词,并将关键词的状态至为「未投放」状态
        $this->load->model('autobid_model');
        $status = $this->autobid_model->delete_autobid_keywords($keyword_ids);

        if ( ! $status) {
            $this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '13',
                'message' => '删除智能竞价关键词失败'
                ))
            );
            return FALSE;
        }

        $this->output->set_output(json_encode(array(
            'status' => 'success'
            ))
        );
    }


    private function _init() {
        $this->load->library('auth_filter');
        list($success, $code, $message) = Auth_filter::api_check_userid(Auth_filter::current_sem_id());
        if (!$success) {
            $this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => $code,
                'message' => $message))
            );
            return FALSE;
        }

        if ( ! isset($_REQUEST['user_id'])) {
            $this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '11',
                'error_message' => '缺少必须请求参数：user_id'))
            );
            return FALSE;
        }

        $this->user_id = intval($_REQUEST['user_id']);

        if ($this->user_id === 0) {
            $this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '12',
                'error_message' => '请求参数：user_id 格式错误'))
            );
            return FALSE;
        }
        return TRUE;
    }
}

/* End of file del.php */
/* Location: ./application/controllers/autobid/del.php */
