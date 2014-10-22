<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common_service {
    
    private $CI;
	
	public function __construct() {
	    $this->CI = & get_instance();
	}

    public function send_email($body,$conf)
    {
        if(empty($body) || empty($conf))
            return FALSE;
        
        $this->CI->load->library('email');

        $config['protocol'] = 'smtp';
        $config['charset'] = 'utf-8';
        $config['mailtype'] = 'html';
        $config['smtp_host'] = 'mail.haizhi.com';
        $config['smtp_user'] = 'server';
        $config['smtp_pass'] = 'hzwj1234';
        $config['smtp_port'] = '25';
        $this->CI->email->initialize($config);

        $this->CI->email->from($conf['email_from']);
        $this->CI->email->to($conf['email_to']); 
        $this->CI->email->cc($conf['email_cc']); 
        $this->CI->email->subject($conf['email_subject']);
        $this->CI->email->message($body); 
        $this->CI->email->send();
        
        return TRUE;
    }

}

?>
