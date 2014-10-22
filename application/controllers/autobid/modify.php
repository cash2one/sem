<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * 修改竞价词设置   
 */
class Modify extends CI_Controller {

	private $user_id		= 0;
	private $keyword_ids	= array();
	private $update_fields	= array();
	private $support_fields = array(
        'target_rank','max_bid','bid_area','snipe',
        'snipe_strategy','snipe_without_strategy','snipe_domain');

	public function index() {
		if ( ! $this->_init()) {return FALSE;}

		$this->load->model('keyword_model');
		$this->load->helper('array_util_helper');

        // 获取用户处于竞价中或竞价暂停的keyword
        $user_exist_keyword = $this->keyword_model->get_by_params(
            array('user_id' => $this->user_id,'bid_status !=' => '1'));
        $user_exist_keyword_ids = data_to_array(
            $user_exist_keyword, 'keyword_id');

        // 检查用户传入的keyword_ids是否属于用户竞价中或竞价暂停的keyword id
        // 如果不全部属于，则要不用户URL被篡改，要不就是系统逻辑存在问题，需
        // 谨慎处理！
        if (count(array_intersect($this->keyword_ids,
            $user_exist_keyword_ids)) !== count($this->keyword_ids)) {
			$this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '11',
                'error_message' => '用户传入关键词ID非本用户所属关键词'))
            );
			return FALSE;
        }

		// 过滤http://
        if (isset($this->update_fields['snipe_domain'])) {
            $this->update_fields['snipe_domain'] = 
                rtrim(preg_replace('#^https?://#','',
                    $this->update_fields['snipe_domain']), '/');
        }

		$params_autobid = array();
		foreach ($this->keyword_ids as $keyword_id) {
			$params_autobid[] = array_merge(
				$this->update_fields,
                array(
                    'keyword_id' => $keyword_id,
                    'complete_feedback' => '0')
            );
            $params_keyword[] = array(
                    'keyword_id' => $keyword_id,
                    'price' => NULL,
                    'rank' => '-1'
                ); 
		}

		$this->load->model('autobid_model');
		$this->autobid_model->update_autobid($params_autobid);
        $this->keyword_model->update_keyword($params_keyword);
        $this->output->set_output(json_encode(
            array('status' => 'success')));
	}


	private function _init() {
		$this->load->library('auth_filter');
        list($success, $code, $message) = Auth_filter::api_check_userid(
            Auth_filter::current_sem_id());
		if ( ! $success) {
            $this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => $code,
                'message' => $message))
            );
			return FALSE;
		}
		$this->user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
		$keyword_ids = isset($_REQUEST['keyword_ids']) ? trim($_REQUEST['keyword_ids']) : '';
		$keyword_ids = explode(',', $keyword_ids);
		$keyword_ids = array_filter($keyword_ids);
		if (empty($keyword_ids)) {
			$this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '11',
                'error_message' => '无效关键词ID'))
            );
			return FALSE;
		}
		$this->keyword_ids = $keyword_ids;
        
        // 目标排名
        $target_rank = isset($_REQUEST['target_rank']) ? trim($_REQUEST['target_rank']) : '';
        // 最高出价
        $max_bid = isset($_REQUEST['max_bid']) ? trim($_REQUEST['max_bid']) : '';
        // 竞价地域
        $bid_area = isset($_REQUEST['bid_area']) ? trim($_REQUEST['bid_area']) : '';
        // 是否锁定竞争对手
        $snipe = isset($_REQUEST['snipe']) ? trim($_REQUEST['snipe']) : '0';
        if (empty($target_rank)
            OR empty($max_bid)
            OR empty($bid_area)) {
			$this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '12',
                'error_message' => '部分参数为空'))
            );
			return FALSE;
        }
		// 获取更新字段
        $update_fields = array_intersect_key(
            $_REQUEST, array_flip($this->support_fields));
		if (empty($update_fields)) {
			$this->output->set_output(json_encode(array(
                'status' => 'failed',
                'error_code' => '12',
                'error_message' => '更新字段为空'))
            );
			return FALSE;
		}
		$this->update_fields = $update_fields;
		return TRUE;
	}
}


/* End of file. */
