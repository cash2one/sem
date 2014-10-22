<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stat_model extends CI_Model {

    protected static $database = 'stat';

    public function __Construct()
    {
        parent::__Construct();
    }

    public function insert_batch_agency($insert_data)
    {
        if(empty($insert_data)) {return FALSE;}
        $conn = $this->databases->{self::$database}->master;
        $res = $conn->on_duplicate_batch('sem_agency_stat', $insert_data); 
		return $res;
    }

    public function insert_batch_agent($insert_data)
    {
        if(empty($insert_data)) {return FALSE;}
        $conn = $this->databases->{self::$database}->master;
        $res = $conn->on_duplicate_batch('sem_agent_stat', $insert_data); 
		return $res;
    }

    
    public function insert_batch_branch($insert_data)
    {
        if(empty($insert_data)) {return FALSE;}
        $conn = $this->databases->{self::$database}->master;
        $res = $conn->on_duplicate_batch('sem_branch_stat', $insert_data); 
		return $res;
    }

    
    public function insert_batch_customer($insert_data)
    {
        if(empty($insert_data)) {return FALSE;}
        $conn = $this->databases->{self::$database}->master;
        // 一次插入数据库1000条数据，防止一次插入过多导致失败
        // 的问题。
        $datas = array_chunk($insert_data, 1000);
        foreach ($datas as $data) {
            $conn->on_duplicate_batch('sem_customer_stat', $data);
            sleep(1);
        } 
    }
    
    
    public function insert_batch_admin($insert_data)
    {
        if(empty($insert_data)) {return FALSE;}
        $conn = $this->databases->{self::$database}->master;
        $res = $conn->on_duplicate_batch('sem_admin_stat', $insert_data); 
		return $res;
    }
    
    
    public function insert_batch_sadmin($insert_data)
    {
        if(empty($insert_data)) {return FALSE;}
        $conn = $this->databases->{self::$database}->master;
        $res = $conn->on_duplicate_batch('sem_sadmin_stat', $insert_data); 
		return $res;
    }


    public function update_batch_agency($bid_keyword_stat_data)
    {
        if(empty($bid_keyword_stat_data)) {return FALSE;}
        $conn = $this->databases->{self::$database}->master;
        foreach($bid_keyword_stat_data as $each) {
            $conn->where('stat_date', $each['stat_date']);
            $conn->where('agency_id', $each['agency_id']);
            $conn->update('sem_agency_stat', $each);
        }
        return TRUE;
    }


    public function update_batch_agent($bid_keyword_stat_data)
    {
        if(empty($bid_keyword_stat_data)) {return FALSE;}
        $conn = $this->databases->{self::$database}->master;
        foreach($bid_keyword_stat_data as $each) {
            $conn->where('stat_date', $each['stat_date']);
            $conn->where('agency_id', $each['agency_id']);
            $conn->update('sem_agent_stat', $each);
        }
        return TRUE;
    }
    
    
    public function update_batch_branch($bid_keyword_stat_data)
    {
        if(empty($bid_keyword_stat_data)) {return FALSE;}
        $conn = $this->databases->{self::$database}->master;
        foreach($bid_keyword_stat_data as $each) {
            $conn->where('stat_date', $each['stat_date']);
            $conn->where('agency_id', $each['agency_id']);
            $conn->update('sem_branch_stat', $each);
        }
        return TRUE;
    }
    
    
    public function update_batch_admin($bid_keyword_stat_data)
    {
        if(empty($bid_keyword_stat_data)) {return FALSE;}
        $conn = $this->databases->{self::$database}->master;
        foreach($bid_keyword_stat_data as $each) {
            $conn->where('stat_date', $each['stat_date']);
            $conn->where('admin_id', $each['admin_id']);
            $conn->update('sem_admin_stat', $each);
        }
        return TRUE;
    }
    
    
    public function update_batch_sadmin($bid_keyword_stat_data)
    {
        if(empty($bid_keyword_stat_data)) {return FALSE;}
        $conn = $this->databases->{self::$database}->master;
        foreach($bid_keyword_stat_data as $each) {
            $conn->where('stat_date', $each['stat_date']);
            $conn->update('sem_sadmin_stat', $each);
        }
        return TRUE;
    }


    public function update_batch_customer($bid_keyword_stat_data)
    {
        if(empty($bid_keyword_stat_data)) {return FALSE;}
        $conn = $this->databases->{self::$database}->master;
        
        foreach($bid_keyword_stat_data as $each) {
            $conn->where('stat_date', $each['stat_date']);
            $conn->where('username', $each['username']);
            $conn->update('sem_customer_stat', $each);
        }
        return TRUE;
    }


    public function history_core_user()
    {
        $conn = $this->databases->{self::$database}->slaves;
        $sql = "select distinct baidu_id
            from sem_customer_stat
            where baidu_id != 0
            and is_core_user = 1;";
        $res = $conn->query($sql);
        if (! $res) {return array();}

        return $res->result_array();
    }


    public function history_activate_user()
    {
        $conn = $this->databases->{self::$database}->slaves;
        $sql = "
            select baidu_id, min(activate_bid_time) as activate_bid_time
            from sem_customer_stat
            where activate_bid_time > '0000-00-00 00:00:00'
            and baidu_id != 0
            group by baidu_id;";
        $res = $conn->query($sql);
        if (! $res) {return array();}

        return $res->result_array();
    }
}

/*End of file */
