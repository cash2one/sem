<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class package_service
{
    private $CI;
    const APPLY_NEW_PACKAGE = 1;
    const APPLY_UPGRADE_PACKAGE = 2;
    const APPLY_RENEW_PACKAGE = 3;
    const PKG_APPLYING = 1;
    const PKG_APPLIED = 2;
	
    public function __construct()
    {
	    $this->CI = & get_instance();
        $this->CI->load->model('enterprise_sem_user_model');
        $this->CI->load->model('package_model');
        $this->CI->load->model('keyword_model');
        $this->CI->load->library('sms/sms_service');
        $this->CI->load->library('service/stat/common_service');
    }


    /* *
     * 计算次购买申请所需金额
     * */
    public function calc_total($user_id, $apply_type, $apply_package_id)
    {
        $info = $this->CI->package_model->all_package();
        $date = date('Y-m-d', time());
        $this->CI->load->helper('date_helper');
        $packages = array();
        foreach ($info as $value) {
            $packages[$value['id']] = $value;
        }

        if ($apply_type == self::APPLY_NEW_PACKAGE) {
            // 新开通套餐
            return $packages[$apply_package_id]['money']; 
        } else if ($apply_type == self::APPLY_UPGRADE_PACKAGE) {
            // 套餐升级
            $opened_pkg_info = $this->CI->enterprise_sem_user_model->opened_package_info($user_id, $date);

            if ( ! empty($opened_pkg_info)) {
               $apply_pkg_money = $packages[$apply_package_id]['money'];
               $opened_pkg_money = $opened_pkg_info[0]['money'];
               $expiration_date = $opened_pkg_info[0]['expiration_date'];
               return 
                   round(
                       ($apply_pkg_money/365 - 
                        $opened_pkg_money/365
                       ) *
                       (date_minus(
                            $expiration_date,
                            $date)
                       )
                   );
            } else {
                //FATAL ERROR
                return -1;
            }
        } else if ($apply_type == self::APPLY_RENEW_PACKAGE){
            // 套餐续费
            return $packages[$apply_package_id]['money']; 
        } else {
            // FATAL ERROR
            return -1;
        }
    }


    public function add($user_id, $apply_type, $apply_package_id, $money)
    {
        $apply_time = date('Y-m-d H:i:s', time());
        $data = array(
            'userid' => $user_id,
            'apply_type' => $apply_type,
            'apply_package' => $apply_package_id,
            'opening_package' => $apply_package_id,
            'apply_time' => $apply_time,
            'money' => $money,
            'buy_status' => 0
            );
        return $this->CI->package_model->insert_package_apply($data);
    }
    
    
    public function is_package_id_legal($apply_package_id)
    {
        $packages = $this->CI->package_model->all_package();
        foreach($packages as $value) {
            if ($value['id'] == $apply_package_id) {
                return TRUE;
            }
        }
        return FALSE;
    }


    public function is_apply_type_legal($apply_type)
    {
        if ($apply_type != self::APPLY_NEW_PACKAGE
            AND $apply_type != self::APPLY_UPGRADE_PACKAGE
            AND $apply_type != self::APPLY_RENEW_PACKAGE) {
            return FALSE;
        }
        return TRUE;
    }


    public function is_apply_valid($apply_type,$apply_package_id,$user_id)
    {
        $date = date('Y-m-d', time());
        $applying_package_info = $this->CI->enterprise_sem_user_model->applying_package_info($user_id);
        $opened_package_info = $this->CI->enterprise_sem_user_model->opened_package_info($user_id, $date);

        if ( ! empty($applying_package_info)) {
            return array(FALSE,1,'已提交购买申请，不能多次申请');
        }

        if ( ! empty($opened_package_info)) {
            if ($apply_type == self::APPLY_NEW_PACKAGE) {
                return array(FALSE,2,'已开通竞价词包服务');
            } else if ($apply_type == self::APPLY_UPGRADE_PACKAGE) {
                $opened_package_id = $opened_package_info[0]['id'];
                if ($apply_package_id <= $opened_package_id) {
                    return array(FALSE,3,'不能升级到低于已开通等级的套餐');
                }
                return array(TRUE,self::APPLY_UPGRADE_PACKAGE,'');
            } else {
                $opened_package_id = $opened_package_info[0]['id'];
                if ($apply_package_id != $opened_package_id) {
                    return array(FALSE,4,'只能续费当前套餐');
                }
                return array(TRUE,self::APPLY_RENEW_PACKAGE,'');
            }
        }
        return array(TRUE,self::APPLY_NEW_PACKAGE,'');
    }
    
    
    /* *
     * 获取套餐信息
     * */
    public function pkg_info($user_id, $baidu_id)
    {
        if(empty($user_id)) {return array();}
        $date = date('Y-m-d', time());
        
        $opened_pkg_info = $this->CI->enterprise_sem_user_model->opened_package_info($user_id, $date);
        $all_pkg_info = $this->CI->package_model->all_package();
        $recommend_pkg_info = $this->recommend_pkg($user_id, $baidu_id);
        foreach($all_pkg_info as $key=>$value) {
            if ($value['id'] == $recommend_pkg_info['id']) {
                $all_pkg_info[$key]['recommend'] = 1;
            }
        }

        $upgrade_pkg_info = array();
        $renew_pkg_info = array();
        if ( ! empty($opened_pkg_info)) { 
            foreach($all_pkg_info as $value) {
                if ($value['id'] > $opened_pkg_info[0]['id']) {
                    $upgrade_pkg_info[] = $value;
                }
                if ($value['id'] == $opened_pkg_info[0]['id']) {
                    $renew_pkg_info = $value;
                }
            }
            foreach($upgrade_pkg_info as $key=>$value) {
                if ($value['id'] == ($opened_pkg_info[0]['id'] + 1)) {
                    $upgrade_pkg_info[$key]['recommend'] = 1;
                }
                $total = $this->calc_total(
                    $user_id,
                    self::APPLY_UPGRADE_PACKAGE,
                    $value['id']);
                $paid_total = $this->calc_paied_total($opened_pkg_info[0], $date);
                // 申请套餐实际所需金额
                $upgrade_pkg_info[$key]['total'] = $total;
                $upgrade_pkg_info[$key]['paid_total'] = $paid_total;
                $upgrade_pkg_info[$key]['expiration_date'] = $opened_pkg_info[0]['expiration_date'];
            }
        }

        $res = array();
        $res['all_pkg_info'] = $all_pkg_info;
        $res['upgrade_pkg_info'] = $upgrade_pkg_info;
        $res['renew_pkg_info'] = $renew_pkg_info;

        return $res;
    }


    public function recommend_pkg($user_id, $baidu_id)
    {
        $all_pkg_info = $this->CI->package_model->all_package();
        $info = $this->CI->keyword_model->get_effective_keyword_count(
            $baidu_id);
        $count = isset($info[0]['count']) ? $info[0]['count'] : 0;

        $larger_than_count_pkg = array();
        $max_pkg = array();
        $max_count = 0;
        foreach($all_pkg_info as $value) {
            $distance = $value['id'] - $count;
            if ($distance >= 0) {
                $larger_than_count_pkg[] = $value;
            }

            if($value['bid_keyword_num'] > $max_count) {
                $max_count = $value['bid_keyword_num'];
                $max_pkg = $value;
            }
        }

        if (empty($larger_than_count_pkg)) {
            // 如果没有套餐关键词数大于用户已有关键词数，
            // 则推荐关键次数最多的套餐
            return $max_pkg;
        } else {
            $min_count = $max_count ; 
            $min_pkg = $max_pkg;
            foreach ($larger_than_count_pkg as $value) {
                if($value['bid_keyword_num'] < $min_count) {
                    $min_count = $value['bid_keyword_num'];
                    $min_pkg = $value;
                }
            }
            return $min_pkg;
        }
    }


    public function notify($user_id)
    {
        $date = date('Y-m-d H:i:s', time());
        $auditor_info 
            = $this->CI->enterprise_sem_user_model->auditor_info($user_id);
        $au_email = $auditor_info[0]['email'];
        $au_mobile = $auditor_info[0]['mobile'];
        $user_info 
            = $this->CI->enterprise_sem_user_model->user_info($user_id);
        $username = $user_info[0]['username'];
        $company_name = $user_info[0]['name'];

        $msg = 
            "用户 ".$username." "
            .$company_name
            ." 已于".$date
            ."申请关键词付费服务，请及时跟进处理。"
            .ZTY_MSG_SUFFIX;

        $result = $this->CI->sms_service->mt($au_mobile, $msg);
        $email_conf = array(
            'email_from' => 'zhitouyi@haizhi.com',
            'email_to' => $au_email,
            'email_cc' => '',
            'email_subject' => '智投易付费服务开通申请');
        $this->CI->common_service->send_email($msg, $email_conf);
    }


    private function calc_paied_total($opened_pkg_info, $start_date)
    {
        $opened_pkg_money = $opened_pkg_info['money'];
        $expiration_date = $opened_pkg_info['expiration_date'];
        return
            round(
                (
                    $opened_pkg_money/365
                ) *
                (date_minus(
                    $expiration_date,
                    $start_date)
                )
            );
    }
}


/* End of file. */
