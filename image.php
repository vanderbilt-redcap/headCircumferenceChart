<?php
## TODO Make type names consistent
$chartType = $_GET["chartType"];
$chartSex = $_GET["chartSex"];
$chartDataSet = $_GET["chartDataSet"];

/** @var $module \Vanderbilt\HeadCircChart\HeadCircChart */
if(array_key_exists($chartType.$chartDataSet,\Vanderbilt\HeadCircChart\HeadCircChart::$imageDetails)) {
	if(array_key_exists($chartSex,\Vanderbilt\HeadCircChart\HeadCircChart::$imageDetails[$chartType.$chartDataSet])) {
		$imageFile = \Vanderbilt\HeadCircChart\HeadCircChart::$imageDetails[$chartType.$chartDataSet][$chartSex]["imageLocation"];
	}
}
else {
	die("Invalid type");
}

if($imageFile) {
	$imageData = file_get_contents($imageFile);

	header("Content-Type: image/png");
	echo $imageData;
}
else {
	die("ERROR");
}