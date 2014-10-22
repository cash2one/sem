<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refbid_service {
    private $CI;
    private $user_id;
    private $sem_id;
  
    public function __construct($params) {
        $this->CI =& get_instance();
        $this->CI->load->model('keyword_model');
        $this->CI->load->library('baidu_service', $params);

        $this->user_id = $params['user_id'];
        $this->sem_id = $params['sem_id'];
    }

    public function add_keyword($params = array()) {
        if (empty($params))
            return FALSE;

        $this->CI->keyword_model->add_refbid_keyword($params);
        return TRUE;
    }

    public function delete_keyword($keyword_ids) {
        $this->CI->keyword_model->delete_refbid_keywords($keyword_ids);
        return TRUE;
    }

    public function update_keyword($keywords) {
        if (empty($keywords)) {
            return FALSE;
        } 
        
        $baidu_result = $this->CI->baidu_service->baidu_keyword_update($keywords);
        if ($baidu_result['header']['error_code'] != 0 || empty($baidu_result)) {
            return $baidu_result['header'];
        }

        foreach ($baidu_result['body']['keywords'] as $keyword) {
            $db_params = array(
                'unit_id'               => $keyword['adgroup_id'],
                'keyword'               => $keyword['keyword'],
                'price'                 => $keyword['price'],
                'pc_destination_url'    => $keyword['pc_destination_url'],
                'mobile_destination_url'=> $keyword['mobile_destination_url'],
                'match_type'            => $keyword['match_type'],
                'quality'               => $keyword['quality'],
                'pause'                 => $keyword['pause'],
                'status'                => $keyword['status'],
            );
            $condition = array('keyword_id' => $keyword['keyword_id']);
            $this->CI->keyword_model->update_params($db_params, $condition);
        }
        return TRUE;
    }

    public function get_list($params) {
        $k_params = array('user_id' => $this->sem_id);
        !empty($params['unit_id']) && $k_params['unit_id'] = $params['unit_id'];
        !empty($params['keyword_id']) && $k_params['keyword_id'] = $params['keyword_id'];
        !empty($params['keyword']) && $k_params['keyword'] = $params['keyword'];
        !empty($params['bid_status']) && $k_params['bid_status'] = $params['bid_status'];
        !empty($params['match_type']) && $k_params['match_type'] = $params['match_type'];
        !empty($params['quality']) && $k_params['quality'] = $params['quality'];
        !empty($params['tag_id']) && $k_params['tag_id'] = $params['tag_id'];

        $dbdata = $this->CI->keyword_model->get_keyword_list(
            $k_params,
            array('keyword_id', 'keyword', 'pause', 'status', 'unit_id', 'quality', 'quality_reason', 'match_type', 'bid_status', 'ref_status','tag'),
            array(
                'offset' => empty($params['offset']) ? 0 : $params['offset'],
                'limit' => empty($params['limit']) ? 10 : $params['limit'], 
                'orderby' => empty($params['orderby']) ? 'keyword_id asc' : $params['orderby'])
        );

        $this->CI->load->helper('array');
        $this->CI->load->helper('array_util_helper');
        $this->CI->load->model('unit_model');
        
        $unit_ids = array_unique(data_to_array($dbdata, 'unit_id', 'intval'));
        $unit_info = $this->CI->unit_model->get_unit_join_plan_by_params(array('unit_id' => $unit_ids), array('unit_id', 'unit_name', 'max_price', 'pause'), array('plan_id', 'plan_name', 'price_ratio', 'region', 'pause'), array('hash_key' => 'unit_id'));

        $keyword_ids = array_unique(data_to_array($dbdata, 'keyword_id'));
        $refbid_info = $this->CI->keyword_model->get_refbid_info($keyword_ids);

        $list = array();
        foreach ($dbdata as $keyword) {
            $info = elements(array('keyword_id', 'keyword', 'status', 'quality','quality_reason', 'pause', 'unit_id', 'match_type', 'bid_status', 'ref_status','tag'), $keyword, '');
            $info['unit'] = array(
                'unit_id' => $info['unit_id'], 
                'name' => $unit_info[$info['unit_id']]['unit_name'],
                'pause' => $unit_info[$info['unit_id']]['unit_pause'],
            );
            $info['plan'] = array(
                'plan_id' => $unit_info[$info['unit_id']]['plan_id'], 
                'name' => $unit_info[$info['unit_id']]['plan_name'],
                'pause' => $unit_info[$info['unit_id']]['plan_pause'],
            );
            $info['region'] = $unit_info[$info['unit_id']]['region'];
            if (empty($refbid_info[$keyword['keyword_id']])) {
                $info['refbid'] = array(
                    'bid_area' => '', 
                    'min_bid' => '', 
                    'max_bid' => '', 
                    'target_rank' => '', 
                    'calc_bid' => '', 
                    'calc_time' => '', 
                    'status' => 0,
                );
            } else {
                $info['refbid'] = $refbid_info[$keyword['keyword_id']];
            }
            unset($info['unit_id']);

            $list[] = $info;
        }

        return $list;
    }

    public function get_list_count($params = array()) {
        $k_params = array('user_id' => $this->sem_id);
        !empty($params['unit_id']) && $k_params['unit_id'] = $params['unit_id'];
        $count = $this->CI->keyword_model->get_by_params($k_params, array('count(*) as count'));
        $count = array_shift($count);
        return isset($count['count']) ? $count['count'] : '0';
    }

    public function get_refbid_info($keyword_ids) {
        $this->CI->keyword_model->get_refbid_info($keyword_ids);      
    }

    private function has_creative_in_unit($unit_id) {
        $creatives = $this->CI->creative_model->get_creative_with_stat_by_params(array('unit_id' => $unit_id, 'pause' => 0), array(), array('creative_id', 'title')); 
        return !empty($creatives);
    }

    private function verify_keyword_validation($keyword_info) {
        $this->CI->load->helper('array_util_helper');
        $keyword_info = change_data_key($keyword_info, 'keyword_id');
        $keyword_ids = array_keys($keyword_info);
        $list = $this->get_list(array('keyword_id' => $keyword_ids));

        foreach ($list as $keyword) {
            $keyword_info[$keyword['keyword_id']]['status'] = 0;
            $keyword_info[$keyword['keyword_id']]['plan_id'] = $keyword['plan']['plan_id'];
        }
          
        return $keyword_info;
    }

    public function calculate($params) {
        $calcable_keywords = $this->verify_keyword_validation($params);
        $db_params = array('ref_status' => 1);
        $condition = array('keyword_id' => array_keys($calcable_keywords));
        $this->CI->keyword_model->update_params($db_params, $condition);
        $this->add_keyword($calcable_keywords);

        return count($calcable_keywords);
    }

    public function current_calc_count()
    {
        $res = $this->CI->keyword_model->get_calc_count($this->sem_id);

        return empty($res[0]['count']) ? 0 : $res[0]['count'];
    }
}
