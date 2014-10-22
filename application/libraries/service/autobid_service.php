<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class autobid_service {
    
    private $CI;
	
	public function __construct() {
	    $this->CI = & get_instance();
        $this->CI->load->model('keyword_model');
        $this->CI->load->model('user_tag_model');
        $this->CI->load->model('monitor_model');
        $this->CI->load->model('autobid_model');
        $this->CI->load->model('account_bind_model');
        $this->CI->load->model('enterprise_sem_user_model');
	}

    //判断关键词是否属于该用户
    public function belong_user($user_id,array $keywords)
    {
        if(empty($user_id) || empty($keywords))
            return FALSE;
        $res = $this->CI->keyword_model->get_keyword_count($user_id,$keywords); 

        if(!empty($res[0]['count']) && $res[0]['count'] == count($keywords))
            return TRUE;

        return FALSE;
    }
    
    public function get_deadline()
    {
        $curr_str = strtotime(date('Y-m-d H:i:s',time()));
        $match_str = strtotime(date('Y-m-d H:30:00',time()));

        if($curr_str >= $match_str)
            return date('Y-m-d H:30:00',time());
        else
            return date('Y-m-d H:00:00',time());
    }

    public function get_keyword_rank($id,$s_time,$e_time)
    {
        if(empty($id) || empty($s_time) || empty($e_time))
            return array();
        
        $res = array();
        $default = array('id'=>$id,'bid'=>'0.00','rank'=>'-2','compete_rank'=>'-1','moni_time'=>'');

        $rank_data = $this->CI->monitor_model->rank_data($id,$s_time,$e_time);

        //填充中间没有的时间
        foreach($rank_data as $value)
        {
            $res[strtotime($value['moni_time'])] = $value;
        }
        $time = strtotime($s_time);
        for($i = 0 ; $i <= 48 ; ++$i)
        {
            if(!array_key_exists($time,$res))
            {
                $res[$time] = $default;
                $res[$time]['moni_time'] = date("Y-m-d H:i:s",$time);
            }
            $time += 1800;
        }
        //排序
        ksort($res);
        return array_values($res);
    }

    public function get_rank_set($id)
    {
        if(empty($id))
            return '';

        $data = $this->CI->autobid_model->get_by_id($id,'alarm_rank');

        return empty($data[0]['alarm_rank']) ? '' : $data[0]['alarm_rank'];
    }

    public function monitor_set($id,$rank)
    {
        if(empty($id))
            return FALSE;
    
        $update_data = array('alarm_rank'=>$rank);
        $condition = array('keyword_id'=>$id);
        return $this->CI->autobid_model->update($update_data,$condition);
    }

    public function monitor_last_time()
    {
        $res = $this->CI->monitor_model->get_last_time();

        return empty($res[0]['last_time']) ? FALSE : $res[0]['last_time'];
    }

    public function last_rank($last_time)
    {
        if(empty($last_time))
            return array();
        
        $res = array();
        //取六个库的数据
        for($count = 0;$count < SWAN_DB_COUNT;++$count)
        {
            $key_info = $this->CI->keyword_model->last_rank($last_time,$count);
            $res = array_merge($res,$key_info);
        }
        
        return $res;
    }

    public function get_hzuser_info($data)
    {
        if(empty($data)) 
            return array();

        foreach($data as $value)
        {
            $ids[] = $value['user_id'];
        }

        $user = $this->CI->account_bind_model->get_hzuser_info(array_unique($ids));
        $res = array();
        foreach($user as $value)
        {
            $res[$value['baidu_id']] = $value['mobile'];
        }
        return $res;
    }

    //获取tag
    public function get_tags($user_id)
    {
        if(empty($user_id))
            return $res;
        
        $res = $this->CI->user_tag_model->get_tag($user_id);

        return $res;
    }

    public function modify_tag($keyword_ids,$tag_id)
    {
        if(empty($keyword_ids))
            return FALSE;

        return $this->CI->keyword_model->modify_tag($keyword_ids,$tag_id);
    }

    public function add_tag($user_id,$tag)
    {
        if(empty($tag) || empty($user_id))
            return FALSE;

        return $this->CI->user_tag_model->add_tag($user_id,$tag);
    }

    public function del_tag($user_id,$tag_id)
    {
        if(empty($user_id) || empty($tag_id))
            return FALSE;

        return $this->CI->keyword_model->delete_tag($user_id,$tag_id);
    }

    public function get_curr_keyword_tag_count($user_id,$tag_id)
    {
        if(empty($user_id) || empty($tag_id))
            return 0;

        $res = $this->CI->keyword_model->get_keyword_tag_count($user_id,$tag_id);
        return empty($res[0]['count']) ? 0 : $res[0]['count'];
    }

    public function exist_tag_id($tag_id)
    {
        if(empty($tag_id))
            return FALSE;

        $res = $this->CI->user_tag_model->get($tag_id);
        
        if(empty($res[0]))
            return FALSE;
        return TRUE;
    }

    public function keywords_filter($keyword_ids,$tag_id)
    {
        $res = array();
        if(empty($keyword_ids))
            return $res;

        $keywords = $this->CI->keyword_model->keywords_filter($keyword_ids,$tag_id);
        foreach($keywords as $value)
        {
            $res[] = $value['keyword_id'];
        }
        return $res;
    }

    //判断关键词的地域是否属于计划
    public function area_belong_plan($keyword_ids)
    {
        if(empty($keyword_ids))
            return FALSE;

        //拿到关键词的地域，计划的地域，用户的地域
        $area = $this->CI->autobid_model->get_all_area($keyword_ids);

        if(empty($area))
            return FALSE;

        $this->CI->load->library('region_service');
        foreach($area as $value)
        {
            //计划没有使用账户的
            if(empty($value['region']))
                $value['region'] = $value['region_target'];
            if(!$this->CI->region_service->area_belong($value['bid_area'],$value['region']))
            {
                return FALSE;
            }
        }

        return TRUE;
    }

    //获取要插入的智能竞价keyword_id
    public function autobid_keywords($user_id,$plan_id,array $unit_ids,array $keyword_ids,array $rest_ids)
    {
        if(empty($plan_id) && empty($unit_ids) && empty($keyword_ids))
            return array('','','');
        if(empty($user_id))
            return array('','','');
        
        //关键词是否属于一个计划
        if(empty($plan_id))
        {
            $params = array('user_id'=>$user_id);
            !empty($unit_ids) && $params['unit_id'] = $unit_ids;
            !empty($keyword_ids) && $params['keyword_id'] = $keyword_ids;
            $col = array("distinct(t_swan_baidu_plan.plan_id) as plan_id");
            $res = $this->CI->keyword_model->get_keyword($params,$col);
            if(empty($res))
                return array('','','keyword_id not exist');
            else if(count($res) != 1)
                return array('','','keyword belong more than two plan');
            
            $plan_id = $res[0]['plan_id'];
        }
        //拿到keyword_ids
        $params = array('user_id'=>$user_id,'plan_id'=>$plan_id);
        !empty($unit_ids) && $params['unit_id'] = $unit_ids;
        !empty($keyword_ids) && $params['keyword_id'] = $keyword_ids;
        $col = array('keyword_id','price','max_price','original_price');
        $keywords_info = $this->CI->keyword_model->get_keyword($params,$col);
        if(empty($keywords_info))
            return array('','','no valid keywords');
        $this->CI->load->helper('array_util');
        $keywords_info = change_data_key($keywords_info,'keyword_id');
        //排除不要的keyword_id
        foreach($rest_ids as $id)
        {
            unset($keywords_info[$id]);
        }

        return array($keywords_info,$plan_id,'');
    }


    /* *
     * 检测是否能继续添加新的关键词
     *  @return
     *      TRUE 可以继续添加
     *      FALSE 无法继续添加
     * */
    public function check_if_can_add($new_keyword_ids, $baidu_id, $user_id)
    {
        $date = date('Y-m-d', time());
        $existed_keyword_ids
            = $this->CI->autobid_model->get_all_autobid_keywords($baidu_id);
        $num = count(array_unique(array_merge(
            $existed_keyword_ids, $new_keyword_ids)));
        $package = $this->CI->enterprise_sem_user_model->opened_package_info($user_id, $date);
        if ( ! empty($package[0])) {
            $limit = $package[0]['bid_keyword_num'];
        } else {
            $limit = 100;
        }
        return $num <= $limit;
    }


    public function clear_autobid_keywords($user_id)
    {
        $keyword_ids = $this->CI->autobid_model->get_all_autobid_keywords($user_id);
        return $this->CI->autobid_model->delete_autobid_keywords($keyword_ids);
    }
}


