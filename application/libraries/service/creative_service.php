<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Creative_service {
    
    private $CI;
    private $user_id;
    private $sem_id;
	
	public function __construct($params) {
	    $this->CI =& get_instance();
        $this->CI->load->model('creative_model');
        $this->CI->load->library('baidu_service', $params);
        
        $this->user_id = $params['user_id'];
        $this->sem_id = $params['sem_id'];
	}

    public function add_creative($unit_id, $title, $params) {
        if (empty($unit_id) || empty($title)) {
            return FALSE;
        }          

        $baidu_result = $this->CI->baidu_service->baidu_creative_add($unit_id, $title, $params);
        if ($baidu_result['header']['error_code'] != 0 || empty($baidu_result)) {
            return $baidu_result['header'];
        }

        $db_params = array(
            'creative_id'           => $baidu_result['body']['creative_id'],
            'user_id'               => $this->sem_id,
            'title'                 => $title,
            'unit_id'               => $unit_id,
            'description1'          => $baidu_result['body']['description1'],
            'description2'          => $baidu_result['body']['description2'],
            'pc_destination_url'    => $baidu_result['body']['pc_destination_url'],
            'pc_display_url'        => $baidu_result['body']['pc_display_url'],
            'mobile_destination_url'=> $baidu_result['body']['mobile_destination_url'],
            'mobile_display_url'    => $baidu_result['body']['mobile_display_url'],
            'pause'                 => $baidu_result['body']['pause'],
            'status'                => $baidu_result['body']['status'],
        );
        
        $this->CI->creative_model->insert($db_params);
        return TRUE;
    }

    public function delete_creative($creative_ids) {
        $baidu_result = $this->CI->baidu_service->baidu_creative_delete($creative_ids);
        if ($baidu_result['header']['error_code'] != 0 || empty($baidu_result)) {
            return $baidu_result['header'];
        }

        //若单元下没有有效创意，则暂停此单元下所有关键词的智能竞价
        $unit_ids = $this->CI->creative_model->get_unit_by_creative($creative_ids); 

        $db_params = array(
            'user_id' => $this->sem_id,
            'creative_id' => $creative_ids,
        );

        $this->CI->creative_model->delete_params($db_params);

        if (!empty($unit_ids)) {
            $this->CI->load->library('service/operation_association_service');
            foreach ($unit_ids as $unit_id) {
                $creatives = $this->CI->creative_model->get_creative_count_by_unit($unit_id['unit_id'], array('pause' => 0)); 
                if (empty($creatives)) {
                    //pause the keyword autobid status in this unit
                    $this->CI->operation_association_service->associate_keyword_bid_status(0, $unit_id['unit_id']);             
                }
            }
        }

        return TRUE;
    }

    public function update_creative($creatives) {
        if (empty($creatives)) {
            return FALSE;
        } 
        
        $baidu_result = $this->CI->baidu_service->baidu_creative_update($creatives);
        if ($baidu_result['header']['error_code'] != 0 || empty($baidu_result)) {
            return $baidu_result['header'];
        }

        $this->CI->load->library('service/operation_association_service');

        foreach ($baidu_result['body']['creatives'] as $creative) {
            $db_params = array(
                'unit_id'               => $creative['adgroup_id'],
                'title'                 => $creative['title'],
                'description1'          => $creative['description1'],
                'description2'          => $creative['description2'],
                'pc_destination_url'    => $creative['pc_destination_url'],
                'pc_display_url'        => $creative['pc_display_url'],
                'mobile_destination_url'=> $creative['mobile_destination_url'],
                'mobile_display_url'    => $creative['mobile_display_url'],
                'pause'                 => $creative['pause'],
                'status'                => $creative['status'],
            );
            $condition = array('creative_id' => $creative['creative_id']);
            $this->CI->creative_model->update_params($db_params, $condition);
            if ($db_params['pause'] == 1) {
                $creatives = $this->CI->creative_model->get_creative_count_by_unit($creative['adgroup_id'], array('pause' => 0)); 
                if (empty($creatives)) {
                    //pause the keyword autobid status in this unit
                    $this->CI->operation_association_service->associate_keyword_bid_status(0, $creative['adgroup_id']);             
                }
            }
        }
        return TRUE;
    }

    public function get_list($params) {
        $k_params = array('user_id' => $this->sem_id);
        !empty($params['unit_id']) && $k_params['unit_id'] = $params['unit_id'];

        $dbdata = $this->CI->creative_model->get_creative_with_stat_by_params(
            $k_params,
            array('baidu_id' => $this->sem_id, 'date >=' => $params['start_time'], 'date <=' => $params['end_time']),
            array('creative_id', 'title', 'description1', 'description2', 'pc_destination_url', 'pc_display_url', 'mobile_destination_url', 'mobile_display_url', 'status', 'pause', 'unit_id', 'sum(impression) as impression', '(sum(cost) / sum(click)) as click_avg', 'sum(click) as click', '(sum(click) / sum(impression)) as click_ratio', 'sum(cost) as cost'),
            array('groupby' => 'creative_id', /*'hash_key' => 'creative_id',*/ 'offset' => $params['offset'], 'limit' => $params['limit'], 'orderby' => $params['orderby'])
        );

        $this->CI->load->helper('array');
        $this->CI->load->helper('array_util_helper');
        $this->CI->load->model('unit_model');
        $unit_ids = array_unique(data_to_array($dbdata, 'unit_id', 'intval'));
        $unit_info = $this->CI->unit_model->get_unit_join_plan_by_params(array('unit_id' => $unit_ids), array('unit_id', 'unit_name'), array('plan_id', 'plan_name'), array('hash_key' => 'unit_id'));
        $list = array();
        foreach ($dbdata as $keyword) {
            $info = elements(array('creative_id', 'title', 'description1', 'description2', 'pc_destination_url', 'pc_display_url', 'mobile_destination_url', 'mobile_display_url', 'status', 'unit_id', 'pause'), $keyword, '');
            $stats = elements(array('impression', 'cost','price','click','click_avg','click_ratio'), $keyword, 0);
            $info = array_merge($info, $stats);
            $info['unit'] = array('unit_id' => $info['unit_id'], 'name' => $unit_info[$info['unit_id']]['unit_name']);
            $info['plan'] = array('plan_id' => $unit_info[$info['unit_id']]['plan_id'], 'name' => $unit_info[$info['unit_id']]['plan_name']);
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
        $count = $this->CI->creative_model->get_by_params($k_params, array('count(*) as count'));
        $count = array_shift($count);
        return isset($count['count']) ? $count['count'] : '0';
    }

}
