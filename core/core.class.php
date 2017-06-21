<?php

/*************************************
 * CORE CLASS
 * by: Marek Juhar (15/11/2016)
 *************************************
 * 
 * Core class managing the manipulation with the modules (classes).
 * 
 * 
 * --------------
 * Functionality:
 * --------------
 * 1. Ensuring that all the needed classes are being INCLUDED (by require_once)
 *    and if needed also LOADED (instance created):
 *      
 *      CORE MODULES (their classes are situated in the ./core/{core_module_name}/ folder)
 *      - try to load in constructor of core.class.php (this file) - via loadModules() method, 
 *        if core class not included, hmAutoload() function is called (which is in the autoload stack),
 *        which tries to load(include) when undefined class is called.
 *      
 *      MODULES (their classes are situated in the ./modules/{module_name}/ folder)
 *      - NOT included automaticaly, just when the module is called
 *        (by __get() method and there by loadModule() method) 
 *      - loaded (instance created) by __get() function (in the core class, here), 
 *        which is called when some module is referred in the code
 *        (e.g.: core::getInstance()->{some_module}->{some_method}())
 *        or by method loadModule()
 *      - there are few (not only one) rules where the module can be stored and how it is called 
 *        (details in reading from URL params - core.class.php/getController() method) 
 *      
 *      GENERAL CLASSES (models, like routers, cards, interfaces etc., situated in ./classes/ folder)
 *      - included by hmAutoload() function (which is in the autoload stack),
 *        which tries to load(include) when undefined class is called.
 *      - instance of general class is NOT created automaticaly. developer needs to define it where it is needed!
 * 
 * 
 * 2. Processing which module(class) and which method is going to be executed:
 *      
 *      URL params are delimitered by '/' character. Two main variables are set, that define what the application will do:
 *      
 *      1.  class             - name of the class to be called
 *      2.  method            - name of the method to be called
 *      3.  params (optional) - other URL parameters, passed via URL 
 *                              (separated by '/' character, after first two variables are set)
 * 
 *      URL params are parsed in constructor and class/method is set in core->getController() 
 *      according to the "complicated" rules :) see for yourself down below somewhere in the getController() method.
 *       
 * 
 * 3. Other core related functionality
 *      
 *      Global functions of core methods. E.g. shortcut CWR() definition
 * 
 * 
 * 4. Autoload function definition
 * 
 *      more details in the function somewhere below
 *  
 */
final class core {
    public static $instance;
    public $loaded_modules;
    public $controller;
    
    function __construct() {
        /* 
         * IMPORTANT:
         * ---------- 
         * don't call here any method in which HM()->... is called,
         * because by calling CWR the core constructor is also called 
         * and infinite loop of recursion is started
         */
        /*
        var_dump($_SERVER['REQUEST_URI']);
        if ($_SERVER['REQUEST_URI'] == "/cowrie/trunk/?url=") {
            header("Location: ".DEFAULT_URL_ROOT."");
        }
        */
        
        // load core modules (classes), except controller
        $this->loadModules(DEFAULT_PATH_CORE, array('core', 'controller'));
        
        // include config
        require_once(DEFAULT_PATH_CONFIG."/config.inc.php");
        
        // parse URL
        $this->routing->parseUrl();
    }

    /*
     * in case some module is not loaded(its instance is not created), this function does that
     */
    function __get($module){
        
        if (! $this->isModuleLoaded($module)){
            $this->loadModule($module);
        } 
        return $this->{$module};
    }
    
    private function isModuleLoaded($module_name) {
        return isset($this->$module_name);
    }
    
    /*
     * Load modules
     * Not core modules, because this method is used just in '__get()', 'getController()' and 'hmAutoload()' methods,
     * which are used just for dealing with modules in ./modules/ and ./classes/ folders (not core modules).
     */
    public function loadModule($module) {
        // if subclass (class that is not stored in the folder named NOT the same as the class),
        // we use this for classes like full_pe... etc. that are stored in modules/routersIGN/
        /*if (strlen($this->url->module_folder) > 0 && 
            is_file(DEFAULT_PATH_MODULES."/{$this->url->module_folder}/{$module}.class.php")) {
            require_once(DEFAULT_PATH_MODULES."/".$this->url->module_folder."/".$module.".class.php");
        
        // or try to find class one folder up
        } elseif (strlen($this->url->module_folder) > 0 && 
            is_file(DEFAULT_PATH_MODULES."/{$this->url->module_folder}/../{$module}.class.php")) {
            require_once(DEFAULT_PATH_MODULES."/{$this->url->module_folder}/../{$module}.class.php");
        
        // otherwise it is assumed that the class is stored in the folder with the same name as class' name
        } else {*/
        // this is just for elseif case
        $module_location = $this->searchClass($module);

        if (is_file(DEFAULT_PATH_CLASSES."/{$module}/{$module}.class.php")) {
            require_once(DEFAULT_PATH_CLASSES."/{$module}/{$module}.class.php");
        
        // really last chance to find module in all possible locations, where modules are stored
        }
        elseif (is_file($module_location."/{$module}.class.php")) {
            require_once($module_location."/{$module}.class.php");
            
        // if module not found, then show ERROR message
        }
        else {
            HM()->home->setMessage(ERR_MODULE_NOT_RECOGNIZED.$module.".");
            header("Location: ".DEFAULT_URL_ROOT);
        }
        //}
                
        $this->loaded_modules[$module] = 1;
        $this->{$module} = new $module();
    }


    // search for wanted class in all possible locations (not module, because module needs to have a directory, but class doesn't have to have a directory)
    public function searchClass($class, $locations=array(/*DEFAULT_PATH_MODULES, */DEFAULT_PATH_CORE, DEFAULT_PATH_CLASSES)) {
        foreach ($locations as $loc) {
            if (file_exists($loc."/".$class.".class.php")) {
                return $loc;
            
            } elseif ($dirs = @scandir($loc)) {
                foreach ($dirs as $kdir=>$dir) {
                    $dirs[$kdir] = $loc."/".$dir;
                    if (!is_dir($loc."/".$dir) || $dir == "." || $dir == "..") {
                        unset($dirs[$kdir]);
                    }
                }
                $dirs = array_merge($dirs);
                if ($subsearch = $this->searchClass($class, $dirs)) {
                    return $subsearch;
                }
                
            }
        }
        
        return false;
    } 
    
    // search for wanted module in all possible locations
    public function classExists($class) {
        if ($this->searchClass($class) !== false) {
            return true;
        } else {
            return false;
        }
    }
    

    /*
     * Create instance of the module classes (in $this->{module_name}) that are in ./{$_dir_path}/modules/ folder,
     * exclude classes in $exceptions.
     */
    private function loadModules($dir_path, $exceptions=array()) {
        $handle = opendir($dir_path.'/');
        while (false !== ($module = readdir($handle))) {
            if ($module != "." && $module != ".." &&    // not a hidden folder 
                substr($module, 0, 1) != "_" &&         // not disabled (by '_' character before directory module name)
                is_dir($dir_path.'/'.$module) &&        // class has folder
                !in_array($module, $exceptions) &&      // not excluded in exceptions
                class_exists($module))                  // class has to exist (if not, hmAutoload is called by default - see description of the class_exists() function)
            {
                $this->loaded_modules[$module] = 1;
                // class is included automaticaly by hmAutoload autoload function
                $this->{$module} = new $module();
            }
        }
        closedir($handle);
    }
    
    /*
     * Return own instance. Fine way of calling the main core of the application.
     */ 
    static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new core();
        }
        return self::$instance;
    }
    

    /*
     * Find out which controller(module/class) to call
     */ 
    function getController(){
        // if for some reason we define controller (module/class), we want to execute it in advance
        if (isset($this->controller)) {
            return $this->controller;
        }
        
        if (strlen($this->url->class) == 0) {
            $controller_name = DEFAULT_CONTROLLER_NAME;
        } else {
            $controller_name = $this->routing->class;
        }
        
        if ($this->isModuleLoaded($controller_name)) {
            return $this->$controller_name;
        } else {
            $this->loadModule($controller_name);
            return $this->$controller_name;
        }
    }

}

function hmAutoload_getSpecificSubfolders4generalClass() {
    $specific_dirs = scandir(DEFAULT_PATH_CLASSES);
    foreach ($specific_dirs as $dkey => $d) {
        if (in_array($d, array(".", "..")) || !is_dir(DEFAULT_PATH_CLASSES."/".$d)) {
            unset($specific_dirs[$dkey]);
        }
    }
    return array_merge($specific_dirs);
}


/*
 * if !class_exists($class) then try to load it from various locations: ./core/{core_class_name}/ or ./classes/, or others...
 */
function hmAutoload($class) {
    
    // if some class is missing, try to load it from ./core/{class_name}/
    if (is_file(DEFAULT_PATH_CORE."/".$class."/".$class.".class.php") ) {
        require_once(DEFAULT_PATH_CORE."/".$class."/".$class.".class.php");
    // if some class is missing, try to load it from ./classes
    }
    /*elseif (is_file(DEFAULT_PATH_CLASSES."/".$class.".class.php") ) {
        
        // if it's here, than it might also be in some of the subfolders, so let's find out if the specific subfolder for this module exists
        $specific_subfolder_path = "";
        $specific_subfolders = hmAutoload_getSpecificSubfolders4generalClass();
        // if the specific subfolder for this module (from url) exists and the needed class is there, then use it, instead of the global one in the /classes/ root folder
        if (!empty(HM()->url->class_ID) && in_array(HM()->url->class_ID, $specific_subfolders) && file_exists(DEFAULT_PATH_CORE."/../classes/".HM()->url->class_ID."/".$class.".class.php")) {
            $specific_subfolder_path = "/".HM()->url->class_ID;
        }
        
        require_once(DEFAULT_PATH_CORE."/../classes".$specific_subfolder_path."/".$class.".class.php");
    // if some class is missing, try to load it from ./modules/{class_name}/{class_name}.class.php
    }*/ elseif (is_file(DEFAULT_PATH_CLASSES."/".$class."/".$class.".class.php") ) {
        require_once(DEFAULT_PATH_CLASSES."/".$class."/".$class.".class.php");
    
    // try locations set in get_include_path()
    } elseif (extAutoload($class)) {
        // class loaded in extAutoload
        
        
    
    // if nothing above helped (usually routersIgn, or gateway, etc.), try loadModule(), 
    // because there are conditions how to load modules in folder with name that is different 
    // than the class name (full_..., part_...)
    } else {
        HM()->loadModule($class);
    }
    
}

/*
 * 
 */
function setAutoloadPath($path) {
    set_include_path(get_include_path().PATH_SEPARATOR.$path);
}

/*
 *  if class not found in Cowrie folders/modules (logic in hmAutoload), then try locations from get_include_path(), where might be some paths for external libraries
 */
function extAutoload($class) {
    $return = false;
    
    // for second if, try logic for phpseclib (SSH library)
    $class_parts = explode("_", $class);
    
    $locations = explode(PATH_SEPARATOR, get_include_path());
    
    foreach ($locations as $lkey => $l) {
        if (is_file($l."/".$class.".php") ) {
            $return = true;
            require_once($l."/".$class.".php");
            
        // try phpseclib (SSH library) logic of class/folder
        } elseif (!empty($class_parts) && is_array($class_parts) && count($class_parts) == 2 && is_file($l."/".$class_parts[0]."/".$class_parts[1].".php") ) {
            $return = true;
            require_once($l."/".$class_parts[0]."/".$class_parts[1].".php");
            
        }
    }
    
    
    //var_dump($locations); die();
    
    return $return;
    
}
// define autoload function into autoload stack
spl_autoload_register('hmAutoload');


/*
 * Definition of "shortcut"
 */
function HM() {
    return core::getInstance();
}


// quick debug function
/*function debugMe($die=false) {
    //var_dump(debug_backtrace());
    echo "<table>";
    foreach (debug_backtrace() as $dkey => $d) {
        echo 
        "<tr>
            <th colspan='2'>".$dkey.":</th>
        </tr>
        <tr>
            <th>File: </th>
            <td>".$d['file']."</td>
        </tr>
        <tr>
            <th>Line: </th>
            <td>".$d['line']."</td>
        </tr>
        <tr>
            <th>Function: </th>
            <td>".$d['function']."</td>
        </tr>";
        if (isset($d['class'])) {
        echo "
        <tr>
            <th>Class: </th>
            <td>".$d['class']."</td>
        </tr>";
        }
    }
    echo "</table>";
    
    if ($die) {
        die();
    }
}*/



?>