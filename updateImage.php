<?php
/** @var $module \Vanderbilt\HeadCircChart\HeadCircChart */

$userRights = $module->getUser()->getRights($project_id);

if($userRights !== NULL) {
	$record = $_POST['record'];
	$event = $_POST['event'];
	$instrument = $_POST['instrument'];
	$repeatInstance = $_POST['repeatInstance'];
	$thisAge = $_POST['age'];
	$thisValue = $_POST['thisValue'];
	$chartType = $_POST['chartType'];
	
	if(!$record) {
		die();
	}
	
	list($sex,$age,$circumference,$height,$weight,$useFentonChart) =
		$module->getChartDataForRecord($project_id,$record,$event,$instrument,$repeatInstance,$thisAge,$thisValue,$chartType);
	
	$headChartField = $module->getProjectSetting("circ-chart-field");
	$heightChartField = $module->getProjectSetting("height-chart-field");
	$weightChartField = $module->getProjectSetting("weight-chart-field");
	$debugMode = $module->getProjectSetting("debug-mode");
	
	$thisField = false;
	$chartDataSet = "";
	if($useFentonChart) {
		$chartDataSet = "_fenton";
	}
	
	if($chartType == "headCirc") {
		$thisField = $headChartField;
		$values = $circumference;
	}
	if($chartType == "height") {
		$thisField = $heightChartField;
		$values = $height;
	}
	if($chartType == "weight") {
		$thisField = $weightChartField;
		$values = $weight;
	}
	
	$chartDetails = false;
	foreach(\Vanderbilt\HeadCircChart\HeadCircChart::$imageDetails[$chartType.$chartDataSet] as $thisSex => $thisImage) {
		foreach($thisImage["logic"] as $thisLogic) {
			$logicVar = $thisLogic[0];
			if($$logicVar !== $thisLogic[2]) {
				continue 2;
			}
		}
		
		$chartSex = $thisSex;
		$chartDetails = $thisImage;
		break;
	}
	
	if($chartDetails) {
		list($instanceX,$instanceY,$x,$y) = $module->calculateXY($chartDetails,$age,$values,$repeatInstance);
		
		$returnValues = [
			"chartType" => $chartType,
			"chartDataSet" => $chartDataSet,
			"chartSex" => ($sex == 2 ? "girls" : "boys"),
			"field" => $thisField,
			"x" => $instanceX,
			"y" => $instanceY,
			"xHistory" => $x,
			"yHistory" => $y,
			"debug" => $debugMode,
		];
		echo json_encode($returnValues);
	}
}