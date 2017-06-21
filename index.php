<?php

/*************************************
 * INDEX.PHP
 * by: Marek Juhar (15/11/2016)
 *************************************
 * 
 * main file of the Hockey Manager online game
 * 
 */

// where am I? local/test/live server?
/*$subdomain = $db_branch_prefix = "";
if ($_SERVER['HTTP_HOST'] == "127.0.0.1") {
    $this_server = "local";
    $smth = strtolower(str_replace("/", "", substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], "/", 1))));
    if (strpos($smth, "branch_") === 0) {
        $subdomain = "/".$smth;
        $this_server = $smth;
        $db_branch_prefix = $smth."_";
    }
}*/

## SESSION #################################################################################
session_name('hmog');
if (!session_start()) { die('Session could not be started!'); }
## END SESSION #################################################################################


## CORE CONFIG #################################################################################
//debugging stuff
error_reporting(E_ALL);
ini_set("display_errors", 1);

/* 
 * constant definition of the path to the core modules of the application,
 * modules (and their classes), general classes and libs folders
 */
define("DEFAULT_PATH_CORE","./core");
define("DEFAULT_PATH_CONFIG","./config");
//define("DEFAULT_PATH_MODULES","./modules");
define("DEFAULT_PATH_CLASSES","./classes");
define("DEFAULT_PATH_LIBS","./libs");
//define("DEFAULT_PATH_USERDATA", "./userdata");
define("DEFAULT_PATH_TEMPLATES", "./smarty/templates");


// define some more constants, based on which server you are
//define('APP_SUB_FOLDER', $subdomain);
define('DOMAIN', $_SERVER['HTTP_HOST']);
//define('DB_BRANCH_PREFIX', $db_branch_prefix);
define("DEFAULT_URL_ROOT","http://".DOMAIN.'/');

// the URL parsing (find which class/method to call) is done in core constructor


// Requires needed for application to run, including external libs which
// are a stable part of the framework
require(DEFAULT_PATH_LIBS."/smarty-3.1.30/libs/Smarty.class.php");
require(DEFAULT_PATH_CORE."/core.class.php");
## END CORE CONFIG #################################################################################


header('content-type: text/html; charset=utf-8');

/* 
 * Call static method getInstance() of the class core, which will select the controller based on URL parameter 'class_ID'.
 * In case of no URL parameter, DEFAULT_CONTROLLER_NAME is selected.
 * 
 * General way of calling the getInstance() method:
 * core::getInstance()->...
 * 
 * Shortcut (defined in core class file './core/core.class.php'):
 * CWR()->...
 * 
 * Next steps in functionality:
 * - 'getController()' method   - located in core class 'core.class.php' - determines which controller (module) will be executed
 *                                and parses the URL from which it reads also the method and other params
 * - 'show()' method            - located in controller class and thus inherited by class (module) selected by 'getController()' method - determines which class & method
 *                                of the selected class will be executed
 */

HM()->getController()->show();


?>
