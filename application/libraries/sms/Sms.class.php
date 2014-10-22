<?php

//require_once(ROOT_PATH . 'util/Curl.class.php');
require_once dirname(__FILE__) . '/../../util/Curl.class.php';


if (!defined('SMS_SN')) {
    define('SMS_SN', 'DXX-BBX-010-18171');
}

if (!defined('SMS_PWD')) {
    define('SMS_PWD', '7d5AfA-A');
}

class Sms {

    private $sn = '';
    private $pwd = '';
    private $host = 'http://sdk2.entinfo.cn:8060';
    
    public function __construct($sn = SMS_SN, $pwd = SMS_PWD) {
        $this->sn   = $sn;
        $this->pwd  = $pwd;
    }

    private function encodePWD() {
        return strtoupper(md5($this->sn . $this->pwd));
    }

    public function mt($mobile = array(), $content = '', $ext = '', $stime = '', $rrid = '') {
        if (empty($mobile) || empty($content)) {
            return FALSE;
        }

        $mobile = is_array($mobile) ? implode(',', $mobile) : $mobile;
        $content = @iconv('UTF-8', 'gb2312//IGNORE', $content);
        $params = array(
            'sn'            => $this->sn,
            'pwd'           => $this->encodePWD(),
            'mobile'        => $mobile,
            'content'       => $content,
            'ext'           => $ext,
            'stime'         => $stime,
            'rrid'          => $rrid,
        );

        $curl = new Curl();
        $curl->setNeedHeader(FALSE);
        $xml = $curl->post($this->host . '/webservice.asmx/mt', $params);
        preg_match('/<string xmlns=\"http:\/\/tempuri.org\/\">(.*)<\/string>/', $xml, $str);

        if (empty($str) || !isset($str[1])) {
            return FALSE;
        }
        return $str[1];
    }
}


