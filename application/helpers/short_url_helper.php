


<?php
/**
 * 由长连接生成短链接操作
 * 
 * 算法描述：使用6个字符来表示短链接，我们使用ASCII字符中的'a'-'z','0'-'9','A'-'Z'，共计62个字符做为集合。 *                   每个字符有62种状态，六个字符就可以表示62^6（56800235584），那么如何得到这六个字符，
 *           具体描述如下：
 *                1. 对传入的长URL+设置key值 进行Md5，得到一个32位的字符串(32 字符十六进制数)，即16的32次方；
 *        2. 将这32位分成四份，每一份8个字符，将其视作16进制串与0x3fffffff(30位1)与操作, 即超过30位的忽略处理；
 *                3. 这30位分成6段, 每5个一组，算出其整数值，然后映射到我们准备的62个字符中, 依次进行获得一个6位的短链接地址。 *
 */
function shortUrl($long_url, $size = 6) {
    $base = "0123456789";
    
    // 利用md5算法方式生成hash值
    $hex = hash('md5', $long_url = '');
    $hexLen = strlen($hex);
    $subHexLen = $hexLen / 8;
    
    $output = array();
	$base_size = strlen($base);
    for( $i = 0; $i < $subHexLen; $i++ )
    {
        // 将这32位分成四份，每一份8个字符，将其视作16进制串与0x3fffffff(30位1)与操作
        $subHex = substr($hex, $i*8, 8);
        $idx = 0x3FFFFFFF & (1 * ('0x' . $subHex));
        
        // 这30位分成6段, 每5个一组，算出其整数值，然后映射到我们准备的62个字符
        $out = '';
        for( $j = 0; $j < $size; $j++ )
        {
                $val = ($base_size - 1) & $idx;
                $out .= $base[$val];
                $idx = $idx >> floor(30 / $size);
        }
        $output[$i] = $out;
    }
    
    return $output;
}
  
$url = 'http://flyer0126.iteye.com/';
$ret = shortUrl($url);
var_dump($ret);


################ 打印结果 ################
/* array(4) {
        [0]=>
        string(6) "2aEzqe"
        [1]=>
        string(6) "Rj6Bve"
        [2]=>
        string(6) "f2mQvi"
        [3]=>
        string(6) "z2eqYv"
} */
