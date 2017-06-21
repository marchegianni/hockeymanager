<?php

/*************************************
 * CONTROLLER CLASS
 * by: Marek Juhar (23/11/2016)
 *************************************
 * 
 * This class is inherited by every page class (classes stored in ./pages/{class_name}/ folders).
 * It is NOT inherited by any general class (stored in ./classes/), NOR by the core classes (stored in ./core/classes/).
 * 
 * --------------
 * Functionality:
 * --------------
 * 
 * 1. Method 'show()' is crucial to the proper running of the whole application.
 *    It processes what method of the selected class(module) should be executed.
 *    This method can be used for final preparation of the output (e.g. global initialization, final smarty commands etc.)
 * 
 * 2. Methods that are shared by all modules can be put here, so that every module can access it. 
 * 
 */
abstract class controller {
    public $default_method = '';
    public $template = '';
    public $full_access_groups = array();
    public $priority_javascripts = array();
    public $priority_stylesheets = array();
    public $javascripts = array();
    public $stylesheets = array();
    public $is_ajax = false;
    
    public $global_testing_mode = false;
    
    // user access rights variables (the logic from 6/2015)
    protected $USER_ACCESS = array('read'=>false, 'write'=>false);
    
    function __construct() {
    }
    
    // when method is not defined, this function will be called
    function __call($method,$arguments){
        // connect to default database
        CWR()->db->connect(DB_DEFAULT_CONNECTION);
        return call_user_func_array(array($this, $method), $arguments);
    }
    
    
    // this method is used to get number of input parameters of called method 
    // (use in automatic passing of the URL parameters to the called method)
    /*protected function __getNumberOfMethodParams($class_name, $method_name) {
        $method = new ReflectionMethod($class_name, $method_name);
        return $method->getNumberOfRequiredParameters();
    }*/
    
    // returns the group access rights for the called section/class/method
    /*protected function getClassMethodAccess($sec, $cl, $me) {
        $acc_types = CWR()->db->getData("    SELECT  `group_id`, GROUP_CONCAT(DISTINCT `access_type`) `access_types` 
                                                FROM    `class_method_in_group` 
                                                WHERE   `section`=?s AND 
                                                        `class`=?s AND 
                                                        `method`=?s 
                                                GROUP BY `group_id`
                                                ORDER BY `group_id`;", array($sec, $cl, $me));
        $access_types = array();
        if ($acc_types !== false) {
            foreach ($acc_types as $atkey => $at) {
                $access_types[$at['group_id']] = explode(",", $at['access_types']);
            }
        }
        
        return $access_types;
    }*/
    
    // check if user has specific access to specific mathod in cowrie
    /*protected function hasUserAccess($section, $class, $method, $acc_type="all", $user_id=false) {
        $has_access = false;
        
        // set correct input access types
        if ($acc_type == "all") {
            $acc_type = array("read", "write");
        } elseif (!is_array($acc_type) && is_string($acc_type) && strlen($acc_type)>0) {
            $acc_type = array($acc_type);
        }
        
        //var_dump(CWR()->user->getUserId());
        $user_groups = CWR()->user->getUserGroupId($user_id);
        //var_dump($user_groups);
        
        // get the access rights for specific method
        $rights = $this->getClassMethodAccess($section, $class, $method);
        //var_dump($rights);
        
        // check the method access rights with user groups
        if (!empty($rights)) {
            $rights_flatten = array();
            foreach ($rights as $gid => $access_types) {
                if (in_array($gid, $user_groups)) {
                    foreach ($access_types as $access_type) {
                        if (!in_array($access_type, $rights_flatten)) {
                            $rights_flatten[] = $access_type;
                        }
                    }
                }
            }
            
            // check if specific access right is set for the user
            if (!empty($rights_flatten)) {
                $cmp = array_diff($acc_type, $rights_flatten);
                if (empty($cmp)) {
                    $has_access = true;
                }
            }
            
        }
        
        return $has_access;
    }*/


    /*
     * Find out what method needs to be executed and call it
     */
    public function show(){

        /*************** LOGIN - check **************/
        $uri = str_replace(APP_SUB_FOLDER, "", $_SERVER['REQUEST_URI']);
        $uri = str_replace("//", "/", $uri);
        if (($uri != '/home/login' && $uri != '/home/guestLogin' && $uri != '/home/passwordRecovery' && $uri != '/home/jsDisabledError') && (!isset($_SESSION['user']) || empty($_SESSION['user'])) && 
            strpos($uri, "/admin/babysitting/ping") === false &&     // babysitting ping
            strpos($uri, "/admin/babysitting/oneLongPing") === false &&     // babysitting ping
            strpos($uri, "/admin/babysitting/runPingChecks") === false &&     // babysitting ping
            strpos($uri, "/admin/babysitting/runMtuSweepTest") === false &&     // babysitting MTU sweep test
            strpos($uri, "/ip/ncg/ceCleaning/getPingStatus4Interfaces") === false &&     // ce cleanings ping checks
            strpos($uri, "/admin/presenceAdmin/sendAbsenceEmail") === false &&     // send weekly absence email
            strpos($uri, "/admin/ipcAdmin/isNextWeekIPCset") === false &&     // check weekly on monday if next week IPC is set
            strpos($uri, "/admin/ipToolBoxAdmin/connectToFirewall") === false &&     // be constatly connected to firewall
            strpos($uri, "/admin/giniDbSync/") === false &&     // giniDbSync
            strpos($uri, "/admin/autoTester/") === false) {     // autoTester module
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI']; // save URL user want to access and after login redirect user there
            header ("Location:".DEFAULT_URL_ROOT.'home/login');
            exit();
        }
        /*************** LOGIN - end ****************/
        
        // connect to default database
        CWR()->db->connect(DB_DEFAULT_CONNECTION);
        
        if (MAINTAINANCE) {
            $this->template = "maintenance";
            CWR()->view->display($this->template.".tpl");
            exit();
        }
        
        // set flag if called request is Ajax or normal
        $method_ID_4ajax = "";
        $auto_ajax = false;
        if (strpos(CWR()->url->method_ID, "Ajax")!==false && method_exists($this,CWR()->url->method_ID)) {
            $this->is_ajax = true;
            $this->template = "";
            
        // set all necesserry flags for autoajax call
        } elseif (  empty(CWR()->url->method_ID) && 
                    isset(CWR()->url->other_url_params[0]) && 
                    is_string(CWR()->url->other_url_params[0]) && 
                    strpos(CWR()->url->other_url_params[0], "Ajax")!==false && 
                    !method_exists($this,CWR()->url->other_url_params[0])) {
            $this->is_ajax = $auto_ajax = true;
            $this->template = "";
            $method_ID_4ajax = trim(str_replace("Ajax", "", CWR()->url->other_url_params[0]));
            CWR()->url->method_ID = $method_ID_4ajax;
            unset(CWR()->url->other_url_params[0]);
            array_merge(CWR()->url->other_url_params);
        }
        
        
        // if initAll needs to be ignored - for test module, for easier testing, ajax request
        if (!$this->is_ajax && (!isset($this->ignoreGlobalInit) || !$this->ignoreGlobalInit)) {
            
            // init all stuff that needs to be called everytime the page reloads
            CWR()->globalInit->initAll($this);
        }

        // classic class->method call
        if (strlen(CWR()->url->method_ID) > 0 && method_exists($this,CWR()->url->method_ID)) {
            $funct = CWR()->url->method_ID;
            
        // ajax call (automated)
        } elseif ($auto_ajax && strlen($method_ID_4ajax) > 0 && method_exists($this,$method_ID_4ajax)) {
            $funct = $method_ID_4ajax;
                        
        } else {
            $funct = $this->default_method;
        }
        
        
        /******************* CHECK USER ACCESS RIGHTS to the called class/method **********************/
        // before we call the desired method, check if user has READ / WRITE acces to this class/method
        // user must be logged in
        if (CWR()->user->isLogged()) {
            $user_groups = CWR()->user->getUserGroupId();
            $class_method_in_groups = $this->getClassMethodAccess(CWR()->url->section_ID, CWR()->url->class_ID, $funct);
            
            $user_has_read_access = false;
            $user_has_write_access = false;
            if (!empty($class_method_in_groups)) {
                foreach ($user_groups as $key => $user_group_id) {
                    // user has at least read access
                    if (array_key_exists($user_group_id, $class_method_in_groups)) {
                        // set read/write access as true, if in this user group access rights array
                        foreach ($class_method_in_groups[$user_group_id] as $atkey => $at) {
                            ${"user_has_".$at."_access"} = true;
                        }
                    }
                }
            }

            // manualy override:
            //  - homepage (every time user has no access to the called class/method it will redirect user to the home page with the error message)
            if (strlen(CWR()->url->class_ID) == 0 || 
                (CWR()->url->class_ID == "home" && strlen(CWR()->url->method_ID) == 0) || 
                (CWR()->url->class_ID == "autoTester")
                ) {
                $user_has_read_access = true;
            }

            // this is new logic of checking if user has read/write access
            $this->USER_ACCESS = array('read'=>$user_has_read_access, 'write'=>$user_has_write_access);
            
            //var_dump($this->USER_ACCESS);
            if (!$user_has_read_access) {
                
                // if Ajax request failed, then simple error message
                if (strpos(CWR()->url->method_ID, "Ajax")!==false) {
                    $this->template="";
                    header('HTTP/1.0 403 Cowrie: NO ACCESS!');
                    echo ERR_NOT_AUTHORISED_ACCESS;
                    exit();
                    return true;
                } else {
                    $this->setMessage('ERR_NOT_AUTHORISED_ACCESS');
                    header ("Location:".DEFAULT_URL_ROOT.'home');
                    return true;
                }
            }
        }
        CWR()->view->assign("USER_ACCESS", $this->USER_ACCESS);
        


        // if URL parameters are set, check if their number is equal or higher to the required arguments of called method
        // (if equal, call the method with these arguments), if not invalid URL.
        $funct_arguments = array();
        if (!empty(CWR()->url->other_url_params)) {
            if (count(CWR()->url->other_url_params) >= $this->__getNumberOfMethodParams(get_class($this), $funct)) {
                $funct_arguments = CWR()->url->other_url_params;
            } else {
                $this->setMessage('ERR_INVALID_URL');
                CWR()->home->redirect2lastUrl(true);
            }
        }

        
        // variable $result stores the returned value of the selected method, if it would return something at all
        // (by default it's NULL, when nothing is returned)
        $result = NULL;
        if (empty($funct_arguments)) {
            $result = $this->$funct();
        } else {
            $result = call_user_func_array(array($this, $funct), $funct_arguments);
        }



        /***************** MESSAGES / NOTIFICATIONS ******************/
        // this can not be in globalInit class, because it needs to be called after module is executed
        // if message was set
        if (isset($_SESSION['cwr_message'])) {
            $this->showMessage($_SESSION['cwr_message']);
        }
        // if notification was set
        if (isset($_SESSION['cwr_notification'])) {
            $this->showNotification($_SESSION['cwr_notification']);
        }

        
        // if autoajax call
        if ($this->is_ajax && $auto_ajax) {
            header_remove();
            $this->template = "";
            echo json_encode($result);
            exit();
            
        } elseif (isset($this->template) && strlen($this->template)>0){
            
            // if showing intro include JS/CSS for it automaticaly
            if ($this->template == "intro" || $this->template == "home") {
                $this->addCss(DEFAULT_URL_ROOT."css/intro.css");
                $this->addJs(DEFAULT_URL_ROOT."jscripts/intro.js");
            }
            
            
            /* 
             * AUTOMATIC js/css include
             * only if template is set this action is done, 
             * otherwise you have to include it before calling display (or smth like that)
             */
            $js_code = $this->generateJsIncludes();
            $css_code = $this->generateCssIncludes();
            CWR()->view->assign('jsIncludesHTML', $js_code);
            CWR()->view->assign('cssIncludesHTML', $css_code);
            
            // display set template
            CWR()->view->cwrDisplay($this->template.".tpl");
        }
        
    }


    protected function setIntroBlocks($main_menu_item, $arr) {
        // get left menu items
        $left_menu = CWR()->menu->getLeftMenu($main_menu_item);
        
        // add them into intro blocks array
        if (!empty($left_menu)) {
            $arr['has_2nd_level_items'] = false;
            foreach($left_menu as $key=>$item) {
                $ar = array("title" => $item['title'], "icon_class" => $item['icon_class'], "links" => array());
                
                // check if has children
                if ($item['has_children'] > 0) {
                    $ar['multiple_links'] = true;
                    // set all children
                    foreach($item['sub'] as $subkey => $subitem) {
                        $ar['links'][] = array("title" => $subitem['title'], "link" => $subitem['url']);
                    }
                    
                // if current item has no children set link of current item
                } else {
                    $ar['multiple_links'] = false;
                    $ar['link'] = $item['url'];
                }
                if ($ar['multiple_links']) {
                    $arr['has_2nd_level_items'] = true;
                }
                                
                $arr['blocks'][] = $ar;
                
            }
        }
                
        return $arr;
    }
    
    
    // sets message into session
    public function setMessage($msg) {
        $_SESSION['cwr_message'] = $msg;
    }
    
    // set all variables that ensures that the message is shown
    public function showMessage($msg) {
        CWR()->view->assign('show_msg_box', true);
        CWR()->view->assign('msg_box_type', substr($msg, 0, 3));
        if (defined($msg)) {
            CWR()->view->assign('message', constant($msg));
        } else {
            CWR()->view->assign('message', $msg);
        }
        unset($_SESSION['cwr_message']);
    }

    // sets notification into session
    public function setNotification($msg) {
        $_SESSION['cwr_notification'] = $msg;
    }

    // set all variables that ensures that the notification is shown
    public function showNotification($msg) {
        if (defined($msg)) {
            CWR()->view->assign('notification', constant($msg));
        } else {
            CWR()->view->assign('notification', $msg);
        }
        unset($_SESSION['cwr_notification']);
    }
    
    public function showAjaxError($error) {
        if ($this->is_ajax) {
            //ob_clean();
            header_remove();
            $this->template = "";
            header('HTTP/1.0 403 Cowrie: Ajax ERROR: '.$error);
            exit();
        }
        return true;
    }
    
    // - finds JS and CSS called by the same name as module class-input argument, and includes it
    // - called in constructor of every module
    // - returns true, if at least one of the js/css files exists and is included
    protected function addDefaultScripts($class=false) {
        // if class not set, take the called class
        if (!$class) {
            $class = CWR()->url->class_ID;
        }
        
        $ret = false;
        
        // get path to the class
        $path = CWR()->searchClass($class);
        
        // if JS file exists, add it to the js array to be included
        if (file_exists($path."/".$class.".js")) {
            $this->addJs(DEFAULT_URL_ROOT.$path."/".$class.".js");
            $ret = true;
        } elseif (file_exists($path."/jscripts/".$class.".js")) {
            $this->addJs(DEFAULT_URL_ROOT.$path."/jscripts/".$class.".js");
            $ret = true;
        }

        // if CSS exists, add it to the css array to be included
        if (file_exists($path."/".$class.".css")) {
            $this->addCss(DEFAULT_URL_ROOT.$path."/".$class.".css");
            $ret = true;
        } elseif (file_exists($path."/css/".$class.".css")) {
            $this->addCss(DEFAULT_URL_ROOT.$path."/css/".$class.".css");
            $ret = true;
        }
        
        return $ret;
    }


    // add JS in array, that will later be included automaticaly into HTML code
    // if second argument priority set to TRUE, then JS is added into separate array (these JSs will be included prior to the rest of JSs)
    public function addJs ($src, $priority=FALSE){
        if ($priority) {
            $this->priority_javascripts[] = $src;
        } else {
            $this->javascripts[] = $src;
        }
    }
    // add JS that will be included with the $if statement (used for javascripts loaded only in specific IE versions)
    public function addJsForIE($src, $if) {
        $this->javascripts[] = array($src, $if);
    }
    
    
    
    // add CSS in array, that will later be included automaticaly into HTML code
    // if second argument priority set to TRUE, then CSS is added into separate array (these CSSs will be included prior to the rest of CSSs)
    public function addCss ($src, $priority=FALSE){
        if ($priority) {
            $this->priority_stylesheets[] = $src;
        } else {
            $this->stylesheets[] = $src;
        }
    }
    
    // returns stamp of last modification of the css/js file (used for solving issue with browser caching js/css)
    private function getMstamp($file) {
        $file = str_replace(DEFAULT_URL_ROOT, "", $file);
        return date("ymd_His", filemtime($file));
    }
    
    // generates HTML code for JS includes 
    public function generateJsIncludes (){
        if (is_array($this->priority_javascripts) && is_array($this->javascripts)) {
            $generated = "";
            // first generate priority JSs
            foreach ($this->priority_javascripts as $key=>$js) {
                // if ie
                if (is_array($js)) {
                    $generated .= '<!--[if '.$js[1].']<script type="text/javascript" src="'.$js[0].'?'.$this->getMstamp($js[0]).'"></script><![endif]-->';
                } else {
                    $generated .= '<script type="text/javascript" src="'.$js.'?'.$this->getMstamp($js).'"></script>';
                }
            }
            // then other JSs follow
            foreach ($this->javascripts as $key=>$js) {
                // if ie
                if (is_array($js)) {
                    $generated .= '<!--[if '.$js[1].']<script type="text/javascript" src="'.$js[0].'?'.$this->getMstamp($js[0]).'"></script><![endif]-->';
                } else {
                    $generated .= '<script type="text/javascript" src="'.$js.'?'.$this->getMstamp($js).'"></script>';
                }
            }
            return $generated;
        }
    }
      
      
    // generates HTML code for CSS includes 
    public function generateCssIncludes (){
        if (is_array($this->priority_stylesheets) && is_array($this->stylesheets)){
            $generated = "";
            // first generate priority CSSs
            foreach ($this->priority_stylesheets as $key=>$css) {
                $generated .= '<link href="'.$css.'?'.$this->getMstamp($css).'" rel="stylesheet" />';
            }

            // then other CSSs follow
            foreach ($this->stylesheets as $key=>$css) {
                $generated .= '<link href="'.$css.'?'.$this->getMstamp($css).'" rel="stylesheet" />';
            }
            return $generated;
        }
    }
    
    // used only in globalInit, because there it's only constructor class that can be used, no other core or other module
    /*public function getSelectedMainMenuItem() {
        $selected = false;
        if (isset(CWR()->url->section_ID)) {
            $selected = CWR()->url->section_ID;
            if ($selected == 'ip' || $selected == 'voice') {
                if (isset(CWR()->url->url[1])) {
                    $selected = CWR()->url->url[1];
                }
            }
        }
        return $selected;
    }*/
    
    
    
    // THIS ACCESS CHECKING LOGIC IS USED ONLY IN OLD LOGIC (until 6/2015) - J. Rybar (e.g. modules like templates, voice pops, ipv6 booking/freeing and bgp community booking/freeing)
    
    // Check if the user has full access to the current module.
    // User groups list is empty by default, so you need to set the $this->full_access_groups property 
    // in the desired class constructor, where you want the user access limitation to be applied.
    // Called anywhere you need to check if the current user has full access (write/edit/read)
    /*public function hasFullAccess($stop_on_noaccess=false) {
        if (!empty($this->full_access_groups)) {
            $has_full_access = FALSE;
            $group_names = CWR()->user->getUserGroupName();
            foreach ($this->full_access_groups as $group) {
                if (!is_array($group_names) && is_string($group_names)) {
                    $group_names = array($group_names);
                } elseif (!is_array($group_names) && !is_string($group_names)) {
                    $group_names = array();
                }
                if (in_array($group, $group_names)) {
                    $has_full_access = TRUE;
                    break;
                }
            }
        
        // if property full_access_groups is empty, that means that no limitation of access is set for the current module/class
        } else {
            $has_full_access = TRUE;
        }
        
        // if input parameter set to true, then stop loading the page and redirect user back to the previous page with an error notice
        if ($stop_on_noaccess && !$has_full_access) {
            $this->setMessage("ERR_NOT_AUTHORISED_ACCESS"); CWR()->home->redirect2lastUrl(); return false;
        }
        
        // otherwise, just return the full access flag value
        return $has_full_access;
    }*/
    
    // set user groups that have full access to the called module
    // if input argument is empty, then all users have full access 
    /*public function setFullAccessGroups($groups) {
        $this->full_access_groups = $groups;
        // define smarty flag for checking if user has full access
        if ($this->hasFullAccess()) {
            CWR()->view->assign('FULL_ACCESS', TRUE);
        }
    }*/
    
    // check if user is working on localhost
    public function isLocalhost() {
        if (DOMAIN == "127.0.0.1") {
            return true;
        } else {
            return false;
        }
    }

    // check if user is working on localhost
    /*public function isTestServer() {
        if (DOMAIN.APP_SUB_FOLDER == CWR_TEST_SERVER) {
            return true;
        } else {
            return false;
        }
    }*/
        
    public function jsDisabledError() {
        $this->template="";

        $this->addCss(DEFAULT_URL_ROOT.'css/login.css');
        
        $css_code = $this->generateCssIncludes();
        CWR()->view->assign('cssIncludesHTML', $css_code);
        
        
        CWR()->view->display('jsDisabledError.tpl');
        die();
    }

    public function redirect2lastUrl($under_construction=FALSE) {
        // set redirect url
        if (isset($_SERVER['HTTP_REFERER']) && strlen($_SERVER['HTTP_REFERER']) > 0) {
            $redirect = $_SERVER['HTTP_REFERER'];
        } else {
            $redirect = DEFAULT_URL_ROOT.'home';
        }

        if ($under_construction) {
            $redirect = DEFAULT_URL_ROOT.'home/underConstruction';
        }

        header("Location: ".$redirect);
        die(); // this is necessery, for example in case of setting message/notification to be shown after reload, it is necessery to stop page execution here, not to unset session variable
    }
    
    // redirects to old cowrie, logs into old cowrie with users credentials from new cowrie and then load the url in old cowrie we want to redirect to
    /*protected function go2oldCowrie($url=false) {
        $this->template = "";
       
        $script = "index.php";
        
        //create array of data to be posted
        $post_data['autologin'] = 1;
        $user = CWR()->db->getData("SELECT `login`, `password` FROM `users` WHERE `id`=?i LIMIT 1", array(CWR()->user->getUserId()));
        $post_data['new_cwr_login'] = $user[0]['login'];
        $post_data['new_cwr_pass'] = $user[0]['password'];
        $post_data['redirect_url'] = ($url !== false ? str_replace("|a|", "&", str_replace("|q|", "?", $url)) : "");
        
        
        // make a "fake" submit form, that will submit automaticaly to the old cowrie ane there it will login and redirect to the url, if set
        echo "<html>
                <head></head>
                <body>
                  <form id='remoteLoginForm' action='http://".DOMAIN."/".$script."' enctype='multipart/form-data' method='post'>";
        //traverse array and prepare data for posting (key1=value1)
        foreach ( $post_data as $key => $value) {
            echo "  <input type='hidden' name='".$key."' value='".$value."' />";
        }
        echo "      <input type='submit' name='submit' value='submit' id='submitForm' style='display:none;' />";
        echo "    </form>
                  <script type='text/javascript'>
                    window.onload = function(){ document.getElementById('submitForm').click(); };
                  </script>
                </body>
              </html>";

        return true;
    }*/
    
    // show under construction image and message
    public function underConstruction() {
        $this->template = 'under_construction';
    }
    
    /*public function setTestingMode($status) {
        if (isset($status) && is_bool($status)) {
            $this->global_testing_mode = $status;
        }
    }*/
    
}


?>