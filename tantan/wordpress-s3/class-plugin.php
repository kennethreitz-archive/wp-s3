<?php
class TanTanWordPressS3Plugin {
    var $options;
    
    function TanTanWordPressS3Plugin() {
        add_action('admin_menu', array(&$this, 'addhooks'));
        add_action('load-upload.php', array(&$this, 'addPhotosTab'));
        add_action('activate_tantan/wordpress-s3.php', array(&$this, 'activate'));
        if ($_GET['tantanActivate'] == 'wordpress-s3') {
            $this->showConfigNotice();
        }
        $this->photos = array();
        $this->albums = array();
        $this->perPage = 1000;
    }
    
    // this should install the javascripts onto the user's s3.amazonaws.com account
    
    function installAjax() {
        $js = array('S3Ajax.js');
    }
    
    function activate() {
        wp_redirect('plugins.php?tantanActivate=wordpress-s3');
        exit;
    }
    function deactivate() {}
    
    function showConfigNotice() {
        add_action('admin_notices', create_function('', 'echo \'<div id="message" class="updated fade"><p>Amazon S3 Plugin for WordPress <strong>activated</strong>. <a href="options-general.php?page=tantan/wordpress-s3/class-plugin.php">Configure the plugin &gt;</a></p></div>\';'));
    }

    function addhooks() {
        add_options_page('Amazon S3', 'Amazon S3', 10, __FILE__, array(&$this, 'admin'));
        $this->version_check();
    }  
    function version_check() {
        global $TanTanVersionCheck;
        if (is_object($TanTanVersionCheck)) {
            $data = get_plugin_data(dirname(__FILE__).'/../wordpress-s3.php');
            $TanTanVersionCheck->versionCheck(668, $data['Version']);
        }
    }
    function admin() {
        if ($_POST['action'] == 'save') {
            if (!is_array($_POST['options'])) $_POST['options'] = array();
            $_POST['options']['key'] = trim($_POST['options']['key']);
            $_POST['options']['secret'] = trim($_POST['options']['secret']);
            
            update_option('tantan_wordpress_s3', $_POST['options']);
            
            if ($_POST['options']['bucket']) {
                $message = "Saved settings.";
            } else {
                $message = "Saved Amazon S3 authentication information. ";
            }
        }
        $options = get_option('tantan_wordpress_s3');
        if ($options['key'] && $options['secret']) {
            require_once(dirname(__FILE__).'/lib.s3.php');
            $s3 = new TanTanS3($options['key'], $options['secret']);
            if (!($buckets = $s3->listBuckets())) {
                $error = $this->getErrorMessage($s3->parsed_xml, $s3->responseCode);
            }
        } elseif ($options['key']) {
            $error = "Please enter your Secret Access Key.";
        } elseif ($options['secret']) {
            $error = "Please enter your Access Key ID.";
        }
        include(dirname(__FILE__).'/admin-options.html');
    }
    function addPhotosTab() {
        add_filter('wp_upload_tabs', array(&$this, 'wp_upload_tabs'));
        add_action('upload_files_tantan_amazons3', array(&$this, 'upload_files_tantan_amazons3'));
        add_action('upload_files_upload', array(&$this, 'upload_files_upload'));
        add_action('admin_print_scripts', array(&$this, 'upload_tabs_scripts'));
    }
    function wp_upload_tabs ($array) {
    /*
        0 => tab display name, 
        1 => required cap, 
        2 => function that produces tab content, 
        3 => total number objects OR array(total, objects per page), 
        4 => add_query_args
	*/
        $this->options = get_option('tantan_wordpress_s3');
        if ($this->options['key'] && $this->options['secret'] && $this->options['bucket']) {
            $paged = array();
	        $args = array(); // this doesn't do anything in WP 2.1.2
            $tab = array(
                'tantan_amazons3' => array('Amazon S3', 'upload_files', array(&$this, 'tab'), $paged, $args),
                //'tantan_amazons3_upload' => array('Upload S3', 'upload_files', array(&$this, 'upload'), $paged, $args),
                );
            return array_merge($array, $tab);
        } else {
            return $array;
        }
    }

    function upload_tabs_scripts() {
        include(dirname(__FILE__).'/admin-tab-head.html');
    }
    function upload_files_upload() {
        // javascript here to inject javascript and allow the upload from to post to amazon s3 instead
    }
    function upload_files_tantan_amazons3() {
    }
    function tab() {
        $offsetpage = (int) $_GET['paged'];
        if (!$offsetpage) $offsetpage = 1;
        
        if (!$this->options['key'] || !$this->options['secret']) {
            return;
        }
        $bucket = $this->options['bucket'];
        
        require_once(dirname(__FILE__).'/lib.s3.php');
        $s3 = new TanTanS3($this->options['key'], $this->options['secret']);
        
        $prefix = $_GET['prefix'] ? $_GET['prefix'] : '';
        //echo urlencode($prefix);
        
        $ret = $s3->listKeys($bucket, false, urlencode($prefix), '/');//, false, 's3/', '/');
        //print_r($ret);
        $keysList = $ret['keys'];
        $prefixes = $ret['prefixes'];
        //print_r($keysList);
        $keys = array();
        if (is_array($keysList)) foreach ($keysList as $key) {
            $path = explode('/', $key->Key);
            $keys = $this->mapKey($keys, $path);
        }
        include(dirname(__FILE__).'/admin-tab.html');
    }
    
    function getErrorMessage($parsed_xml, $responseCode){
    	$message = 'Error '.$responseCode.': ' . $parsed_xml->Message;
    	if(isset($parsed_xml->StringToSignBytes)) $message .= "<br>Hex-endcoded string to sign: " . $parsed_xml->StringToSignBytes;
    	return $message;
    }

    // turns array('a', 'b', 'c') into $array['a']['b']['c']
    function mapKey($keys, $path) {
        $k =& $keys;
        $size = count($path) - 1;
        $workingPath = '/';
        foreach ($path as $i => $p) {
            if ($i === $size) {
                $k['_size'] = isset($k['_size']) ? $k['_size'] + 1 : 1;
                $k['_path'] = $workingPath;
                $k['_objects'][$k['_size']] = $p;
            } else {
                $k =& $k[$p]; // traverse the tree
                $workingPath .= $p . '/';
            }
        }
        return $keys;
    }
}
?>