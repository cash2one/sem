<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Modify extends CI_Controller {
    private $user_id    = 0;
    private $sem_id     = 0;
    private $creatives  = array();
    
    private function _init() {
        $user_status = Auth_filter::api_check_userid(Auth_filter::current_sem_id());
        if(!isset($user_status[0]) || !$user_status[0]) {
            $this->output->set_error($user_status[1], $user_status[2]);
            return FALSE;
        }
        $this->user_id = Auth_filter::current_userid();
        $this->sem_id = Auth_filter::current_sem_id();

        if (empty($_REQUEST['creative_ids']) || empty($_REQUEST['fields']) || empty($_REQUEST['type'])) {
            $this->output->set_error(1003, 'necessary params required');
            return FALSE;
        }
        $creative_ids = explode(',', trim($_REQUEST['creative_ids']));

        $fields = explode(',', $_REQUEST['fields']);
        $type  = intval($_REQUEST['type']);
        
        if ($type != 1 && $type != 5 && count($fields) > 1) {
            //当fields包含多个字段时，type只能为1
            $this->output->set_error(1001, 'params invalid');
            return FALSE;
        }

        if ($type < 0 || $type > 5) {
            $this->output->set_error(1001, 'params invalid');
            return FALSE;
        }

        $creatives = array();
        $this->load->model('creative_model');
        foreach ($creative_ids as $creative_id) {
            $old = $this->creative_model->get_by_params(
                array('creative_id' => $creative_id), 
                array('creative_id', 'title', 'description1', 'description2', 'pc_destination_url', 'pc_display_url', 'mobile_destination_url', 'mobile_display_url'));

            if (empty($old))
                continue;
            
            $old = current($old);
            $new = $this->replace_creative_with_type($type, $old);
            if (!$new) {
                $this->output->set_error(1001, 'params invalid');
                return FALSE;
            }
            $new['creative_id'] = intval($new['creative_id']);
            $this->creatives[] = $new;
        }
        
        $params = array('user_id' => $this->user_id, 'sem_id' => $this->sem_id);
        $this->load->library('service/creative_service', $params);
        return TRUE;
    }

    private function replace_creative_with_type($type, $creative) {
        $fields = explode(',', trim($_REQUEST['fields']));

        switch ($type) {
            case 1:
                foreach ($fields as $field) {
                    $creative[$field] = str_replace($_REQUEST['str_from'], $_REQUEST['str_to'], $creative[$field]);
                }
                break;
            case 2:
                $creative[$fields[0]] = $_REQUEST['str_to'];
                if (in_array('pause', $fields)) {
                    $creative = array(
                        'creative_id' => $creative['creative_id'],
                        'pause' => intval($creative['pause']),
                    );
                }
                break;
            case 3:
                $creative[$fields[0]] = $_REQUEST['str_to'] . $creative[$fields[0]]; 
                break;
            case 4:
                $creative[$fields[0]] = $creative[$fields[0]] . $_REQUEST['str_to']; 
                break;
            case 5:
                $values = explode(',', trim($_REQUEST['str_to']));
                if (count($fields) != count($values)) {
                    return FALSE;
                }
                $new = array_combine($fields, $values);
                $creative = array_merge($creative, $new);
                break;
            default:
                break;
        }
        return $creative;
    }

    public function index() {
        if (!$this->_init()) {
            return FALSE;
        }

        $result = $this->creative_service->update_creative($this->creatives);
        if ($result !== TRUE) {
            $this->output->set_error($result['error_code'], $result['error_msg']);
            return;
        }
        $this->output->set_json(
            array(
                'status' => 'success',
            )
        );
    }
}
