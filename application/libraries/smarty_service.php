<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(ROOT."/third_party/smarty/Smarty.class.php");

class smarty_service extends Smarty {

    
    function __construct() {
        parent::__construct();

        $this->compile_check = TRUE;
        $this->caching = FALSE;
        
        $this->setTemplateDir(SMARTY_TMP_DIR);
		$this->setCompileDir(SMARTY_COMPILE_DIR);
		$this->setCacheDir(SMARTY_CACHE_DIR);
		$this->setConfigDir(SMARTY_CONFIG_DIR);

        $this->assign('version', PHPUI_VERSION);
        $this->assign('environment', ENVIRONMENT);
    }

    public function view($template_name,$params=array())
    {
        if(empty($template_name))
            return FALSE;
        
        if(is_array($params))
        {
            foreach($params as $key=>$value)
            {
                $this->assign($key,$value);
            }
        }
        $this->display($template_name);
    }
}

