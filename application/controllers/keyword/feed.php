<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Feed extends CI_Controller {
    private $user_id	= 0;
    private $sem_id 	= 0;
    private $unit_id    = 0;
    private $start_time	= '';
    private $end_time	= '';
    private $limit		= '10';
    private $offset		= '0';
    private $orderby	= 'keyword_id';
    private $order_type	= 'desc';

    private function _init() {
        $user_status = Auth_filter::api_check_userid(Auth_filter::current_sem_id());
        if(!isset($user_status[0]) || !$user_status[0]) {
            $this->output->set_error($user_status[1], $user_status[2]);
            return FALSE;
        }
        $this->user_id = Auth_filter::current_userid();
        $this->sem_id = Auth_filter::current_sem_id();

        $this->start_time = empty($_REQUEST['start_time']) ? 
            date('Y-m-d') : trim($_REQUEST['start_time']);
        $this->end_time = empty($_REQUEST['end_time']) ? 
            date('Y-m-d') : trim($_REQUEST['end_time']);

        !empty($_REQUEST['page_size']) && 
            $this->limit = intval($_REQUEST['page_size']);
        (!empty($_REQUEST['page']) && intval($_REQUEST['page']) > 0) && 
            $this->offset = $this->limit * (intval($_REQUEST['page']) - 1);

        !empty($_REQUEST['orderby']) && 
            $this->orderby = trim($_REQUEST['orderby']);
        (!empty($_REQUEST['ordertype']) && in_array(trim($_REQUEST['ordertype']), array('asc','desc'))) &&
            $this->order_type = trim($_REQUEST['ordertype']);

        if (!empty($_REQUEST['unit_id'])) {
            $this->unit_id = intval($_REQUEST['unit_id']);
        } else {
            if (!empty($_REQUEST['plan_id'])) {
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
        }

        $params = array('user_id' => $this->user_id, 'sem_id' => $this->sem_id);
        $this->load->library('service/keyword_service', $params);

        return TRUE;
    }

    public function index() {
        if (!$this->_init()) {
            return FALSE;
        }

        $params = array(
            'start_time'=> $this->start_time,
            'end_time'  => $this->end_time,
            'offset'    => $this->offset,
            'limit'     => $this->limit,
            'orderby'   => "{$this->orderby} {$this->order_type}",
        );
        !empty($this->unit_id) && $params['unit_id'] = $this->unit_id;

        $list = $this->keyword_service->get_list($params);

        $c_params = array();
        !empty($this->unit_id) && $c_params['unit_id'] = $this->unit_id;
        $item_count = $this->keyword_service->get_list_count($c_params);
        
        $ret = array(
            'list' => $list,
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
