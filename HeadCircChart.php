<?php

namespace Vanderbilt\HeadCircChart;

use ExternalModules\AbstractExternalModule;

class HeadCircChart extends AbstractExternalModule
{
	public static $imageDetails = [
		"boys" => [
			"imageLocation" => __DIR__ . "/images/head_circ_chart_cdc_boys.png",
			"pixelRange" => [83,100,508,600],
			"graphRange" => [0,36,30,53],
			"logic" => [["sex","=","1"]]
		],
		"girls" => [
			"imageLocation" => __DIR__ . "/images/head_circ_chart_cdc_girls.png",
			"pixelRange" => [96,100,503,600],
			"graphRange" => [0,36,30,53],
			"logic" => [["sex","=","0"]]
		]
	];
	
	function redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance) {
		$chartField = $this->getProjectSetting("chart-field");
		$sexField = $this->getProjectSetting("sex-field");
		$ageField = $this->getProjectSetting("age-field");
		$circumferenceField = $this->getProjectSetting("circumference-field");
		
		if($chartField && $sexField && $ageField && $circumferenceField) {
			$chartInstrument = $this->getProject()->getFormForField($chartField);
			
			if($chartInstrument == $instrument) {
				$age = [];
				$circumference = [];
				$sex = false;
				$chartType = false;
				
				$thisAge = false;
				$thisCircumference = false;
				
				$recordData = \REDCap::getData([
					"records" => $record,
					"project_id" => $project_id,
					"fields" => [$sexField,$ageField,$circumferenceField,$this->getProject()->getRecordIdField()],
					"return_format" => "json",
					"events" => $event_id
				]);
				$recordData = json_decode($recordData,true);
				
				foreach($recordData as $eventDetails) {
					if($eventDetails[$sexField] !== "") {
						$sex = $eventDetails[$sexField];
					}
					
					if($eventDetails["redcap_repeat_instrument"] == $instrument) {
						$age[$eventDetails["redcap_repeat_instance"]] = $eventDetails[$ageField];
						$circumference[$eventDetails["redcap_repeat_instance"]] = $eventDetails[$circumferenceField];
						
						if($eventDetails["redcap_repeat_instance"] == $repeat_instance) {
							$thisAge = $eventDetails[$ageField];
							$thisCircumference = $eventDetails[$circumferenceField];
						}
					}
				}
				
				## Calculate percentile and z-score for storage on form
				if($thisAge !== false && $thisAge !== "" && $thisCircumference !== false && $thisCircumference !== "") {
					$refData = $this->getCsvData();
					$ageDays = round($thisAge * 30.5);
					
					$distributionData = $refData[$sex === "0" ? "2" : "1"][$ageDays];
					
					## Formula for zscore:  Z = [ ((value / M)**L) – 1] / (S * L)
					$zScore = (pow($thisCircumference / $distributionData[1],$distributionData[0]) - 1) /
						($distributionData[0]*$distributionData[2]);
					$percentile = $this->zscoreToPercentile($zScore);
					
					## TODO save to REDCap record
				}
				
				$chartDetails = false;
				foreach(self::$imageDetails as $thisType => $thisImage) {
					foreach($thisImage["logic"] as $thisLogic) {
						$logicVar = $thisLogic[0];
						if($$logicVar !== $thisLogic[2]) {
							continue 2;
						}
					}
					
					$chartType = $thisType;
					$chartDetails = $thisImage;
					break;
				}
				## Calculate x,y coordinates of mark
				if(count($age) > 0 && count($circumference) > 0 && $chartDetails) {
					$startX = $chartDetails["pixelRange"][0];
					$startY = $chartDetails["pixelRange"][1];
					$xWidth = $chartDetails["pixelRange"][2];
					$yWidth = $chartDetails["pixelRange"][3];
					$ageStart = $chartDetails["graphRange"][0];
					$ageRange = $chartDetails["graphRange"][1];
					$headStart = $chartDetails["graphRange"][2];
					$headRange = $chartDetails["graphRange"][3];
					
					
					$instanceX = false;
					$instanceY = false;
					$x = [];
					$y = [];
					foreach($age as $instance => $thisAge) {
						$thisCircumference = $circumference[$instance];
						
						if($thisAge === "" || $thisCircumference === "") {
							continue;
						}
						
						$thisX = round($startX + ($xWidth / ($ageRange - $ageStart)) * ($thisAge - $ageStart));
						$thisY = round($startY + ($yWidth / ($headRange - $headStart)) * ($thisCircumference - $headStart));
						
						$x[] = $thisX;
						$y[] = $thisY;
						
						if($instance == $repeat_instance) {
							$instanceX = $thisX;
							$instanceY = $thisY;
						}
					}
					
					echo "<script type='text/javascript' src='".$this->getUrl("js/functions.js")."'></script>
						<script type='text/javascript'>
								var headCircImagePath = '".$this->getUrl("image.php")."';
								$(document).ready(function() { insertImageChart('".$chartType."',".json_encode($chartField).",".json_encode($instanceX).",".json_encode($instanceY).",".json_encode($x).",",json_encode($y)."); });
						</script>";
				}
				
			}
		}
	}
	
	function getCsvData() {
		$f = fopen(__DIR__."/data/WHOref_d.csv","r");
		
		$headers = fgetcsv($f);
		$headers = array_flip($headers);
		$data = [];
		
		while($row = fgetcsv($f)) {
			$sex = $row[$headers["sex"]];
			$ageDays = $row[$headers["_agedays"]];
			$headL = $row[$headers["_headc_l"]];
			$headM = $row[$headers["_headc_m"]];
			$headS = $row[$headers["_headc_s"]];
			
			$data[$sex][$ageDays] = [$headL,$headM,$headS];
		}
		
		return $data;
	}

	## Source: https://stackoverflow.com/questions/11603228/z-score-to-percentile-in-php
	function erf($x)
	{
		$pi = 3.1415927;
		$a = (8*($pi - 3))/(3*$pi*(4 - $pi));
		$x2 = $x * $x;
		
		$ax2 = $a * $x2;
		$num = (4/$pi) + $ax2;
		$denom = 1 + $ax2;
		
		$inner = (-$x2)*$num/$denom;
		$erf2 = 1 - exp($inner);
		
		return sqrt($erf2);
	}
	
	function zscoreToPercentile($n)
	{
		if($n < 0)
		{
			return (1 - $this->erf($n / sqrt(2)))/2;
		}
		else
		{
			return (1 + $this->erf($n / sqrt(2)))/2;
		}
	}
}