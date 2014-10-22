<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Competitor_service 
{
    private $CI;
  
    public function __construct() 
    {
        $this->CI = & get_instance();
        $this->CI->load->model('keyword_model');
        $this->CI->load->model('keyword_competitor_model');
        $this->CI->load->model('competitor_model');
        $this->CI->load->model('competitor_stat_model');
        $this->CI->load->helper('array_util_helper');
    }

    public function get_list($params) 
    {
        $res = array();
        if(empty($params))
            return $res;

        //获取关键词列表
        $key_list = $this->CI->keyword_model->track_keyword_list($params);
        $key_list = change_data_key($key_list,'keyword_id');
        //获取跟踪对手id
        $track_ids = $this->CI->keyword_competitor_model->competitor_data(array_keys($key_list));
        //获取跟踪对手基础数据
        $track = $this->CI->competitor_model->get(array_keys(change_data_key($track_ids,'competitor_id')));
        //获取当前排名数据
        $rank = $this->CI->competitor_stat_model->rank_data(array_keys($track));

        $count = 0;
        foreach($key_list as $value)
        {
            $res[$count] = $value;
            $res[$count]['competitors'] = array();
            $i = 0;
            foreach($track_ids as $val)
            {
                if($value['keyword_id'] == $val['keyword_id'])
                {
                    $res[$count]['competitors'][$i]['competitor_id'] = $val['competitor_id'];
                    $res[$count]['competitors'][$i]['domain'] = $track[$val['competitor_id']]['domain'];
                    $res[$count]['competitors'][$i]['track_area'] = $track[$val['competitor_id']]['track_area'];
                    $res[$count]['competitors'][$i]['rank'] = empty($rank[$val['competitor_id']]['rank']) ? '' : $rank[$val['competitor_id']]['rank'];

                    ++$i;
                }
            }
            ++$count;
        }
        
        return $res;
    }

    public function get_list_count($params) 
    {
        if(empty($params))
            return $res;
        
        $res = $this->CI->keyword_model->track_keyword_list($params,TRUE);
        return empty($res[0]['count']) ? 0 : $res[0]['count'];
    }

    //获取关键词的排名情况
    public function keyword_rank($keyword_id,$s_time,$e_time)
    {
        if(empty($keyword_id) || empty($s_time) || empty($e_time))
            return array();
        

        $condition = array('type'=>'2','time >='=>$s_time,'time <='=>$e_time);
        $data = $this->CI->competitor_stat_model->get_rank(array($keyword_id),$condition);
        $res = $this->_fill_rank($keyword_id,$data,$s_time);

        return $res;
    }

    //获取跟踪对手排名情况
    public function competitor_rank($keyword_id,$s_time,$e_time)
    {
        if(empty($keyword_id) || empty($s_time) || empty($e_time))
            return array();
        
        //获取跟踪对手id
        $track_ids = $this->CI->keyword_competitor_model->competitor_data(array($keyword_id));
        if(empty($track_ids))
            return array();

        //获取跟踪对手排名数据
        $condition = array('type'=>'1','time >='=>$s_time,'time <='=>$e_time);
        $track_ids = change_data_key($track_ids,'competitor_id');
        $data = $this->CI->competitor_stat_model->get_rank(array_keys($track_ids),$condition);
        //根据竞争对手id，拿到域名
        $id_to_domain = $this->CI->competitor_model->get_by_param('id,domain',array('id'=>array_keys($track_ids)),'id');
        //按竞争对手分组
        $split_data = array();
        foreach($data as $value)
        {
            $split_data[$value['id']][] = $value;
        }
        $res = array();
        foreach($split_data as $key=>$value)
        {
            $res[$key] = $this->_fill_rank($key,$value,$s_time,$id_to_domain[$key]['domain']);
        }

        return $res;
    }

    //填充排名
    private function _fill_rank($id,$data,$time,$name = '')
    {
        if(empty($id) || empty($data) || empty($time))
            return array();

        $default_rank = '-2';
        $interval = 3600;
        $res = array();
        foreach($data as $value)
        {
            $res[strtotime($value['time'])] = $value;
        }
        $time = strtotime($time);
        for($i = 0 ; $i <= 24 ; ++$i)
        {
            if(!array_key_exists($time,$res))
            {
                $res[$time]['id'] = $id;
                $res[$time]['time'] = date("Y-m-d H:i:s",$time);
                $res[$time]['rank'] = $default_rank;
            }
            if(!empty($name))
                $res[$time]['name'] = $name;
            
            $time += $interval;
        }
        //排序
        ksort($res);
        return array_values($res);
    }

    public function update_status($status,$condition)
    {
        if(empty($status) || empty($condition))
            return FALSE;

        $update_data = array('competitor_status'=>$status);
        return $this->CI->keyword_model->update_status($update_data,$condition);
    }

    //判断一个用户下的开启追踪关键词数是否达到上限
    public function reach_limit($keyword_ids,$user_id)
    {
        if(empty($keyword_ids) || empty($user_id))
            return FALSE;
        
        //获取开启过跟踪对手的关键词id
        $params = array(
                'user_id'=>$user_id,
                'competitor_status != '=>'1',
            );
        $cols = array('keyword_id');
        $track_keyword = $this->CI->keyword_model->get_keyword_list($params,$cols,array('hash_key'=>'keyword_id'));

        $total_keyword = array_merge($keyword_ids,array_keys($track_keyword));
        
        $count = count(array_unique($total_keyword));

        return ($count > COMPETITOR_KEYWORD_MAX) ? TRUE : FALSE;
    }

    //插入并获取跟踪对手id
    public function insert_get_id($keyword_ids,$domains,$area)
    {
        if(empty($keyword_ids) || empty($domains) || empty($area))
            return array();

        //根据keyword_id获取关键词文字
        $params = array('keyword_id'=>$keyword_ids);
        $cols = array('keyword_id','keyword');
        $keyword_info = $this->CI->keyword_model->get_keyword_list($params,$cols,array('hash_key'=>'keyword_id'));

        //要插入的数据
        $relation = array();
        $insert_data = array();
        $count = 0;
        foreach($keyword_info as $key=>$value)
        {
            foreach($domains as $val)
            {
                $insert_data[$count]['domain'] = $val;
                $insert_data[$count]['track_area'] = $area;
                $insert_data[$count]['keyword'] = $value['keyword'];
                $sign = $this->_create_competitor_key($val,$area,$value['keyword']);
                $insert_data[$count]['competitor_key'] = $sign;

                $relation[$sign] = $key;
                ++$count;
            }
        }
        //获取数据库中已有的数据，避免重复插入
        $has_competitor_key = $this->CI->competitor_model->get_by_param('competitor_key',array('competitor_key'=>array_keys($relation)),'competitor_key');
        //过滤已有数据
        foreach($insert_data as $key=>$value)
        {
            if(!empty($has_competitor_key[$value['competitor_key']]))
                unset($insert_data[$key]);
        }
        
        //批量插入数据
        $insert_res = $this->CI->competitor_model->insert_batch($insert_data);
        //if(!$insert_res)
            //return array();
        //获取插入的id
        $insert_ids = $this->CI->competitor_model->get_by_param('competitor_key,id',array('competitor_key'=>array_keys($relation)),'competitor_key');

        $res = array();
        foreach($relation as $key=>$value)
        {
            $res[$insert_ids[$key]['id']] = $value; 
        }
        
        return $res;
    }

    private function _create_competitor_key($domain,$area,$keyword)
    {
        if(empty($domain) || empty($area) || empty($keyword))
            return NULL;

        return md5($domain.$area.$keyword);
    }

    public function insert_relation($user_id,$competitor_to_keyword)
    {
        if(empty($user_id) || empty($competitor_to_keyword))
            return FALSE;

        $insert_data = array();
        $count = 0;
        foreach($competitor_to_keyword as $key=>$value)
        {
            $insert_data[$count]['keyword_id'] = $value;
            $insert_data[$count]['user_id'] = $user_id;
            $insert_data[$count]['competitor_id'] = $key;
            ++$count;
        }
        $del_data = array_unique(array_values($competitor_to_keyword));
    
        return $this->CI->keyword_competitor_model->insert_batch($insert_data,$del_data);
    }
}
