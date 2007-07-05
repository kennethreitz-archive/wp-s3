<?php
/*
Calculates RFC 2104 compliant HMACs.
Based on code from  http://pear.php.net/package/Crypt_HMAC
*/   
class TanTanCrypt_HMAC {
var $_func;var $_ipad;var $_opad;var $_pack;
function TanTanCrypt_HMAC($key, $func = 'md5'){$this->setFunction($func);$this->setKey($key);}
function setFunction($func){if (!$this->_pack = $this->_getPackFormat($func)) { die('Unsupported hash function'); }$this->_func = $func;}
function setKey($key){$func = $this->_func;if (strlen($key) > 64) {$key =  pack($this->_pack, $func($key));}if (strlen($key) < 64) {$key = str_pad($key, 64, chr(0));}$this->_ipad = (substr($key, 0, 64) ^ str_repeat(chr(0x36), 64));$this->_opad = (substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64));}
function _getPackFormat($func){$packs = array('md5' => 'H32', 'sha1' => 'H40');return isset($packs[$func]) ? $packs[$func] : false;}
function hash($data){$func = $this->_func;return $func($this->_opad . pack($this->_pack, $func($this->_ipad . $data)));}
}
/*
class Stream{
var $data;
function stream_function($handle, $fd, $length){return fread($this->data, $length);}
}
*/

require_once (dirname(__FILE__).'/../lib/curl.php');
//require_once(dirname(__FILE__).'/../lib/Request.php');


/*
    based on code provided by Amazon
*/
class TanTanS3 {

	var $serviceUrl;
	var $accessKeyId;
	var $secretKey;
	var $responseString;
	var $responseCode;
	var $parsed_xml;
	var $req;
			
	/**
	 * Constructor
	 *
	 * Takes ($accessKeyId, $secretKey, $serviceUrl)
	 *
	 * - [str] $accessKeyId: Your AWS Access Key Id
	 * - [str] $secretKey: Your AWS Secret Access Key
	 * - [str] $serviceUrl: OPTIONAL: defaults: http://s3.amazonaws.com/
	 *
	*/
	function TanTanS3($accessKeyId, $secretKey, $serviceUrl="http://s3.amazonaws.com/") {
		$this->serviceUrl=$serviceUrl;
		$this->accessKeyId=$accessKeyId;
		$this->secretKey=$secretKey;
		$this->req =& new TanTanCurl($this->serviceUrl);
	}
			
	/**
	 * listBuckets -- Lists all buckets.
	*/
	function listBuckets() {
		$ret = $this->send('');
		if($ret == 200){ 
		    $return = array();
		    foreach ($this->parsed_xml->Buckets->Bucket as $bucket) {
		        $return[] = (string) $bucket->Name;
		        
		    }
		    return $return;
			
		}
		else{
			return false;
		}    
	}	
	/**
	 * listKeys -- Lists keys in a bucket.
	 *
	 * Takes ($bucket [,$marker][,$prefix][,$delimiter][,$maxKeys]) -- $marker, $prefix, $delimeter, $maxKeys are independently optional
	 *
	 * - [str] $bucket: the bucket whose keys are to be listed
	 * - [str] $marker: keys returned will occur lexicographically after $marker (OPTIONAL: defaults to false)
	 * - [str] $prefix: keys returned will start with $prefix (OPTIONAL: defaults to false)
	 * - [str] $delimiter: keys returned will be of the form "$prefix[some string]$delimeter" (OPTIONAL: defaults to false)
	 * - [str] $maxKeys: number of keys to be returned (OPTIONAL: defaults to 1000 - maximum allowed by service)
	*/
	function listKeys($bucket, $marker=FALSE, $prefix=FALSE, $delimiter=FALSE, $maxKeys='1000') {
		$ret = $this->send($bucket, "max-keys={$maxKeys}&marker={$marker}&prefix={$prefix}&delimiter={$delimiter}");
		if($ret == 200){
		    return true;
		} else {
			return false;
		}
	}
	
	/**
	 * getBucketACL -- Gets bucket access control policy.
	 *
	 * Takes ($bucket)
	 *
	 * - [str] $bucket: the bucket whose acl you want
	*/	 
	function getBucketACL($bucket){
		$ret = $this->send($bucket.'/?acl');
		if ($ret == 200) {
			return true;
		} else {
			return false;		
		}
	}
	
	/**
	 * getObjectACL -- gets an objects access control policy.
	 *
	 * Takes ($bucket, $key)  
	 *
	 * - [str] $bucket
	 * - [str] $key
	*/   
	function getObjectACL($bucket, $key){
		$ret = $this->send($bucket."/".urlencode($key).'?acl');
		if ($ret == 200) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * getMetadata -- Gets the metadata associated with an object.
	 *
	 * Takes ($bucket, $key)  
	 *
	 * - [str] $bucket
	 * - [str] $key
	*/   
	function getMetadata($bucket, $key){
	    if ($data = $this->getCache($bucket."/".$key)) {
	        return $data;
	    }
		$ret = $this->send($bucket."/".urlencode($key), '', 'HEAD');
		if ($ret == 200) {
			$data = $this->req->getResponseHeader();
			foreach ($data as $k => $d) $data[strtolower($k)] = trim($d);
			$this->setCache($bucket."/".$key, $data);
			return $data;
		} else {
			return array();
		}
	}
	function send($resource, $args='', $method='GET') {
		$method=strtoupper($method);
		$httpDate = gmdate("D, d M Y G:i:s T");
		$signature = $this->constructSig("$method\n\n\n$httpDate\n/$resource");
		
		$this->req->setURL($this->serviceUrl.$resource.($args ? '?'.$args : ''));
		$this->req->setMethod($method);
		$this->req->addHeader("Date", $httpDate);
		$this->req->addHeader("Authorization", "AWS " . $this->accessKeyId . ":" . $signature);
		$this->req->sendRequest();
		if ($method=='GET') {
			$this->parsed_xml = simplexml_load_string($this->req->getResponseBody());
		}
		return $this->req->getResponseCode();
	}
	function hex2b64($str) {
		$raw = '';
		for ($i=0; $i < strlen($str); $i+=2) {
			$raw .= chr(hexdec(substr($str, $i, 2)));
		}
		return base64_encode($raw);
	}
		 
	function constructSig($str) {
		$hasher =& new TanTanCrypt_HMAC($this->secretKey, "sha1");
		$signature = $this->hex2b64($hasher->hash($str));
		return($signature);
	}
	
    function initCacheTables() {
        global $wpdb;
        if (!is_object($wpdb)) return;
        
        $wpdb->query("CREATE TABLE IF NOT EXISTS `tantan_wordpress_s3_cache` (
                `request` VARCHAR( 255 ) NOT NULL ,
                `response` TEXT NOT NULL ,
                `timestamp` DATETIME NOT NULL ,
                PRIMARY KEY ( `request` )
            ) TYPE = MYISAM");	    
	}
	function setCache($key, $data) {
        global $wpdb;
        if (!is_object($wpdb)) return false;
        $key = addslashes(trim($key));
        $wpdb->query("DELETE FROM tantan_wordpress_s3_cache WHERE request = '".$key."'");
        $sql = "INSERT INTO tantan_wordpress_s3_cache (request, response, timestamp) VALUES ('".$key."', '" . addslashes(serialize($data)) . "', '" . strftime("%Y-%m-%d %H:%M:%S") . "')";
        $wpdb->query($sql); 
        return $data;
	}
	function getCache($key) {
        global $wpdb;
        if (!is_object($wpdb)) return false;
        $key = trim($key);
        $result = $wpdb->get_var("SELECT response FROM tantan_wordpress_s3_cache WHERE request = '" . $key . "' LIMIT 1");
        
        if (!empty($result)) {
            return unserialize($result);
        }
        return false;        
	}
	function clearCache() {
        global $wpdb;
        if (!is_object($wpdb)) return false;
	    $result = $wpdb->query("DELETE FROM tantan_wordpress_s3_cache;");
	}
}
?>