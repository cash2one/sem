<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Questionnaire_res_model extends CI_Model {

    private $database = 'phoenix';
    private $table = 't_questionnaire_res';

    public function __Construct()
    {
        parent::__Construct();
    }

    public function add($data)
    {
        if(empty($data))
            return FALSE;
		$conn = $this->databases->{$this->database}->master;
        $res = $conn->insert($this->table, $data); 
        return $res;
    }

    public function update($user_id,$data)
    {
        if(empty($data) || empty($user_id))
            return FALSE;
		$conn = $this->databases->{$this->database}->master;
        $conn->where('user_id',$user_id);
        $res = $conn->update($this->table, $data); 
        return $res;
    }
}

