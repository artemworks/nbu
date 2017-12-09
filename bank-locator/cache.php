<?php

// path to cashe files
$cachePath = __DIR__ . '/cache/';

$nbuServiceUrl = "https://bank.gov.ua/NBU_BankInfo/get_data_branch";

$testUrl = $nbuServiceUrl . "?typ=0";

// cache functions implementation
function writeContent($url,$filename) {
	global $cachePath;

	$entryPoint = file_get_contents( $url );
	$file = fopen($cachePath.$filename,"w");
	fwrite($file,$entryPoint);
	fclose($file);
	$entryPoint = null;
}

// cache creation
writeContent($testUrl, "sample.xml");

echo "Cache created!";


?>