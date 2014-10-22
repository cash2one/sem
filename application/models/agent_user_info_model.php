<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Agent_user_info_model extends CI_Model {

	private $table = 't_agent_user_info';
	private $database = 'phoenix';


    public function get($id,$col="*")
    {
		if(empty($id))
			return array();

		$conn = $this->databases->{$this->database}->slaves;
        $conn->select($col);
        $conn->from($this->table);
        if(is_array($id))
            $conn->where_in('userid',$id);
        else
            $conn->where('userid',$id);
        $res = $conn->get();

		if(!$res)
			return array();
		return $res->result_array();
    }

}

// END Enterprise_user_model class

/* End of file agent_user_info_model.php */
/* Location: ./application/models/agent_user_info_model.php */

