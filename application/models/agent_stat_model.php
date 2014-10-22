<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class agent_stat_model extends CI_Model {

	private $table = 't_mcc_stat';
	private $database = 'phoenix';

	//获取基本信息
	public function get($col="*",$condition = NULL) {

        $conn = $this->databases->{$this->database}->slaves;
        $conn->select($col);
        $conn->from($this->table);
        if(!is_null($condition))
            $conn->where($condition);
        $res = $conn->get();

		if(!$res)
			return array();
		return $res->result_array();
	}

	//获取一周数据
	public function get_complex($col,$s_date,$e_date) 
    {
        if(empty($col))
            return array();
        $condition = array('date >= '=>$s_date,'date <= '=>$e_date);
        $conn = $this->databases->{$this->database}->slaves;
        $conn->select($col);
        $conn->from($this->table);
        $conn->where($condition);
        $conn->group_by("username");
        $res = $conn->get();

		if(!$res)
			return array();
		return $res->result_array();
	}

}

// END user_info_model class

/* End of file agent_stat_model.php */
/* Location: ./application/models/agent_stat_model.php */

