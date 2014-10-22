<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Feed extends CI_Controller {
    private $user_id	= 0;
    private $sem_id 	= 0;

    private $plan_id	= 0;
	private $unit_id	= 0;
	private $limit		= '10';
	private $offset		= '0';

    //搜索条件
    private $keyword	= '';
    private $bid_status	= array();
    private $match_type	= array();
    private $quality	= array();
    private $tag_id        = '';
//  private $min_price	= 0;
//  private $max_price	= 0;

    private function _init() {
        $user_status = Auth_filter::api_check_userid(Auth_filter::current_sem_id());
        if(!isset($user_status[0]) || !$user_status[0]) {
            $this->output->set_error($user_status[1], $user_status[2]);
            return FALSE;
        }
        $this->user_id = Auth_filter::current_userid();
        $this->sem_id = Auth_filter::current_sem_id();

        !empty($_REQUEST['page_size']) && 
            $this->limit = intval($_REQUEST['page_size']);
        (!empty($_REQUEST['page']) && intval($_REQUEST['page']) > 0) && 
            $this->offset = $this->limit * (intval($_REQUEST['page']) - 1);

        if (!empty($_REQUEST['unit_id'])) {
            $this->unit_id = intval($_REQUEST['unit_id']);
        } else if (!empty($_REQUEST['plan_id'])) {
            //根据计划查找unit_id
            $this->load->model('unit_model');
            $this->load->helper('array_util_helper');

            $units = $this->unit_model->get_by_params(array('plan_id' => intval($_REQUEST['plan_id']), 'user_id' => $this->sem_id));
            $this->unit_id = data_to_array($units, 'unit_id', 'intval');
            if (empty($this->unit_id)) {

                $ret = array(
                    'list' => array(),
                    'page' => array(
                        'page_size' => $this->limit,
                        'cur_page' => 0,
                        'total_page' => 0,
                        'count' => 0,
                    )
                );
                $this->output->set_json(
                    array(
                        'status' => 'success',
                        'data' => $ret,
                    )
                );       
                return FALSE;
            }
        }

        // 高级搜索参数
		$keyword = $this->input->get_post('keyword');
		!empty($keyword) && $this->keyword = $keyword;
		$bid_status = isset($_REQUEST['bid_status']) ? trim($_REQUEST['bid_status']) : '';
		$bid_status = array_filter(explode(',', $bid_status));
		!empty($bid_status) && $this->bid_status = $bid_status;
		$match_type = isset($_REQUEST['match_type']) ? trim($_REQUEST['match_type']) : '';
		$match_type = array_filter(explode(',', $match_type));
		!empty($match_type) && $this->match_type = $match_type;
		$quality = isset($_REQUEST['quality']) ? trim($_REQUEST['quality']) : '';
		$quality = array_filter(explode(',', $quality));
		!empty($quality) && $this->quality = $quality;

        $tag_id = isset($_REQUEST['tag_id']) ? trim($_REQUEST['tag_id']) : '';
        !empty($tag_id) && $this->tag_id = $tag_id;

//		$min_price = isset($_REQUEST['min_price']) ? floatval($_REQUEST['min_price']) : 0;
//		!empty($min_price) && $this->min_price = $min_price;
//		$max_price = isset($_REQUEST['max_price']) ? floatval($_REQUEST['max_price']) : 0;
//		!empty($max_price) && $this->max_price = $max_price;

        $params = array('user_id' => $this->user_id, 'sem_id' => $this->sem_id);
        $this->load->library('service/refbid_service', $params);

        return TRUE;
    }

    public function index() {
        if (!$this->_init()) {
            return FALSE;
        }

        $params = array(
            'offset'    => $this->offset,
            'limit'     => $this->limit,
            'orderby'   => "ref_status desc, last_update desc",
        );
        !empty($this->unit_id) && $params['unit_id'] = $this->unit_id;
        // 高级搜索参数
		!empty($this->keyword)		&& $params['keyword'] = array('op' => 'like', 'value' => $this->keyword);
		!empty($this->bid_status)	&& $params['bid_status'] = $this->bid_status;
		!empty($this->match_type)	&& $params['match_type'] = $this->match_type;
		!empty($this->quality)		&& $params['quality'] = $this->quality;
		!empty($this->tag_id)		&& $params['tag_id'] = $this->tag_id;
//		!empty($this->min_price)	&& $params['price >='] = $this->min_price;
//		!empty($this->max_price)	&& $params['price <='] = $this->max_price;

        $list = $this->refbid_service->get_list($params);

        $c_params = array();
        !empty($this->unit_id) && $c_params['unit_id'] = $this->unit_id;
        $item_count = $this->refbid_service->get_list_count($c_params);

        $calcing = 0;
        foreach ($list as $keyword) {
            if ($keyword['ref_status'] == 1) {
                $calcing = 1;
                break;
            }
        }

        $ret = array(
            'list' => $list,
            'calculating' => $calcing,
            'page' => array(
                'page_size' => $this->limit,
                'cur_page' => ceil($this->offset / $this->limit) + 1,
                'total_page' => ceil($item_count / $this->limit),
                'count' => $item_count,
            )
        );
        $this->output->set_json(
            array(
                'status' => 'success',
                'data' => $ret,
            )
        );
    }
}
