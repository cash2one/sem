<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Keyword_service {

    private $CI;
    private $user_id;
    private $sem_id;
  
    public function __construct($params = NULL) {
        $this->CI =& get_instance();
        $this->CI->load->model('keyword_model');
        $this->CI->load->library('baidu_service', $params);

        if(!is_null($params))
        {
            $this->user_id = $params['user_id'];
            $this->sem_id = $params['sem_id'];
        }
    }

    public function add_keyword($keywords, $unit_id, $params = array()) {
        if (empty($keywords) && empty($unit_id))
            return FALSE;

        $error = array();
        foreach ($keywords as $keyword) {
            $baidu_result = $this->CI->baidu_service->baidu_keyword_add($keyword, $unit_id, 1);
            if ($baidu_result['header']['error_code'] != 0 || empty($baidu_result)) {
                $error[] = $baidu_result['header'];
                continue;
            }

            $db_params = array(
                'keyword_id' => $baidu_result['body']['keyword_id'],
                'user_id' => $this->sem_id,
                'keyword' => $keyword,
                'unit_id' => $unit_id,
                'price'  => $baidu_result['body']['price'],
                'pc_destination_url'  => $baidu_result['body']['pc_destination_url'],
                'mobile_destination_url'  => $baidu_result['body']['mobile_destination_url'],
                'match_type'  => $baidu_result['body']['match_type'],
				'quality' => $baidu_result['body']['quality'],
				'quality_reason' => $baidu_result['body']['quality_reason'],
                'pause'  => $baidu_result['body']['pause'],
                'status'  => $baidu_result['body']['status'],
            );
            $this->CI->keyword_model->insert($db_params);
        }
        return TRUE;
    }

    public function add_keyword_batch($insert_data) {
        if (empty($insert_data))
            return FALSE;

        $this->CI->keyword_model->insert_batch($insert_data);
        return TRUE;
    }

    public function delete_keyword($keyword_ids) {
        $baidu_result = $this->CI->baidu_service->baidu_keyword_delete($keyword_ids);
        if ($baidu_result['header']['error_code'] != 0 || empty($baidu_result)) {
            return $baidu_result['header'];
        }

        $db_params = array(
            'user_id' => $this->sem_id,
            'keyword_id' => $keyword_ids,
        );
        //$this->CI->keyword_model->delete_params($db_params);

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
				'quality_reason' 		=> $keyword['quality_reason'],
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

        $dbdata = $this->CI->keyword_model->get_keyword_with_stat_by_params(
            $k_params,
            array('baidu_id' => $this->sem_id, 'date >=' => $params['start_time'], 'date <=' => $params['end_time']),
            array('keyword_id', 'keyword', 'status', 'pause', 'unit_id', 'price','pc_destination_url','mobile_destination_url', 'quality', 'quality_reason', 'match_type', 'sum(impression) as impression', '(sum(cost) / sum(click)) as click_avg', 'sum(click) as click', '(sum(click) / sum(impression)) as click_ratio', 'sum(cost) as cost'),
            array('groupby' => 'keyword_id', /*'hash_key' => 'keyword_id', */'offset' => $params['offset'], 'limit' => $params['limit'], 'orderby' => $params['orderby'])
        );

        $this->CI->load->helper('array');
        $this->CI->load->helper('array_util_helper');
        $this->CI->load->model('unit_model');
        
        $unit_ids = array_unique(data_to_array($dbdata, 'unit_id', 'intval'));
        $unit_info = $this->CI->unit_model->get_unit_join_plan_by_params(array('unit_id' => $unit_ids), array('unit_id', 'unit_name', 'max_price'), array('plan_id', 'plan_name', 'price_ratio'), array('hash_key' => 'unit_id'));

        $list = array();
        foreach ($dbdata as $keyword) {
            $info = elements(array('keyword_id', 'keyword', 'status', 'quality','quality_reason', 'pause', 'unit_id', 'match_type','pc_destination_url','mobile_destination_url'), $keyword, '');
            $stats = elements(array('impression', 'cost','price','click','click_avg','click_ratio'), $keyword, 0);
            $info = array_merge($info, $stats);
            $info['unit'] = array('unit_id' => $info['unit_id'], 'name' => $unit_info[$info['unit_id']]['unit_name']);
            $info['plan'] = array('plan_id' => $unit_info[$info['unit_id']]['plan_id'], 'name' => $unit_info[$info['unit_id']]['plan_name']);
            empty($info['price']) && $info['price'] = floatval($unit_info[$info['unit_id']]['max_price']);
            $info['price_ratio'] = floatval($unit_info[$info['unit_id']]['price_ratio']);
            $info['mobile_price'] = $info['price'] * $info['price_ratio'];
            $info['cost'] = round($info['cost'], 2);
            $info['click_ratio'] = 100 * $info['click_ratio'];
            $info['click_avg'] = round($info['click_avg'], 2);
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

    //通过keyword_ids拿到这些keyword所在单元下的全部keyword
    public function get_keywords_in_unit_by_keyword_id($keyword_ids) {
        $unit_ids = $this->CI->keyword_model->get_by_params(array('keyword_id' => $keyword_ids), array('unit_id'));
        $this->CI->load->helper('array_util_helper');
        $unit_ids = array_unique(data_to_array($unit_ids, 'unit_id', 'intval'));
        if (empty($unit_ids)) {
            return array();
        }
        $dbdata = $this->CI->keyword_model->get_by_params(array('unit_id' => $unit_ids), array('keyword_id'));
        return array_unique(data_to_array($dbdata, 'keyword_id', 'intval'));
    }

    //获取关键词列表，添加竞价词用
    public function keyword_list($params,$type="list")
    {
        if(empty($params))
            return array();
    
        if($type == 'list')
        {
            $col = array('keyword_id','keyword','bid_status');
            return $this->CI->keyword_model->get_keyword($params,$col);
        }
        else
        {
            $col = array('count(keyword_id) as count');
            $res = $this->CI->keyword_model->get_keyword($params,$col);
            return empty($res[0]['count']) ? 0 : $res[0]['count'];
        }
    }

    public function bid_alive($user_ids)
    {
        if(empty($user_ids))
            return FALSE;

        $params = array('user_id'=>$user_ids,'bid_status !='=>'1');
        $col = 'count(1) as count';
        //取5个库的数据
        for($count = 0;$count < SWAN_DB_COUNT;++$count)
        {
            $info = $this->CI->keyword_model->get_by_param($count,$params,$col);
            if(!empty($info[0]['count']))
                return TRUE;
        }
        return FALSE;
    }
}
