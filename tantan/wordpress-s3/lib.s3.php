<?php
/*
    based on code provided by Amazon
*/
require_once (dirname(__FILE__).'/../lib/HMAC.php');

// grab this with "pear install --onlyreqdeps HTTP_Request"
//require_once 'Request.php';
//require_once (dirname(__FILE__).'/../lib/curl.php');
require_once(dirname(__FILE__).'/../lib/Request.php');


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
		$this->req =& new HTTP_Request($this->serviceUrl);
	}
			
	/**
	 * listBuckets -- Lists all buckets.
	*/
	function listBuckets() {
		$httpDate = gmdate("D, d M Y G:i:s T");
		$stringToSign="GET\n\n\n$httpDate\n/";
		$signature = $this->constructSig($stringToSign);
		$req =& new HTTP_Request($this->serviceUrl);
		$req->addHeader("Date", $httpDate);
		$req->addHeader("Authorization", "AWS " . $this->accessKeyId . ":" . $signature);
		$req->sendRequest();
		$this->responseCode = $req->getResponseCode();
		$this->responseString = $req->getResponseBody();
		$this->parsed_xml = simplexml_load_string($this->responseString);
		if($this->responseCode == 200){ 
		    $return = array();
		    foreach ($this->parsed_xml->Buckets->Bucket as $test) {
		        $return[] = $test;
		        
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
		$httpDate = gmdate("D, d M Y G:i:s T");
		$stringToSign = "GET\n\n\n$httpDate\n/$bucket";
		$signature = $this->constructSig($stringToSign);
		//$req =& new HTTP_Request($this->serviceUrl.$bucket."?max-keys={$maxKeys}&marker={$marker}&prefix={$prefix}&delimiter={$delimiter}");
		$req =& new HTTP_Request($this->serviceUrl.$bucket."?max-keys={$maxKeys}&marker={$marker}&prefix={$prefix}&delimiter={$delimiter}");
		$req->addHeader("Date", $httpDate);
		$req->addHeader("Authorization", "AWS " . $this->accessKeyId . ":" . $signature);
		$req->sendRequest();
		$this->responseCode = $req->getResponseCode();
		$this->responseString = $req->getResponseBody();
		$this->parsed_xml = simplexml_load_string($this->responseString);
		if($this->responseCode == 200){
		    $keys = array();
		    $prefixes = array();
		    if ($this->parsed_xml->Contents) foreach ($this->parsed_xml->Contents as $content) {
		        $keys[] = $content;
		    }
		    if ($this->parsed_xml->CommonPrefixes) foreach ($this->parsed_xml->CommonPrefixes as $content) {
		        $prefixes[] = (string) $content->Prefix;
		    }
		    
			return array('keys' => $keys, 'prefixes' => $prefixes);
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
		$httpDate = gmdate("D, d M Y G:i:s T");
		$stringToSign = "GET\n\n\n$httpDate\n/$bucket/?acl";
		$signature = $this->constructSig($stringToSign);
		$req =& new HTTP_Request($this->serviceUrl.$bucket.'/?acl');
		$req->setMethod("GET");
		$req->addHeader("Date", $httpDate);
		$req->addHeader("Authorization", "AWS " . $this->accessKeyId . ":" . $signature);
		$req->sendRequest();
		$this->responseCode = $req->getResponseCode();
		$this->responseString = $req->getResponseBody();
		$this->parsed_xml = simplexml_load_string($this->responseString);
		if ($this->responseCode == 200) {
			return true;
		} else {
			return false;		
		}
	}
	


	
	/**
	 * getLoggingStatus -- gets a bucket's logging status (is logging enabled?).
	 *
	 * Takes ($bucket)  
	 *
	 * - [str] $bucket
	*/   
	function getLoggingStatus($bucket){
		$httpDate = gmdate("D, d M Y G:i:s T");
		$stringToSign = "GET\n\n\n$httpDate\n/$bucket?logging";
		$signature = $this->constructSig($stringToSign);
		$req =& new HTTP_Request($this->serviceUrl.$bucket.'?logging');
		$req->setMethod("GET");
		$req->addHeader("Date", $httpDate);
		$req->addHeader("Authorization", "AWS " . $this->accessKeyId . ":" . $signature);
		$req->sendRequest();
		$this->responseCode = $req->getResponseCode();
		$this->responseString = $req->getResponseBody();
		$this->parsed_xml = simplexml_load_string($this->responseString);
		if ($this->responseCode == 200) {
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
		$httpDate = gmdate("D, d M Y G:i:s T");
		$resource = $bucket."/".urlencode($key);
		$stringToSign = "HEAD\n\n\n$httpDate\n/$resource";
		$signature = $this->constructSig($stringToSign);
		$req =& new HTTP_Request($this->serviceUrl.$resource);
		$req->setMethod("HEAD");
		$req->addHeader("Date", $httpDate);
		$req->addHeader("Authorization", "AWS " . $this->accessKeyId . ":" . $signature);
		$req->sendRequest();
		$this->responseCode = $req->getResponseCode();
		$this->headers = $req->getResponseHeader();
		if ($this->responseCode == 200) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * getObjectAsString -- Returns object as a string.
	 *
	 * Takes ($bucket, $key)  
	 *
	 * - [str] $bucket
	 * - [str] $key
	*/   
	function getObjectAsString($bucket, $key) {
		$httpDate = gmdate("D, d M Y G:i:s T");
		$resource = $bucket."/".urlencode($key);
		$stringToSign = "GET\n\n\n{$httpDate}\n/$resource";
		$signature = $this->constructSig($stringToSign);
		$req = & new HTTP_Request($this->serviceUrl.$resource);
		$req->setMethod("GET");
		$req->addHeader("Date", $httpDate);
		$req->addHeader("Authorization", "AWS " . $this->accessKeyId . ":" . $signature);
		$req->sendRequest();
		$this->responseCode = $req->getResponseCode();
		$this->responseString = $req->getResponseBody();		
		if ($this->responseCode == 200) {
			return true;
		} else {
		$this->parsed_xml = simplexml_load_string($this->responseString);		
			return false;
		}
	}
	
	/**
	 * queryStringGet -- returns a signed URL to get object
	 *
	 * Takes ($bucket, $key, $expires)  
	 *
	 * - [str] $bucket
	 * - [str] $key
	 * - [str] $expires - signed URL with expire after $expires seconds
	*/   
	function queryStringGet($bucket, $key, $expires){
		$expires = time() + $expires;
		$resource = $bucket."/".urlencode($key);
		$stringToSign = "GET\n\n\n$expires\n/$resource";
		$signature = urlencode($this->constructSig($stringToSign));
		$queryString = "<a href='http://s3.amazonaws.com/$resource?AWSAccessKeyId=$this->accessKeyId&Expires=$expires&Signature=$signature'>$bucket/$key</a>";
		return $queryString;         
	}
	
	function hex2b64($str) {
		$raw = '';
		for ($i=0; $i < strlen($str); $i+=2) {
			$raw .= chr(hexdec(substr($str, $i, 2)));
		}
		return base64_encode($raw);
	}
		 
	function constructSig($str) {
		$hasher =& new Crypt_HMAC($this->secretKey, "sha1");
		$signature = $this->hex2b64($hasher->hash($str));
		return($signature);
	}
}

class Stream{
  var $data;
  function stream_function($handle, $fd, $length){
    return fread($this->data, $length);
  }
}
    


?>