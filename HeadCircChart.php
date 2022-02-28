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
				$age = false;
				$sex = false;
				$circumference = false;
				$chartType = false;
				
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
					
					if(!array_key_exists("redcap_repeat_instance") ||
								($eventDetails["redcap_repeat_instance"] == $repeat_instance &&
								$eventDetails["redcap_repeat_instrument"] == $instrument)) {
						$age = $eventDetails[$ageField];
						$circumference = $eventDetails[$circumferenceField];
					}
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
				if($age && $circumference && $chartDetails) {
					$startX = $chartDetails["pixelRange"][0];
					$startY = $chartDetails["pixelRange"][1];
					$xWidth = $chartDetails["pixelRange"][2];
					$yWidth = $chartDetails["pixelRange"][3];
					$ageStart = $chartDetails["graphRange"][0];
					$ageRange = $chartDetails["graphRange"][1];
					$headStart = $chartDetails["graphRange"][2];
					$headRange = $chartDetails["graphRange"][3];
					
					$x = $startX + ($xWidth / ($ageRange - $ageStart)) * ($age - $ageStart);
					$y = $startY + ($yWidth / ($headRange - $headStart)) * ($circumference - $headStart);
					
					echo "<script type='text/javascript' src='".$this->getUrl("js/functions.js")."'></script>
						<script type='text/javascript'>
								var headCircImagePath = '".$this->getUrl("image.php")."';
								$(document).ready(function() { insertImageChart('".$chartType."','".$chartField."',$x,$y); });
						</script>";
				}
				
			}
		}
	}
}