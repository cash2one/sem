<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . "third_party/mongo/MongoBase.class.php");

class __MongoFavoriteHelper extends MongoModel {
	const _DATABASE_ = 'sem_ranksnap';
}

class Ranksnap_mongo_service {
    private static $db = __MongoFavoriteHelper::_DATABASE_;
    private static $collection = 'html';

    public static function get_snap($keyword_ids) {
        if(empty($keyword_ids))
            return array();

        $fields = array('_id','body','timestamp');
        $result = __MongoFavoriteHelper::getConn()->query(self::$db, self::$collection, array('_id' => array('$in' => $keyword_ids)), $fields); 
        return $result;
    }
}
