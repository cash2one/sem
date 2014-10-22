<?php

/**
 * 报表des加密解密用
 * @author dingzhu
 *
 */
class Des_service {
	
	/**
	 * des加密
	 * @param unknown_type $encrypt
	 * @return boolean|string
	 */
	public static function des_encrypt($key,$encrypt) {
        if(empty($key) || empty($encrypt))
            return '';
		$CI = & get_instance();
		$iv = mcrypt_create_iv ( mcrypt_get_iv_size ( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB ), MCRYPT_RAND );
		$passcrypt = mcrypt_encrypt ( MCRYPT_RIJNDAEL_256, $key, $encrypt, MCRYPT_MODE_ECB, $iv );
		$encode = base64_encode ( $passcrypt );
		return $encode;
	}
	
	/**
	 * des解密
	 * @param unknown_type $decrypt
	 * @return boolean|string
	 */
	public static function des_decrypt($key,$decrypt) {
        if(empty($key) || empty($decrypt))
            return '';
		$CI = & get_instance();
		$decoded = base64_decode ( $decrypt );
		$iv = mcrypt_create_iv ( mcrypt_get_iv_size ( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB ), MCRYPT_RAND );
		$decrypted = mcrypt_decrypt ( MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_ECB, $iv );
		return $decrypted;
	}
}

?>
