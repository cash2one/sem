<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


if ( ! function_exists('encode_password'))
{
	function encode_password($u, $p)
	{
        return md5(md5($p));
	}
}


if ( ! function_exists('encode_password_old'))
{
	function encode_password_old($u, $p)
	{
        $u = strtolower($u);
        $up = $u . md5($p);
        for ($i = 0; $i < 1000; $i++) {
            $up = sha1($up);
        }   
        return $up;
	}
}
