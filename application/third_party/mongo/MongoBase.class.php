<?php

abstract class MongoModel {
    public static function getConn() {
        $class = get_called_class();
        $database = $class::_DATABASE_;
        return MongoBase::getConn($database);
    }
}

class MongoBase {
    private $conn = false;
    private $config = array();

    /**
     * Singleton.
     */
    public static function getConn($database) {
        static $singleton = NULL;
        is_null($singleton) && $singleton = new MongoBase($database);
        return $singleton;
    }

    private function getConfig($database) {
        $CI = & get_instance();
        $CI->load->config('mongo');
        $config = $CI->config->item($database);

        if(empty($config))
        {
            show_error('Invalid Mongodb config: '.$database);
            exit;
        }
        $servers = array();
        foreach ($config as $server) {
            $servers[] = $server['host'] . ':' . $server['port'];
        }
        $replica_set = $config[0]['replica_set'];

        $this->config = array('servers' => $servers, 'replica_set' => $replica_set);
        return $this->config;
    }

    function __construct($database) {
        $config = $this->getConfig($database);
        try {
            $this->conn = new Mongo('mongodb://' . implode(',', $config['servers']), array("replicaSet" => $config['replica_set']));
        } catch (Exception $exc) {
            throw new Exception('can not connect to the mongodb:' . print_r($config, TRUE));
        }
    }

    function __destruct() {
        if ($this->conn !== false) {
            $this->conn->close();
        }
    }

    public function query($db, $collection, $query, $fields, $sort = false, $skip = false, $limit = false) {
        $coll = $this->conn->$db->$collection;
        if ($this->config['replica_set'] !== false) {
            MongoCursor::$slaveOkay = true;
            $cursor = $coll->find($query, $fields)->slaveOkay();
        } else {
            $cursor = $coll->find($query, $fields);
        }

        if ($sort !== false) {
            $cursor->sort($sort);
        }
        if ($skip !== false) {
            $cursor->skip($skip);
        }
        if ($limit !== false) {
            $cursor->limit($limit);
        }
        $result = array();

        foreach ($cursor as $doc) {
            $result[] = $doc;
        }

        return $result;
    }

    public function count($db, $collection, $query) {
        $coll = $this->conn->$db->$collection;
        $result = $coll->count($query);
        return $result;
    }

    public function batch_insert($db, $collection, $array) {
        $coll = $this->conn->$db->$collection;

        try {
            $coll->batchInsert($array, array('safe' => true, 'continueOnError' => true));
        } catch (Exception $exc) {
            throw new Exception('batch insert failed:' . print_r($array, TRUE) . "\n" . $exc);
        }
    }

    public function insert($db, $collection, $array) {
        $coll = $this->conn->$db->$collection;

        try {
            $coll->insert($array, array('safe' => true));
        } catch (Exception $exc) {
            throw new Exception('insert failed:' . print_r($array, TRUE) . "\n" . $exc);
        }
    }

    public function update($db, $collection, $query, $array, $upsert = false, $multiple = false) {
        $coll = $this->conn->$db->$collection;

        try {
            $coll->update($query, $array, array('upsert' => $upsert, 'multiple' => $multiple));
        } catch (Exception $exc) {
            throw new Exception('update failed:' . print_r($query, TRUE) . "\n" . $exc);
        }
    }

    public function batch_remove($db, $collection, $array, $just_one = false) {
        $coll = $this->conn->$db->$collection;

        try {
            foreach ($array as $item) {
                $coll->remove($item, array('safe' => true, 'justOne' => $just_one));
            }
        } catch (Exception $exc) {
            throw new Exception('batch delete failed:' . print_r($array, TRUE) . "\n" . $exc);
        }        
    }

    public function remove($db, $collection, $array, $just_one = false) {
        $coll = $this->conn->$db->$collection;

        try {
            $coll->remove($array, array('safe' => true, 'justOne' => $just_one));
        } catch (Exception $exc) {
            throw new Exception('delete failed:' . print_r($array, TRUE) . "\n" . $exc);
        }
    }
}
