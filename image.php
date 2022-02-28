<?php
$fileId = $_GET["type"];

/** @var $module \Vanderbilt\HeadCircChart\HeadCircChart */
if(array_key_exists($fileId,\Vanderbilt\HeadCircChart\HeadCircChart::$imageDetails)) {
	$imageFile = \Vanderbilt\HeadCircChart\HeadCircChart::$imageDetails[$fileId]["imageLocation"];
}
else {
	die("Invalid type");
}


$imageData = file_get_contents($imageFile);

header("Content-Type: image/png");
echo $imageData;