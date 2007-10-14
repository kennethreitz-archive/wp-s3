<?php
// rename this file to "config.php" to use the settings below, instead of settings through the dashboard admin
// this can be used to automatically configure the plugin in a WordPress MU environment
$TanTanWordPressS3Config = array(
	'key' => '', // AWS Access Key ID
	'secret' => '', // AWS Secret Key
	'bucket' => '', // S3 Bucket
	'virtual-host' => false, // Bucket is configured for virtual hosting
	'wp-uploads' => true, // mirror all WordPress uploads into Amazon S3 bucket
	'permissions' => '', // set to "public" to have the plugin force all files in the specified bucket to "public" (sometimes third party upload utilities don't do this)
	'hideAmazonS3UploadTab' => false, // hide the Amazon S3 tab in the WordPress upload widget
	);
?>