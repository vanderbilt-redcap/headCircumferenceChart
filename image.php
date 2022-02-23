<?php
$fileId = $_GET["type"];

/** @var $module \Vanderbilt\HeadCircChart\HeadCircChart */
if($fileId == "girls") {
	$imageFile = $module->getProjectSetting("girls-chart");
}
else if($fileId == "boys") {
	$imageFile = $module->getProjectSetting("boys-chart");
}
else {
	die("Invalid type");
}

$q = $module->query("SELECT *
					FROM redcap_edocs_metadata
					WHERE doc_id = ?", $imageFile);

if($row = db_fetch_assoc($q)) {
	//get latest image with base64_encode
	$imageData = file_get_contents(EDOC_PATH . $row['stored_name']);
	$fileType = $row["mime_type"];
}

if(!in_array($fileType,["image/jpeg","image/png","image/jpg"])) {
	die("Invalid file");
}
header("Content-Type: $fileType");
echo $imageData;