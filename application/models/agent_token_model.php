<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class agent_token_model extends CI_Model {

	private $table = 't_agent_token';
	private $database = 'phoenix';


    public function get_token($user_id)
    {
		if(empty($user_id))
			return array();

		$conn = $this->databases->{$this->database}->slaves;

        $sql = "select A.* from $this->table as A "
             . "inner join t_enterprise_sem_user as B "
             . "on A.userid = B.owner "
             . "where B.userid = ? ";

        $sql_data[] = $user_id;
		$res = $conn->query($sql,$sql_data);
		if(!$res)
			return array();
		return $res->result_array();
    }

    //占位
    public function get_user_count()
    {
        return array();
    }
}

// END Enterprise_user_model class

/* End of file agent_token_model.php */
/* Location: ./application/models/agent_token.php */

