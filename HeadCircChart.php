<?php

namespace Vanderbilt\HeadCircChart;

use ExternalModules\AbstractExternalModule;

class HeadCircChart extends AbstractExternalModule
{
	
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
				
				## Calculate x,y coordinates of mark
				if($sex === "0") {
					$chartType = "girls";
				}
				elseif($sex === "1") {
					$chartType = "boys";
				}
				
				if($age && $circumference && $chartType) {
					$startX = $this->getProjectSetting("x-offset-$chartType");
					$startY = $this->getProjectSetting("y-offset-$chartType");
					$xWidth = $this->getProjectSetting("x-width-$chartType");
					$yWidth = $this->getProjectSetting("y-width-$chartType");
					$ageStart = $this->getProjectSetting("x-start-age");
					$ageRange = $this->getProjectSetting("x-end-age");
					$headStart = $this->getProjectSetting("y-start-head");
					$headRange = $this->getProjectSetting("y-end-head");
					
					if($startX !== "" && $startY !== "" && $xWidth !== "" && $yWidth !== "" &&
							$ageStart !== "" && $ageRange !== "" && $headStart !== "" && $headRange !== "") {
						$x = $startX + ($xWidth / ($ageRange - $ageStart)) * ($age - $ageStart);
						$y = $startY + ($yWidth / ($headRange - $headStart)) * ($circumference - $headStart);
						
						echo "<script type='text/javascript' src='".$this->getUrl("js/functions.js")."'></script>
							<script type='text/javascript'>
									var headCircImagePath = '".$this->getUrl("image.php")."';
									$(document).ready(function() { insertImageChart('girls','".$chartField."',$x,$y); });
							</script>";
					}
				}
				
			}
		}
	}
}