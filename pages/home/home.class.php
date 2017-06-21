<?php

/*************************************
 * HOME CLASS
 * by: Marek Juhar (23/11/2016)
 *************************************
 * 
 * Homepage module, and example for all other modules
 *  
 */
class home extends controller {
    public $default_method = 'showIntro';
    public $template = 'home';
    
    function __construct() {
        //parent::__construct(); // this ensures that all initializations defined in parent's constructor will be inherited
        $this->addDefaultScripts(__CLASS__);
    }
    
    public function showIntro() {
        /*$show_fixed_blocks = false;
        // get IPC, only if user is IP from EMEA
        if (HM()->user->getUserRegion() == "EMEA" && in_array("IP", HM()->user->getUserGroupName())) {
            $ipc = $this->getIPC();
            if ($ipc !== false) {
                $ipc = HM()->user->getUserName($ipc);
                $date = new DateTime;
                HM()->view->assign("week_no", $date->format("W"));
                HM()->view->assign("ipc", $ipc);
                $show_fixed_blocks = true;

                $next_ipc = $this->getIPC("+1");
                if ($next_ipc !== false) {
                    $next_ipc = HM()->user->getUserName($next_ipc);
                    HM()->view->assign("next_ipc", $next_ipc);
                }
            }
        }*/
        
        // show today date
        $show_fixed_blocks = true;
        
        // if user is in IP/VOICE/ADMIN/DEVELOPER group, he/she can see the presenceAdmin widget and cowrie fun widget
        /*$can_user_see_presence_widget = false;
        foreach (HM()->user->getUserGroupName() as $key => $g) {
            if (in_array($g, array("IP", "Voice", "Admin", "Developer", "Scheduling"))) {
                $can_user_see_presence_widget = true;
                break;
            }
        }
                
        if ($can_user_see_presence_widget) {
            
            // show team presence "widget"
            $this->addCss(DEFAULT_PATH_MODULES."/admin/presenceAdmin/presenceAdmin.css");
            HM()->view->assign("show_team_presence_widget", true);
            
            // show cowrie fun image
            $fun_dir = DEFAULT_PATH_MODULES."/../images/fun";
            if (is_dir(DEFAULT_PATH_MODULES."/../images/fun/".date("D")) && count(scandir(DEFAULT_PATH_MODULES."/../images/fun/".date("D")))>2) {
                $fun_dir = DEFAULT_PATH_MODULES."/../images/fun/".date("D");
            }
            $imgs = scandir($fun_dir);
            foreach ($imgs as $ikey => $i) {
                if ($i == "." || $i == ".." || is_dir($fun_dir."/".$i)) {
                    unset($imgs[$ikey]);
                }
            }
            $imgs = array_merge($imgs);
            $fun_img = $imgs[rand(0, count($imgs)-1)];
            HM()->view->assign("cowrie_fun_img", $fun_dir."/".$fun_img);
            $size = getimagesize($fun_dir."/".$fun_img);
            HM()->view->assign("fun_img_width", $size[0]);
            HM()->view->assign("fun_img_height", $size[1]);
            
        }*/
        
        // check if user has access to cwr features updates module
        /*if ($this->hasUserAccess("admin", "cwrFeaturesAdmin", "getFeaturesUpdates", "read")) {
            // get cowrie features updates
            $can_user_see_cwr_updates_admin_link = false;
            foreach (HM()->user->getUserGroupName() as $key => $g) {
                if (in_array($g, array("Admin", "Developer"))) {
                    $can_user_see_cwr_updates_admin_link = true;
                    break;
                }
            }
            HM()->view->assign("show_cwr_updates_module_link", $can_user_see_cwr_updates_admin_link);
            HM()->view->assign("cwr_updates", HM()->cwrFeaturesAdmin->getFeaturesUpdates());
        }*/
        
        HM()->view->assign("show_fixed_blocks", $show_fixed_blocks);

        // get and set user cusomizable blocks
        /*$user_blocks = HM()->menu->getUserShortcuts();
        HM()->view->assign("user_blocks", $user_blocks);*/
        
                    
    }
    
    
    public function login() {
        $this->template = ''; // this means, that default template will not be called 
        if (isset($_REQUEST['login']) && isset($_REQUEST['password'])) {
            HM()->user->login();
        }
        
        // assign login css
        $this->addCss(DEFAULT_URL_ROOT.'css/login.css');
        
        $css_code = $this->generateCssIncludes();
        $js_code = $this->generateJsIncludes();
        HM()->view->assign('cssIncludesHTML', $css_code);
        HM()->view->assign('jsIncludesHTML', $js_code);
        
        
        HM()->view->display('login.tpl');
    }
    
    public function logout() {
        HM()->user->logout();
    }
    
    public function guestLogin() {
        $this->template = ''; // this means, that default template will not be called
        if (!HM()->user->silentLogin("guest", "iamguest", true)) {
            HM()->home->setMessage('ERR_LOGIN_FAILED');
        }

        // assign login css
        $this->addCss(DEFAULT_URL_ROOT.'css/login.css');
        
        $css_code = $this->generateCssIncludes();
        $js_code = $this->generateJsIncludes();
        HM()->view->assign('cssIncludesHTML', $css_code);
        HM()->view->assign('jsIncludesHTML', $js_code);
        
        
        HM()->view->display('login.tpl');
        
    }
    
    // send a new password to the user
    public function passwordRecovery() {
        $this->template = '';
        
        $ret = false;
        if (isset($_REQUEST['login']) && $_REQUEST['login'] != '') {
            $login = $_REQUEST['login'];
            // if someone inputs their whole email address, ignore everything after '@' sign
            $at_pos = strpos($login, "@");
            if ($at_pos !== false) {
                $login = substr($login, 0, $at_pos);
            }
            $pwd = HM()->str->generateRandomString(8);
            $pwd_db = HM()->user->calculateHash($pwd, $login);
            
            if (HM()->db->getBool("UPDATE `users` SET `password`=?s WHERE `login`=?s LIMIT 1;", array($pwd_db, $login))) {
                //compose and send e-mail notification
                $user = array();
                $user['login'] = $login;
                $user['pwd'] = $pwd;
                HM()->view->assign('user', $user);
                
                $recipient = HM()->user->getUserEmail($login);
                $template = "emails/password_generated.tpl";
                $message = HM()->view->fetch($template);
                
                HM()->mailer->send($recipient, "New password generated", $message);
                
                $ret = true;
            }
        }
        echo json_encode($ret);
        exit();
    }
    
    
    public function getIPC($week = false) {
        $ipc = false;
        
        // get week number
        $date = new DateTime;
        if (strlen($week)>0 && intval($week)!==$week) {
            $date->modify($week." week");
        }
        $week = intval($date->format('W'));
        $year = intval($date->format("Y"));
        
        /*
        // get data from old DB
        HM()->db->connect('cowrie_old');
        $ipc = HM()->db->getData(" SELECT  u.`login` 
        
                                    FROM    `users` u LEFT JOIN 
                                            `ipc_euma_schedule` ipc_sch ON u.`id_user`=ipc_sch.`id_user` 
                                            
                                    WHERE   ipc_sch.`week`=?i AND ipc_sch.`year`=?i", array($week, $year));
        //HM()->db->showDebug();
        HM()->db->connect('cowrie_sys');
        */
        
        // get IPC from new Cowrie
        $ipc = HM()->db->getData(" SELECT  u.`login` 
                                            
                                    FROM    `users` u LEFT JOIN 
                                            `user_ipc_schedule` ipc_sch ON u.`id`=ipc_sch.`user_id` 
                                            
                                    WHERE   ipc_sch.`week`=?i AND ipc_sch.`year`=?i", array($week, $year));
        
        
        return (isset($ipc[0]['login']) ? $ipc[0]['login'] : false);
   }
    
    public function getMyShortcutsHTMLAjax() {
        $this->template = "";
  
        // get and set user cusomizable blocks
        $user_blocks = HM()->menu->getUserShortcuts();
        HM()->view->assign("user_blocks", $user_blocks);
  
        $html = HM()->view->fetch("my_shortcuts.tpl");
        
        echo $html;
    }
   
    public function getAddShortcutHTMLAjax() {
        $this->template = "";
        // get menu items of default menu
        $main_menu = HM()->menu->getMenu('Default main menu', 1, 3);

        HM()->view->assign('menu_items', $main_menu);
        $html = HM()->view->fetch("add_my_shortcut.tpl");

        echo $html;
    }
    
    public function addShortcutAjax($menu_item_id) {
        $this->template = "";
        $user_id = HM()->user->getUserId();
        $max_order = HM()->db->getData("SELECT MAX(`order`) max_order FROM `menu_item_in_user` WHERE `user_id`=?i", array($user_id));
        echo json_encode(HM()->db->getBool("INSERT INTO `menu_item_in_user` (`user_id`, `menu_item_id`, `order`) VALUES (?i, ?i, ?i)", array($user_id, $menu_item_id, intval($max_order[0]['max_order'])+1)));
    }
    
    public function reorderMyShortcutsAjax() {
        $this->template = "";
        if (!isset($_REQUEST['ordered_shortcuts'])) {
            echo json_encode(false);
        }
        
        $user_id = HM()->user->getUserId();
        $status = true;
        foreach ($_REQUEST['ordered_shortcuts'] as $skey => $sid) {
            if (!HM()->db->getBool("UPDATE `menu_item_in_user` SET `order`=?i WHERE `user_id`=?i AND `menu_item_id`=?i LIMIT 1", array($skey+1, $user_id, $sid))) {
                $status = false;
            }
        }
        echo json_encode($status);
    }
    
    public function deleteMyShortcutAjax($menu_item_id) {
        $this->template = "";
        $user_id = HM()->user->getUserId();
        echo json_encode(HM()->db->getBool("DELETE FROM `menu_item_in_user` WHERE `user_id`=?i AND `menu_item_id`=?i", array($user_id, $menu_item_id)));
    }
    
    
    
    
    
    
    
    
    
    /************** REDIRECTS to OLD COWRIE **********************/
    
    public function oldHwUpdates() {
        $this->template = "";
        $this->go2oldCowrie("ncg2_new.php?Submit2=submit&choice1=hardware_updates");
    }
    
}


?>