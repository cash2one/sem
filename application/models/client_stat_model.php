<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Client_stat_model extends CI_Model {

	private $database = 'phoenix';
	private $table = 't_sem_statistic_log';
    
    public function __Construct()
    {
        parent::__Construct();
    }

    /* *
     * 统计SEM客户端下载量和安装量
     * @params:
     *      date_from 开始统计的日期
     *      date_to 结束统计的日期
     *      version 统计的版本
     * @return
     *      结果集，可能为空。
     *
     * */
    public function stat_download_and_install_total($date_from, $date_to, $version)
    {
        $conn = $this->databases->{$this->database}->slaves;

        $sql = "
            SELECT A.DAY, A.version, A.city, A.DOWNLOAD, A.DISCOUNT, B.INSTALL
            FROM
                (SELECT DATE(time) AS DAY,version,city,COUNT(*) AS DOWNLOAD,COUNT(DISTINCT ip) AS DISCOUNT FROM t_sem_statistic_log WHERE type = 'download' AND DATE(time)<='$date_to' AND DATE(time)>='$date_from' GROUP BY version,city, DAY) A
            LEFT JOIN
                (SELECT DATE(time) AS DAY,version,city,COUNT(*) AS INSTALL FROM t_sem_statistic_log WHERE type = 'install' GROUP BY version,city, DAY) B
            ON
                A.DAY=B.DAY AND A.version=B.version And A.city=B.city
            ORDER BY A.DAY DESC, B.INSTALL DESC, A.DOWNLOAD DESC";

        $res = $conn->query($sql);

		if(!$res)
			return array();
        $result = $res->result_array();

        // 根据版本号过滤结果
        if (isset($version)) {
            return array_filter($result, function($item) use ($version){
                return is_array($item) && $item['version'] === $version;
            });
        }
        return $result;
    }

    /* *
     * 统计SEM客户端安装量和操作系统分布
     *
     * */
    public function stat_install_total($date_from, $date_to, $version)
    {
        $conn = $this->databases->{$this->database}->slaves;

        $sql = "
            SELECT DATE(time) AS DAY,version,os,city,COUNT(*) AS INSTALL FROM t_sem_statistic_log WHERE type = 'install' AND DATE(time)<='$date_to' AND DATE(time)>='$date_from' GROUP BY version,city,DAY,os ORDER BY DAY DESC, INSTALL DESC";

        $res = $conn->query($sql);

		if(!$res)
			return array();
        $result = $res->result_array();

        // 根据版本号过滤结果
        if (isset($version)) {
            return array_filter($result, function($item) use ($version){
                return is_array($item) && $item['version'] === $version;
            });
        }
        return $result;
    }
}

/* End of file client_stat_model.php */
/* Location: ./application/models/client_stat_model.php */
