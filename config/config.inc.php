<?php

    // DB connection for Cowrie data DB
    /*$data_db_connect = new mysqlDbConnection();
    $data_db_connect->name = 'information_schema';
    $data_db_connect->host = '127.0.0.1';
    $data_db_connect->user = 'cowrie_3';
    $data_db_connect->password = 'cOwri3@3';
    $data_db_connect->database = 'information_schema';
    $data_db_connect->autoswitch = false;
    $this->db->addConnection($data_db_connect);*/
    
    // DB connection for Cowrie system DB
    $db_connect = new mysqlDbConnection();
    $db_connect->name = 'cowrie_sys';
    $db_connect->host = '127.0.0.1';
    $db_connect->user = 'cowrie_3';
    $db_connect->password = 'cOwri3@3';
    $db_connect->database = DB_BRANCH_PREFIX.'cowrie_sys';
    $db_connect->autoswitch = true;
    $this->db->addConnection($db_connect);
    
    // CONSTANTS definition
    define('DB_DEFAULT_CONNECTION',         'cowrie_sys');
    define('DEFAULT_CONTROLLER_NAME',       'home');
    define('DEFAULT_PAGE_TITLE',            'Hockey Manager');
    /*define('FADING_EFFECT_AUTOTIMEOUT',     1500);
    
    // regular expressions
    define('TEMPLATE_VARIABLE_REGEXP',      '/<([^_]{1}[a-zA-Z0-9_ !@$%\^&\(\)-=\+\[\]{};\'\\:"\|,.\/\?]+[^*]{1})>/');
    
    // other variables
    define('LOCALHOST_MAILER_DISABLED',     true);
    define('MAINTAINANCE',                  false);*/
    
    // mail server connection data
    //define('MAIL_HOST', '10.239.222.172');
    //define('MAIL_USERNAME', 'GVQF0852');    // not used
    //define('MAIL_PASSWORD', 'Cechomor0-0'); // not used
    
    // IPToolBox connection data
    /*define('IPTOOLBOX_USERNAME', 'jrybar');
    define('IPTOOLBOX_PASSWORD', 'JR@obsn3t');
    define('IPTOOLBOX_FULL_USERNAME', 'jakub.rybar');*/
    /*define('IPTOOLBOX_USERNAME', 'mjuhar');
    define('IPTOOLBOX_PASSWORD', 'Cechomor8-8');
    define('IPTOOLBOX_FULL_USERNAME', 'marek.juhar');*/
    /*define('TELNET_USERNAME', 'rsnd_unshut');
    define('TELNET_PASSWORD', '$rNvrI4J');*/
    /*define('IPTB_via_CWR', true); // switch, if on localhost use cowrie server as a mediator to get to ip tool box
    define('COWRIE_IP', '10.238.101.39');
    define('COWRIE_USER', 'cowrie');
    define('COWRIE_PWD', 'c0wri3');
    

    define('CWR_TEST_SERVER',               'cowrie.equant.com/test');*/
    
    // TODO: when whole cowrie is moved to trunk, then this needs to be changed to trunk - it is used in logging in db
    //define('CWR_CONTAINER_FOLDER',          '3.0');

    include_once('./config/msgs.inc.php');

?>