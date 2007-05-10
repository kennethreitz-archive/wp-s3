<?php
require("s3.php");

//Account Identifiers
//$keyId = "--Your AWS Access Key Id Here--";	
//$secretKey = "--Your AWS Secret Key Here--";
$keyId = '1ZJ268JTY4J5YDSVNFG2';
$secretKey = 'XtGIaEWeyIsIapKvrYqt3nVI+ljg7uJX2cwX4XGU';

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Amazon S3 Test Utility in PHP</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
body, td, th{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 12px;
}
-->
</style>
<script type="text/javascript">
var operations = new Array("createBucket","deleteBucket","emptyBucket","listBuckets","listKeys","getBucketACL","setBucketACL","putObject","putObjectStream","getObjectAsString","deleteObject","getMetaData","getObjectACL","setObjectACL","queryStringGet","enableLogging","getLoggingStatus","grantLoggingPermission");
function doFilter(value){
	var elements;
	for (i in operations){
		elements = document.getElementsByName(operations[i]);
		if(value == operations[i]){
			for (y=0;y<elements.length;y++){
				elements[y].style.display='inline';
			}
		}
		else{
			for (y=0;y<elements.length;y++){
				elements[y].style.display='none';
			}
		}
	}
	document.getElementsById("info").style.display='inline';
}

function test(){
	var form = document.forms[2];
	var x = form.bucket.value;
	var y = form.key.value;
	document.getElementById("info").innerHTML = x + y;
}
</script>
</head>

<body onload="doFilter('listBuckets')">
<h1 align='center'>Amazon S3 Test Utility in PHP</h1>
<h2 align='center'>Operations</h2>
	<table align='center' border="1" bordercolor="#EEEEEE" cellspacing="0">
	<tr bgcolor="#EEEEEE"><td><b>Buckets</b></td><td><b>Objects</b></td></tr>
	<tr valign="top">
	<td>
		<input type='radio' name='operation' value='createBucket' onClick='doFilter(this.value)'/>Create a Bucket<br>
		<input type='radio' name='operation' value='deleteBucket' onClick='doFilter(this.value)'/>Delete a Bucket<br>
		<input type='radio' name='operation' value='emptyBucket' onClick='doFilter(this.value)'/>Empty a Bucket (Delete all keys)<br>
		<input type='radio' name='operation' value='listBuckets' onClick='doFilter(this.value)' checked/>List Buckets<br>
		<input type='radio' name='operation' value='listKeys' onClick='doFilter(this.value)'/>List Keys in a Bucket<br>
		<input type='radio' name='operation' value='getBucketACL' onClick='doFilter(this.value)'/>Get Bucket ACL<br>
		<input type='radio' name='operation' value='setBucketACL' onClick='doFilter(this.value)'/>Set Bucket ACL<br>
		<input type='radio' name='operation' value='grantLoggingPermission' onClick='doFilter(this.value)'/>Grant Write-Log Permission<br>
		<input type='radio' name='operation' value='getLoggingStatus' onClick='doFilter(this.value)'/>Get Bucket Logging Stauts<br>
		<input type='radio' name='operation' value='enableLogging' onClick='doFilter(this.value)'/>Log Bucket Activity
	</td>
	<td>
		<input type='radio' name='operation' value='putObject' onClick='doFilter(this.value)'/>Put an Object<br>
		<input type='radio' name='operation' value='putObjectStream' onClick='doFilter(this.value)'/>Stream an Object to S3<br>
		<input type='radio' name='operation' value='deleteObject' onClick='doFilter(this.value)'/>Delete an Object<br>
		<input type='radio' name='operation' value='getObjectAsString' onClick='doFilter(this.value)'/>Get Object as String<br>
		<input type='radio' name='operation' value='queryStringGet' onClick='doFilter(this.value)'/>Get Object via Query String<br>
		<input type='radio' name='operation' value='getMetaData' onClick='doFilter(this.value)'/>Get Object Metadata<br>
		<input type='radio' name='operation' value='getObjectACL' onClick='doFilter(this.value)'/>Get Object ACL<br>
		<input type='radio' name='operation' value='setObjectACL' onClick='doFilter(this.value)'/>Set Object ACL
	</td>
	</tr>
	</table>
</form>

<h2 align='center'>Additional Parameters</h2>
	<div id="createBucket" name="createBucket">
	<h2 align='center'>for <i>Create a Bucket</i></p>
		<form action='index.php' method='POST'>
			<table align='center'>
				<input type='hidden' name='operation' value='createBucket'/>
				<tr><td>Bucket name (no slashes): <input type='text' name='bucket' size='40'/></td></tr>
				<tr id='putCT'><td>Access Control Policy:
            		<select name='acl'>
              			<option value='private'>private</option>
              			<option value='public-read'>public-read</option>
              			<option value='public-read-write'>public-read-write</option>
			  			<option value='authenticated-read'>authenticated-read</option>
            		</select></td></tr>
			</table>
			<p align='center'><input type='submit' name='submit'/></p>
		</form>
	</div>
	
	<div id="deleteBucket" name="deleteBucket">
	<h2 align='center'>for <i>Delete a Bucket</i></p>
		<form action='index.php' method='POST'>
			<table align='center'>
				<input type='hidden' name='operation' value='deleteBucket'/>
				<tr><td>Bucket name (no slashes): <input type='text' name='bucket' size='40'/></td></tr>
			</table>
			<p align='center'><input type='submit' name='submit'/></p>
		</form>
	</div>
	
	<div id="emptyBucket" name="emptyBucket">
	<h2 align='center'>for <i>Empty a Bucket (Delete all keys)</i></p>
		<form action='index.php' method='POST'>
			<table align='center'>
				<input type='hidden' name='operation' value='emptyBucket'/>
				<tr><td>Bucket name (no slashes): <input type='text' name='bucket' size='40'/></td></tr>
			</table>
			<p align='center'><input type='submit' name='submit'/></p>
		</form>
	</div>
	
	<div id="listBuckets" name="listBuckets">
	<h2 align='center'>for <i>List Buckets</i></p>
		<form action='index.php' method='POST'>
			<table align='center'>
				<input type='hidden' name='operation' value='listBuckets'/>
				<tr><td>None</td></tr>
				<tr><td></td></tr>
			</table>
			<p align='center'><input type='submit' name='submit'/></p>
		</form>
	</div>
	
	<div id="listKeys" name="listKeys">
	<h2 align='center'>for <i>List Keys in a Bucket</i></p>
		<form action='index.php' method='POST'>
			<table align='center'>
				<input type='hidden' name='operation' value='listKeys'/>
				<tr><td>Bucket name (no slashes): <input type='text' name='bucket' size='40'/></td></tr>
				<tr><td>Number of Keys to List (optional, max=1000): <input type='text' name='max-keys' size='10'/></td></tr>
				<tr><td>Keys alphabetically after (optional): <input type='text' name='marker' size='20'/></td></tr>
				<tr><td>Keys beginning with (optional): <input type='text' name='prefix' size='20'/></td></tr>
				<tr><td>Keys delimited by (optional): <input type='text' name='delimiter' size='20'/></td></tr>
			</table>
			<p align='center'><input type='submit' name='submit' onClick='test()'/></p>
		</form>
	</div>
	
	<div id="getBucketACL" name="getBucketACL">
	<h2 align='center'>for <i>Get Bucket ACL</i></p>
		<form action='index.php' method='POST'>
			<table align='center'>
				<input type='hidden' name='operation' value='getBucketACL'/>
				<tr><td>Bucket name (no slashes): <input type='text' name='bucket' size='40'/></td></tr>
			</table>
			<p align='center'><input type='submit' name='submit'/></p>
		</form>
	</div>
	
	<div id="setBucketACL" name="setBucketACL">
	<h2 align='center'>for <i>Set Bucket ACL</i></p>
		<form action='index.php' method='POST'>
			<table align='center'>
				<input type='hidden' name='operation' value='setBucketACL'/>
				<tr><td>Bucket name (no slashes): <input type='text' name='bucket' size='40'/></td></tr>
				<tr id='putCT'><td>Access Control Policy:
            		<select name='acl'>
              			<option value='private'>private</option>
              			<option value='public-read'>public-read</option>
              			<option value='public-read-write'>public-read-write</option>
			  			<option value='authenticated-read'>authenticated-read</option>
            		</select></td></tr>
			</table>
			<p align='center'><input type='submit' name='submit'/></p>
		</form>
	</div>
	
	<div id="grantLoggingPermission" name="grantLoggingPermission">
	<h2 align='center'>for <i>Grant Write-Log Permission</i></p>
		<form action='index.php' method='POST'>
			<table align='center'>
				<input type='hidden' name='operation' value='grantLoggingPermission'/>
				<tr><td>Bucket name (no slashes): <input type='text' name='bucket' size='40'/></td></tr>
				<tr><td>Note: To revoke logging permission, overwrite the ACP for a bucket using one of the canned ACL's provided by <b>Set Bucket ACL</b>.</td></tr>
			</table>
			<p align='center'><input type='submit' name='submit'/></p>
		</form>
	</div>
	
	<div id="getLoggingStatus" name="getLoggingStatus">
	<h2 align='center'>for <i>Get Bucket Logging Status</i></p>
		<form action='index.php' method='POST'>
			<table align='center'>
				<input type='hidden' name='operation' value='getLoggingStatus'/>
				<tr><td>Bucket name (no slashes): <input type='text' name='bucket' size='40'/></td></tr>
			</table>
			<p align='center'><input type='submit' name='submit'/></p>
		</form>
	</div>
	
	<div id="enableLogging" name="enableLogging">
	<h2 align='center'>for <i>Log Bucket Activity</i></p>
		<form action='index.php' method='POST'>
			<table align='center'>
				<input type='hidden' name='operation' value='enableLogging'/>
				<tr><td>Bucket to be Logged (no slashes): <input type='text' name='bucket' size='40'/></td></tr>
				<tr><td>Bucket to Store Logs, i.e. TargetBucket (no slashes): <input type='text' name='targetBucket' size='40'/></td></tr>
				<tr><td>Log Entry Prefix, i.e. TargetPrefix: <input type='text' name='targetPrefix' size='40'/></td></tr>
				<tr><td>Check to turn logging on, don't check to turn logging off. <input type='checkbox' name='switch'/></td></tr>
			</table>
			<p align='center'><input type='submit' name='submit'/></p>
		</form>
	</div>
	
	<div id="putObject" name="putObject">
	<h2 align='center'>for <i>Put an Object</i></p>
		<form action='index.php' enctype='multipart/form-data' method='POST'>
			<table align='center'>
				<input type='hidden' name='operation' value='putObject'/>
				
				<tr><td>Bucket name (no slashes): <input type='text' name='bucket' size='40'/></td></tr>
				<tr><td>Key: <input type='text' name='key' size='40'/></td></tr>
				<tr><td>File to PUT: <input id='fileName' type='file' name='file'/></td></tr>
				<tr><td>Access Control Policy:
            		<select name='acl'>
              			<option value='private'>private</option>
              			<option value='public-read'>public-read</option>
              			<option value='public-read-write'>public-read-write</option>
			  			<option value='authenticated-read'>authenticated-read</option>
            		</select></td></tr>
				<tr><td>x-amz-meta-<i>(name:value)</i>:<input type='text' name='metaOne' size='40'/></td></tr>
				<tr><td>x-amz-meta-<i>(name:value)</i>:<input type='text' name='metaTwo' size='40'/></td></tr>
				<tr><td>x-amz-meta-<i>(name:value)</i>:<input type='text' name='metaThree' size='40'/></td></tr>
				<tr><td>x-amz-meta-<i>(name:value)</i>:<input type='text' name='metaFour' size='40'/></td></tr>
				<tr><td>x-amz-meta-<i>(name:value)</i>:<input type='text' name='metaFive' size='40'/></td></tr>
				<tr><td>x-amz-meta-<i>(name:value)</i>:<input type='text' name='metaSix' size='40'/></td></tr>
				<tr><td>x-amz-meta-<i>(name:value)</i>:<input type='text' name='metaSeven' size='40'/></td></tr>
				<tr><td>Include MD5 Checksum? <input type='checkbox' name='md5'/></td></tr>
			</table>
            <p align='center'><input type='submit' name='submit' onClick='test()'/></p>
		</form>
	</div>
	
	<div id="putObjectStream" name="putObjectStream">
	<h2 align='center'>for <i>Put an Object</i></p>
		<form action='index.php' enctype='multipart/form-data' method='POST'>
			<table align='center'>
				<input type='hidden' name='operation' value='putObjectStream'/>
				
				<tr><td>Bucket name (no slashes): <input type='text' name='bucket' size='40'/></td></tr>
				<tr><td>Key: <input type='text' name='key' size='40'/></td></tr>
				<tr><td>File to PUT: <input id='fileName' type='file' name='file'/></td></tr>
				<tr><td>Access Control Policy:
            		<select name='acl'>
              			<option value='private'>private</option>
              			<option value='public-read'>public-read</option>
              			<option value='public-read-write'>public-read-write</option>
			  			<option value='authenticated-read'>authenticated-read</option>
            		</select></td></tr>
				<tr><td>x-amz-meta-<i>(name:value)</i>:<input type='text' name='metaOne' size='40'/></td></tr>
				<tr><td>x-amz-meta-<i>(name:value)</i>:<input type='text' name='metaTwo' size='40'/></td></tr>
				<tr><td>x-amz-meta-<i>(name:value)</i>:<input type='text' name='metaThree' size='40'/></td></tr>
				<tr><td>x-amz-meta-<i>(name:value)</i>:<input type='text' name='metaFour' size='40'/></td></tr>
				<tr><td>x-amz-meta-<i>(name:value)</i>:<input type='text' name='metaFive' size='40'/></td></tr>
				<tr><td>x-amz-meta-<i>(name:value)</i>:<input type='text' name='metaSix' size='40'/></td></tr>
				<tr><td>x-amz-meta-<i>(name:value)</i>:<input type='text' name='metaSeven' size='40'/></td></tr>
			</table>
            <p align='center'><input type='submit' name='submit' onClick='test()'/></p>
		</form>
	</div>
	
	<div id="deleteObject" name="deleteObject">
	<h2 align='center'>for <i>Delete an Object</i></p>
		<form action='index.php' method='POST'>
			<table align='center'>
				<input type='hidden' name='operation' value='deleteObject'/>
				<tr><td>Bucket name (no slashes): <input type='text' name='bucket' size='40'/></td></tr>
				<tr><td>Key: <input type='text' name='key' size='40'/></td></tr>
			</table>
			<p align='center'><input type='submit' name='submit' onClick='test()'/></p>
		</form>
	</div>
	
	<div id="getObjectAsString" name="getObjectAsString">
	<h2 align='center'>for <i>Get Object as String</i></p>
		<form action='index.php' method='POST'>
			<table align='center'>
				<input type='hidden' name='operation' value='getObjectAsString'/>
				<tr><td>Bucket name (no slashes): <input type='text' name='bucket' size='40'/></td></tr>
				<tr><td>Key: <input type='text' name='key' size='40'/></td></tr>
			</table>
			<p align='center'><input type='submit' name='submit' onClick='test()'/></p>
		</form>
	</div>
	
	<div id="getMetaData" name="getMetaData">
	<h2 align='center'>for <i>Get Object Metadata</i></p>
		<form action='index.php' method='POST'>
			<table align='center'>
				<input type='hidden' name='operation' value='getMetaData'/>
				<tr><td>Bucket name (no slashes): <input type='text' name='bucket' size='40'/></td></tr>
				<tr><td>Key: <input type='text' name='key' size='40'/></td></tr>
			</table>
			<p align='center'><input type='submit' name='submit' onClick='test()'/></p>
		</form>
	</div>
	
	<div id="getObjectACL" name="getObjectACL">
	<h2 align='center'>for <i>Get Object ACL</i></p>
		<form action='index.php' method='POST'>
			<table align='center'>
				<input type='hidden' name='operation' value='getObjectACL'/>
				<tr><td>Bucket name (no slashes): <input type='text' name='bucket' size='40'/></td></tr>
				<tr><td>Key: <input type='text' name='key' size='40'/></td></tr>
			</table>
			<p align='center'><input type='submit' name='submit'/></p>
		</form>
	</div>
	
	<div id="queryStringGet" name="queryStringGet">
	<h2 align='center'>for <i>Get Object via Query String</i></p>
		<form action='index.php' method='POST'>
			<table align='center'>
				<input type='hidden' name='operation' value='queryStringGet'/>
				<tr><td>Bucket name (no slashes): <input type='text' name='bucket' size='40'/></td></tr>
				<tr><td>Key: <input type='text' name='key' size='40'/></td></tr>
				<tr><td>Expires from now (in seconds): <input type='text' name='expires' size='20'/></td></tr>
			</table>
			<p align='center'><input type='submit' name='submit'/></p>
		</form>
	</div>
	
	<div id="setObjectACL" name="setObjectACL">
	<h2 align='center'>for <i>Set Object ACL</i></p>
		<form action='index.php' method='POST'>
			<table align='center'>
				<input type='hidden' name='operation' value='setObjectACL'/>
				<tr><td>Bucket name (no slashes): <input type='text' name='bucket' size='40'/></td></tr>
				<tr><td>Key: <input type='text' name='key' size='40'/></td></tr>
				<tr id='putCT'><td>Access Control Policy:
            		<select name='acl'>
              			<option value='private'>private</option>
              			<option value='public-read'>public-read</option>
              			<option value='public-read-write'>public-read-write</option>
			  			<option value='authenticated-read'>authenticated-read</option>
            		</select></td></tr>
			</table>
			<p align='center'><input type='submit' name='submit' onClick='test()'/></p>
		</form>
	</div>
	
<h2 align='center'>Results</h2>
<?php
// Get request parameters
$operation = $_REQUEST['operation'];
$bucket = $_REQUEST['bucket'];
$key = $_REQUEST['key'];
if($_REQUEST['max-keys'] == '' || $_REQUEST['max-keys'] > 1000){
	$maxKeys = 1000;
} else {
	$maxKeys = $_REQUEST['max-keys'];
}
$marker = $_REQUEST['marker'];
$prefix = $_REQUEST['prefix'];
$delimiter = $_REQUEST['delimiter'];
if (is_uploaded_file($_FILES['file']['tmp_name'])) {
	$filePath = $_FILES['file']['tmp_name'];
	$contentType = $_FILES['file']['type'];
} elseif ($_FILES['file']['tmp_name'] != "") {
    error("Serious error");
}
//Note: the number of metadata items associated with an object is not limited to 7 by S3
$metaOne=strtolower($_REQUEST['metaOne']);
$metaTwo=strtolower($_REQUEST['metaTwo']);
$metaThree=strtolower($_REQUEST['metaThree']);
$metaFour=strtolower($_REQUEST['metaFour']);
$metaFive=strtolower($_REQUEST['metaFive']);
$metaSix=strtolower($_REQUEST['metaSix']);
$metaSeven=strtolower($_REQUEST['metaSeven']);
$metadataArray=array($metaOne, $metaTwo, $metaThree, $metaFour, $metaFive, $metaSix, $metaSeven);
$acl = $_REQUEST['acl'];
$expires = $_REQUEST['expires'];
$md5 = $_REQUEST['md5'];
$contentLength = sprintf("%u", filesize($filePath));//Latest stats: Fails at 37 MB, Succeeds at 18 MB
$targetBucket = $_REQUEST['targetBucket'];
$targetPrefix = $_REQUEST['targetPrefix'];
$switch = $_REQUEST['switch'];

print("<table width='50%' align='center'><td>");
switch($operation){
	case "createBucket":
		$s3 = new s3($keyId, $secretKey);	
		if($s3->createBucket($bucket, $acl)){
			print("Bucket <b>$bucket</b> created.");
		} else { 
			printError($s3->parsed_xml, $s3->responseCode);
		}
		break;
	case "deleteBucket":
		$s3 = new s3($keyId, $secretKey);
		if($s3->deleteBucket($bucket)){
			print("Bucket <b>$bucket</b> deleted.");
		} else {
			printError($s3->parsed_xml, $s3->responseCode);
		}
		break;
	case "emptyBucket":
		$s3 = new s3($keyId, $secretKey);
		if($s3->emptyBucket($bucket)){
			print("Bucket <b>$bucket</b> emptied.");
		} else {
			printError($s3->parsed_xml, $s3->responseCode);
		}
		break;
	case "listBuckets":
		$s3 = new s3($keyId, $secretKey);
		if($s3->listBuckets()){
			$i=1;
			foreach($s3->parsed_xml->Buckets->Bucket as $current){
				print("$i. <b>".$current->Name."</b><br>");
				$i++;
			}
		} else {
			printError($s3->parsed_xml, $s3->responseCode);
		}
		break;
	case "listKeys":
		$s3 = new s3($keyId, $secretKey);
		if($s3->listKeys($bucket, $marker, $prefix, $delimiter, $maxKeys)){
			if(isset($s3->parsed_xml->CommonPrefixes)){
				$j = 1;
				print("Common Prefixes:<br>");
				foreach($s3->parsed_xml->CommonPrefixes as $current){
					print("$j. <b>".$current->Prefix."</b><br>");
					$j++;
				}
			}
			if(isset($s3->parsed_xml->Contents)){
				$i=1;
				print("Keys:<br>");
				foreach($s3->parsed_xml->Contents as $current){
					print("$i. <b>".$current->Key."</b><br>");
					$i++;
				}
			}
			if($s3->parsed_xml->IsTruncated == 'false'){
				print("<b>There are no more keys.</b>");
			} else {
				print("<b>There are more keys in this bucket.</b>");
			}
		} else {
			printError($s3->parsed_xml, $s3->responseCode);
		}
		break;
	case "getBucketACL":
		$s3 = new s3($keyId, $secretKey);
		if($s3->getBucketACL($bucket)){
			print("Access Control List for bucket <b>$bucket</b>:<br><br>");
			print("Owner: ".$s3->parsed_xml->Owner->DisplayName."<br>");
			print("ID: ".$s3->parsed_xml->Owner->ID."<br><br>");
			print("Grants:<br>");
			$i=1;
			foreach($s3->parsed_xml->AccessControlList->Grant as $current){
				if(isset($current->Grantee->DisplayName)){
					print("$i. ".$current->Grantee->DisplayName." => ");
				} else if(isset($current->Grantee->URI)){
					print("$i. ".$current->Grantee->URI." => ");
				}
				print($current->Permission."<br>");
			}
		} else {
			printError($s3->parsed_xml, $s3->responseCode);
		}
		break;
	case "setBucketACL":
		$s3 = new s3($keyId, $secretKey);
		if($s3->setBucketACL($bucket, $acl)){
			print("ACL for bucket <b>$bucket</b> set to <b>$acl</b>.");
		} else {
			printError($s3->parsed_xml, $s3->responseCode);
		}
		break;
	case "grantLoggingPermission":
		$s3 = new s3($keyId, $secretKey);
		if($s3->grantLoggingPermission($bucket)){
			print("Logs can now be written to <b>$bucket</b>.");
		} else {
			printError($s3->parsed_xml, $s3->responseCode);
		}
		break;
	case "getLoggingStatus":
		$s3 = new s3($keyId, $secretKey);
		if($s3->getLoggingStatus($bucket)){
			if(isset($s3->parsed_xml->LoggingEnabled)){
				$targetBucket = $s3->parsed_xml->LoggingEnabled->TargetBucket;
				$targetPrefix = $s3->parsed_xml->LoggingEnabled->TargetPrefix;
				print("Logs for <b>$bucket</b> will be written to <b>$targetBucket</b> with key-prefix <b>$targetPrefix</b>.");
			} else {
				print("Logging for bucket <b>$bucket</b> is <b>off</b>.");
			}
		} else {
			printError($s3->parsed_xml, $s3->responseCode);
		}
		break;
	case "enableLogging":
		$s3 = new s3($keyId, $secretKey);
		if($s3->enableLogging($bucket,$targetBucket,$targetPrefix, $switch)){
			if($switch){
				print("Logging for <b>$bucket</b> is now <b>on</b>.");
			} else {
				print("Logging for <b>$bucket</b> is now <b>off</b>.");
			}
		} else {
			printError($s3->parsed_xml, $s3->responseCode);
		}
		break;
	case "putObject":
	//echo $contentLength;
		$s3 = new s3($keyId, $secretKey);
		if($s3->putObject($bucket, $key, $filePath, $contentType, $contentLength, $acl, $metadataArray, $md5)){
			print("Object <b>$bucket/$key</b> put successfully.");
		} else {
			printError($s3->parsed_xml, $s3->responseCode);
		}
		break;
	case "putObjectStream":
		$s3 = new s3($keyId, $secretKey);
		if($s3->putObjectStream($bucket, $key, $contentType, $contentLength, $filePath, $acl, $metadataArray)){
			print("Object <b>$bucket/$key</b> put successfully.");
		} else {
			printError($s3->parsed_xml, $s3->responseCode);
		}
		break;
	case "deleteObject":
		$s3 = new s3($keyId, $secretKey);
		if($s3->deleteObject($bucket,$key)){
			print("Object <b>$bucket/$key</b> deleted.");
		} else {
			printError($s3->parsed_xml, $s3->responseCode);
		}
		break;
	case "getObjectACL":
		$s3 = new s3($keyId, $secretKey);
		if($s3->getObjectACL($bucket,$key)){
			print("Access Control List for object <b>$bucket/$key</b>:<br>");
			print("Owner: ".$s3->parsed_xml->Owner->DisplayName."<br>");
			print("ID: ".$s3->parsed_xml->Owner->ID."<br>");
			foreach($s3->parsed_xml->AccessControlList->Grant as $current){
				if(isset($current->Grantee->DisplayName)){
					print($current->Grantee->DisplayName." => ");
				} else if(isset($current->Grantee->URI)){
					print($current->Grantee->URI." => ");
				}
				print($current->Permission."<br>");
			}
		} else {
			printError($s3->parsed_xml, $s3->responseCode);
		}
		break;
	case "setObjectACL":
		$s3 = new s3($keyId, $secretKey);
		if($s3->setObjectACL($bucket, $key, $acl)){
			print("ACL for object <b>$bucket/$key</b> set to <b>$acl</b>.");
		} else {
			printError($s3->parsed_xml, $s3->responseCode);
		}
		break;
	case "getMetaData":
		$s3 = new s3($keyId, $secretKey);
		if($s3->getMetadata($bucket,$key)){
			foreach($s3->headers as $key => $value){
				print("<b>$key</b> => $value<br>");
			}
		} else {
			printError($s3->parsed_xml, $s3->responseCode);
		}
		break;
	case "getObjectAsString":
		$s3 = new s3($keyId, $secretKey);
		if($s3->getObjectAsString($bucket,$key)){
			print_r($s3->responseString);
		} else {
			printError($s3->parsed_xml, $s3->responseCode);
		}
		break;
	case "queryStringGet":
		$s3 = new s3($keyId, $secretKey);
		print($s3->queryStringGet($bucket, $key, $expires));
		break;
	default:
		print("Select an operation, then click <b>Submit Query</b> to see the result.");
}
print("</td></table>");	
function printError($parsed_xml, $responseCode){
	echo "Operation Failed<br>";
	echo "Error: ".$responseCode."<br>" . $parsed_xml->Message;
	if(isset($parsed_xml->StringToSignBytes)) echo "<br>Hex-endcoded string to sign: " . $parsed_xml->StringToSignBytes;
}

?>
</body>
</html>