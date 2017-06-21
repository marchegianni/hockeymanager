<?php

/*************************************
 * ROUTING CLASS
 * by: Jakub Rybar (3/1/2014)
 *************************************
 * 
 * Parse URL, and all URL related stuff
 * 
 */
class routing {
    public $url;
    public $class = '';
    public $method = '';
    public $params;
    //public $module_folder = '';


    function __construct() {
        
    }
    
    // get subsection (full/part/nsg) from url
    /*public function getSubsection($url=false) {
        if ($url === false) {
            $url = $this->url;
        }
        preg_match('/^(.{0,}\/)?(full|part|ncg)(\/.{0,})?$/i', $url, $subsection);
        if (empty($subsection)) {
            $return = false;
        } else {
            $return =$subsection[2];
        }
        
        return $return;
    }*/
    

    public function parseUrl() {//TODO: tuto dat getController parsovanie
        
        if (!isset($raw_url) || empty($raw_url)) $raw_url = @$_REQUEST['url'];
        
        // parse URL params
        $this->url = explode('/', $raw_url);
        /*if (count($this->url) > 0 && strlen($this->url[0]) > 0) {
            // unset empty params
            foreach ($this->url as $key => $value) {
                if (empty($value) && $value != 0) {//if ($value != "") {
                    unset($this->url[$key]);
                }
            }
        }*/
        
        if (!empty($this->url)) {
            
            for ($i = 0; $i < count($this->url); $i++) {
                switch ($i) {
                    case 0:
                        $this->class = $this->url[$i];
                        break;
                    case 1:
                        $this->method = $this->url[$i];
                        break;
                    default:
                        $this->params[$i-2] = $this->url[$i];
                        break;
                }
            }
            
            //$this->showUrlDebug();
                        
        }
    }
    
    
    /*public function showUrlDebug() {
        ob_clean();
        echo "<b>URL parsed:</b><pre>";
        var_dump($this);
        echo "</pre>";
        die();
    }*/


}



?>