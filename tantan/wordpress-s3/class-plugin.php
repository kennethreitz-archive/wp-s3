<?php
class TanTanWordPressS3Plugin {
    var $options;
    var $s3;
    
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
            $options = get_option('tantan_wordpress_s3');
            
            $_POST['options']['key'] = trim($_POST['options']['key']);
            $_POST['options']['secret'] = trim($_POST['options']['secret']);
            
            if (!$_POST['options']['secret'] || ereg('not shown', $_POST['options']['secret'])) {
                $_POST['options']['secret'] = $options['secret'];
            }
            
            update_option('tantan_wordpress_s3', $_POST['options']);
            
            if ($_POST['options']['bucket']) {
                $options = get_option('tantan_wordpress_s3');
                require_once(dirname(__FILE__).'/lib.s3.php');
                $s3 = new TanTanS3($options['key'], $options['secret']);
            
                if (!in_array($_POST['options']['bucket'], $s3->listBuckets())) {
                    if ($s3->createBucket($_POST['options']['bucket'],'public-read')) {
                        $message = "Saved settings and created a new bucket: ".$_POST['options']['bucket'];
                    } else {
                        $error = "There was an error creating the bucket ".$_POST['options']['bucket'];
                    }
                } else {
                    $message = "Saved settings.";
                }
            } else {
                $message = "Saved Amazon S3 authentication information. ";
            }
            if (function_exists('dns_get_record') && $_POST['options']['virtual-host']) {
                $record = dns_get_record($_POST['options']['bucket']);
                if (($record[0]['type'] != 'CNAME') || ($record[0]['target'] != 's3.amazonaws.com')) {
                    $error = "Your DNS doesn't seem to be setup correctly to virtually host the domain <em>".$_POST['options']['bucket']."</em>. Make sure the following entry is added to your DNS: <br /><br />".
                        "<code>".$_POST['options']['bucket']." CNAME s3.amazonaws.com.</code><br /><br /><a href='http://docs.amazonwebservices.com/AmazonS3/2006-03-01/VirtualHosting.html'>More info &gt;</a>";
                }
            }
        }
        $options = get_option('tantan_wordpress_s3');
        if ($options['key'] && $options['secret']) {
            require_once(dirname(__FILE__).'/lib.s3.php');
            $s3 = new TanTanS3($options['key'], $options['secret']);
            if (!($buckets = $s3->listBuckets())) {
                $error = $this->getErrorMessage($s3->parsed_xml, $s3->responseCode);
            }
            
            $s3->initCacheTables();
            
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
        if (!$this->options) $this->options = get_option('tantan_wordpress_s3');
        require_once(dirname(__FILE__).'/lib.s3.php');
        $this->s3 = new TanTanS3($this->options['key'], $this->options['secret']);
        

        if ($this->options['key'] && $this->options['secret'] && $this->options['bucket']) {
            $paged = array();
	        $args = array('prefix' => ''); // this doesn't do anything in WP 2.1.2
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
        //wp_enqueue_script('prototype');
        if (!$this->options) $this->options = get_option('tantan_wordpress_s3');

        $accessDomain = $this->options['virtual-host'] ? $this->options['bucket'] : $this->options['bucket'].'.s3.amazonaws.com';
        
        include(dirname(__FILE__).'/admin-tab-head.html');
    }
    function upload_files_upload() {
        // javascript here to inject javascript and allow the upload from to post to amazon s3 instead
    }
    function upload_files_tantan_amazons3() {
	/*
	[newfile] => Array
      (
            [name] => anchor.png
            [type] => image/png
            [tmp_name] => /tmp/phpzbrxnH
            [error] => 0
            [size] => 523
        )
		*/
		if (is_array($_FILES['newfile'])) {
			$file = $_FILES['newfile'];
	        if (!$this->options) $this->options = get_option('tantan_wordpress_s3');
	        require_once(dirname(__FILE__).'/lib.s3.php');
	        $this->s3 = new TanTanS3($this->options['key'], $this->options['secret']);

			$this->s3->putObjectStream($this->options['bucket'], $_GET['prefix'].$file['name'], $file);
		}
		if ($_POST['newfolder']) {
			if (!$this->options) $this->options = get_option('tantan_wordpress_s3');
	        require_once(dirname(__FILE__).'/lib.s3.php');
	        $this->s3 = new TanTanS3($this->options['key'], $this->options['secret']);

			$this->s3->putPrefix($this->options['bucket'], $_POST['prefix'].$_POST['newfolder']);
			//echo ($this->options['bucket']. " : ". $_POST['prefix'].$_POST['newfolder']);
		}
    }
    function tab() {
        $offsetpage = (int) $_GET['paged'];
        if (!$offsetpage) $offsetpage = 1;
        
        if (!$this->options['key'] || !$this->options['secret']) {
            return;
        }
        $bucket = $this->options['bucket'];
        $accessDomain = $this->options['virtual-host'] ? $this->options['bucket'] : $this->options['bucket'].'.s3.amazonaws.com';
        
        $prefix = $_GET['prefix'] ? $_GET['prefix'] : '';
        
        list($prefixes, $keys, $meta, $privateKeys) = $this->getKeys($prefix);
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
    
    // should probably figgure out a way to cache these results to make things more speedy
    function getKeys($prefix) {
        $ret = $this->s3->listKeys($this->options['bucket'], false, urlencode($prefix), '/');//, false, 's3/', '/');
        
        if ($this->s3->responseCode >= 400) {
            return array();
        }
        $keys = array();
        $privateKeys = array();
	    $prefixes = array();
	    $meta = array();
	    if ($this->s3->parsed_xml->CommonPrefixes) foreach ($this->s3->parsed_xml->CommonPrefixes as $content) {
	        $prefixes[] = (string) $content->Prefix;
	    }

	    if ($this->s3->parsed_xml->Contents) foreach ($this->s3->parsed_xml->Contents as $content) {
	        $key = (string) $content->Key;
	        if ($this->isPublic($key)) $keys[] = $key;
	        else {
				if (!($p1 = ereg('^\.', $key)) && 
					!($p2 = ereg('_\$folder\$$', $key)) &&
					!($p3 = ereg('placeholder.ns3', $key))) {
					$privateKeys[] = $key;
				} elseif ($p2) {
					$prefix = ereg_replace('(_\$folder\$$)', '/', $key);
					if (!in_array($prefix, $prefixes)) $prefixes[] = $prefix;
				} else {
					
				}
			}
	    }
	    if ($this->options['permissions'] == 'public') {
			foreach ($privateKeys as $key) {
				$this->s3->setObjectACL($this->options['bucket'], $key, 'public-read');
				$keys[] = $key;
			}
		}

	    foreach ($keys as $i => $key) {
	        $meta[$i] = $this->s3->getMetadata($this->options['bucket'], $key);
	    }
		natcasesort($keys);
		natcasesort($prefixes);
		//print_r($prefixes);
	    //print_r($keys);
	    //print_r($meta);
	
		return array($prefixes, $keys, $meta, $privateKeys);
    }
    
    function isPublic($key) {
        $everyone = 'http://acs.amazonaws.com/groups/global/AllUsers';
        $this->s3->getObjectACL($this->options['bucket'], $key);
        $acl = (array) $this->s3->parsed_xml->AccessControlList;
        if (is_array($acl['Grant'])) foreach ($acl['Grant'] as $grant) {
            $grant = (array) $grant;
            if ($grant['Grantee'] && (ereg('AllUsers', (string) $grant['Grantee']->URI))) {
                $perm = (string) $grant['Permission'];
                if ($perm == 'READ' || $perm == 'FULL_CONTROL') return true;
            }
        }

        
    }
}
?>