<?php  

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['sem_ranksnap'] = array(
    '0' => array(
            'host' => '172.16.34.112',
            'port' => '27017',
            'replica_set' => TRUE,
        ),
    '1' => array(
            'host' => '172.16.34.113',
            'port' => '27017',
            'replica_set' => TRUE,
        ),
 /*   '2' => array(
            'host' => '172.16.34.114',
            'port' => '27017',
            'replica_set' => TRUE,
        ),*/
);
            

