<?php
/** @var $module \Vanderbilt\HeadCircChart\HeadCircChart */

$userRights = $module->getUser()->getRights($project_id);

if($userRights !== NULL) {
	$record = $_POST['record'];
	$event = $_POST['event'];
	$instrument = $_POST['instrument'];
	$repeatInstance = $_POST['repeatInstance'];
	$age = $_POST['age'];
	$thisValue = $_POST['thisValue'];
	
	if(!$record) {
		die();
	}
	
	$chartData = $module->getChartDataForRecord($project_id,$record,$event,$instrument,$repeatInstance);
	
	

	## TODO Need to actually calculate the new chart values
	$returnValues = [
		"chartType" => "weight",
		"chartDataSet" => "",
		"chartSex" => "boys",
		"field" => "weight",
		"x" => 200,
		"y" => 200,
		"xHistory" => [200,300],
		"yHistory" => [200,300],
		"debug" => "1",
	];
	echo json_encode($returnValues);
}