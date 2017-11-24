<?php

// path to cashe files
$cachePath = __DIR__ . '/cache/';

// set of urls
$urlEntryPoint = "https://bank.gov.ua/NBUStatService/v1/statdirectory";
$urlDimensionDetails = $urlEntryPoint . "/dimension";

// cache functions implementation
function writeContent($url,$filename) {
	global $cachePath;

	$jsonEntryPoint = file_get_contents( $url . "/?json" );
	$file = fopen($cachePath.$filename,"w");
	fwrite($file,$jsonEntryPoint);
	fclose($file);
	$jsonEntryPoint = null;
}

// cache creation
writeContent($urlEntryPoint, "statdirectory.cache");
writeContent($urlDimensionDetails, "dimension.cache");

////////////

$dimensionsCacheContent = json_decode(file_get_contents( $cachePath . "dimension.cache" ));
$arrDimensionsCacheContent = array();

foreach ($dimensionsCacheContent as $key => $value) {
	$url = $urlDimensionDetails . "/" . $value->dimensionkod .  "?json";
	$urlHeaders = get_headers($url)[0];
	if ($urlHeaders === "HTTP/1.1 200 OK") {
		$dimensionContent = file_get_contents( $url );
		$arrDimensionsCacheContent[$value->dimensionkod] = $dimensionContent;
	}
}
$file = fopen($cachePath."dimensions.cache","w");
fwrite($file,json_encode($arrDimensionsCacheContent, true));
fclose($file);
$arrDimensionsCacheContent = null;
$dimensionsCacheContent = null;

////////////

$statdirectoryCacheContent = json_decode(file_get_contents( $cachePath . "statdirectory.cache" ));
$arrStatdirectoryCacheContent = array();

foreach ($statdirectoryCacheContent as $key => $value) {
	$url = $urlEntryPoint . "/" . $value->apikod .  "?start=20170701&end=20170701&json";
	$urlHeaders = get_headers($url)[0];
	if ($urlHeaders === "HTTP/1.1 200 OK") {
		$dsContent = file_get_contents( $url );
		$file = fopen($cachePath . "samples/" . $value->apikod . ".cache","w");
		fwrite($file,$dsContent);
		fclose($file);
		$dsContent = null;
	}
}

////////////

echo "Cache created!";


?>