<?php

namespace Vanderbilt\HeadCircChart;

use ExternalModules\AbstractExternalModule;

class HeadCircChart extends AbstractExternalModule
{
	public static $imageDetails = [
		"headCirc" => [
			"boys" => [
				"imageLocation" => __DIR__ . "/images/cdc_growth_chart_head_circ_boys.PNG",
				"pixelRange" => [58,88,407,479],
				"graphRange" => [0,36,30,53],
				"logic" => [["sex","=","1"]]
			],
			"girls" => [
				"imageLocation" => __DIR__ . "/images/cdc_growth_chart_head_circ_girls.PNG",
				"pixelRange" => [65,87,402,479],
				"graphRange" => [0,36,30,53],
				"logic" => [["sex","=","2"]]
			]
		],
		"height" => [
			"boys" => [
				"imageLocation" => __DIR__ . "/images/cdc_growth_chart_height_boys.PNG",
				"pixelRange" => [60,71,406,578],
				"graphRange" => [0,36,41,106],
				"logic" => [["sex","=","1"]]
			],
			"girls" => [
				"imageLocation" => __DIR__ . "/images/cdc_growth_chart_height_girls.PNG",
				"pixelRange" => [61,69,406,578],
				"graphRange" => [0,36,30,53],
				"logic" => [["sex","=","2"]]
			]
		],
		"weight" => [
			"boys" => [
				"imageLocation" => __DIR__ . "/images/cdc_growth_chart_weight_boys.PNG",
				"pixelRange" => [52,66,416,606],
				"graphRange" => [0,36,1.5,19],
				"logic" => [["sex","=","1"]]
			],
			"girls" => [
				"imageLocation" => __DIR__ . "/images/cdc_growth_chart_weight_girls.PNG",
				"pixelRange" => [51,70,416,606],
				"graphRange" => [0,36,1.5,19],
				"logic" => [["sex","=","2"]]
			]
		]
	];
	
	function redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance) {
		$headChartField = $this->getProjectSetting("circ-chart-field");
		$heightChartField = $this->getProjectSetting("height-chart-field");
		$weightChartField = $this->getProjectSetting("weight-chart-field");
		$sexField = $this->getProjectSetting("sex-field");
		$ageField = $this->getProjectSetting("age-field");
		$heightField = $this->getProjectSetting("height-field");
		$weightField = $this->getProjectSetting("weight-field");
		$circumferenceField = $this->getProjectSetting("circumference-field");
		$debugMode = $this->getProjectSetting("debug-mode");
		
		$recordData = \REDCap::getData([
			"records" => $record,
			"project_id" => $project_id,
			"fields" => [$sexField,$ageField,$circumferenceField,$heightField,$weightField,$this->getProject()->getRecordIdField()],
			"return_format" => "json",
			"events" => $event_id
		]);
		$recordData = json_decode($recordData,true);
		$circInstrument = $this->getProject()->getFormForField($headChartField);
		$heightInstrument = $this->getProject()->getFormForField($heightChartField);
		$weightInstrument = $this->getProject()->getFormForField($weightChartField);
		
		## Process head circumference, height and weight data
		if($sexField && $ageField) {
			echo "<script type='text/javascript' src='".$this->getUrl("js/functions.js")."'></script>
			<script type='text/javascript'>
					var imagePath = '".$this->getUrl("image.php")."';
			</script>";
			
			$age = [];
			$circumference = [];
			$height = [];
			$weight = [];
			$sex = false;
			$chartType = false;
			
			foreach($recordData as $eventDetails) {
				if($eventDetails[$sexField] !== "") {
					$sex = $eventDetails[$sexField];
				}
				
				if($eventDetails["redcap_repeat_instrument"] == $instrument) {
					if($eventDetails[$ageField] !== "") {
						$age[$eventDetails["redcap_repeat_instance"]] = $eventDetails[$ageField];
					}
					if($circumferenceField && $eventDetails[$circumferenceField] !== "") {
						$circumference[$eventDetails["redcap_repeat_instance"]] = $eventDetails[$circumferenceField];
					}
					if($heightField && $eventDetails[$heightField] !== "") {
						$height[$eventDetails["redcap_repeat_instance"]] = $eventDetails[$heightField];
					}
					if($weightField && $eventDetails[$weightField] !== "") {
						$weight[$eventDetails["redcap_repeat_instance"]] = $eventDetails[$weightField];
					}
				}
			}
			
			## Insert head circumference chart if data exists
			if(count($circumference) > 0 && $headChartField && $instrument == $circInstrument) {
				$chartDetails = false;
				foreach(self::$imageDetails["headCirc"] as $thisType => $thisImage) {
					foreach($thisImage["logic"] as $thisLogic) {
						$logicVar = $thisLogic[0];
						if($$logicVar !== $thisLogic[2]) {
							continue 2;
						}
					}
					
					$chartType2 = $thisType;
					$chartDetails = $thisImage;
					break;
				}
				
				if($chartDetails) {
					list($instanceX,$instanceY,$x,$y) = $this->calculateXY($chartDetails,$age,$circumference,$repeat_instance);
					
					$debugDetails = false;
					if($debugMode) {
						$debugDetails = $chartDetails["pixelRange"];
					}
					
					echo "<script type='text/javascript'>
								$(document).ready(function() { insertImageChart('headCirc','".$chartType2."',".json_encode($headChartField).",".json_encode($instanceX).",".json_encode($instanceY).",".json_encode($x).",",json_encode($y).",".json_encode($debugDetails)."); });
						</script>";
				}
			}
			
			## Insert height chart if data exists
			if(count($height) > 0 && $heightChartField && $instrument == $heightInstrument) {
				$chartDetails = false;
				foreach(self::$imageDetails["height"] as $thisType => $thisImage) {
					foreach($thisImage["logic"] as $thisLogic) {
						$logicVar = $thisLogic[0];
						if($$logicVar !== $thisLogic[2]) {
							continue 2;
						}
					}
					
					$chartType2 = $thisType;
					$chartDetails = $thisImage;
					break;
				}
				
				if($chartDetails) {
					list($instanceX,$instanceY,$x,$y) = $this->calculateXY($chartDetails,$age,$height,$repeat_instance);
					
					$debugDetails = false;
					if($debugMode) {
						$debugDetails = $chartDetails["pixelRange"];
					}
					
					echo "<script type='text/javascript'>
								$(document).ready(function() { insertImageChart('height','".$chartType2."',".json_encode($headChartField).",".json_encode($instanceX).",".json_encode($instanceY).",".json_encode($x).",",json_encode($y).",".json_encode($debugDetails)."); });
						</script>";
				}
			}
			
			## Insert weight chart if data exists
			if(count($weight) > 0 && $weightChartField && $instrument == $weightInstrument) {
				$chartDetails = false;
				foreach(self::$imageDetails["weight"] as $thisType => $thisImage) {
					foreach($thisImage["logic"] as $thisLogic) {
						$logicVar = $thisLogic[0];
						if($$logicVar !== $thisLogic[2]) {
							continue 2;
						}
					}
					
					$chartType2 = $thisType;
					$chartDetails = $thisImage;
					break;
				}
				
				if($chartDetails) {
					list($instanceX,$instanceY,$x,$y) = $this->calculateXY($chartDetails,$age,$weight,$repeat_instance);
					
					$debugDetails = false;
					if($debugMode) {
						$debugDetails = $chartDetails["pixelRange"];
					}
					
					echo "<script type='text/javascript'>
								$(document).ready(function() { insertImageChart('weight','".$chartType2."',".json_encode($headChartField).",".json_encode($instanceX).",".json_encode($instanceY).",".json_encode($x).",",json_encode($y).",".json_encode($debugDetails)."); });
						</script>";
				}
			}
		}
	}
	
	function redcap_save_record($project_id,$record,$instrument,$event_id,$gorup_id,$survey_hash,$response_id,$repeat_instance) {
		$sexField = $this->getProjectSetting("sex-field");
		$ageField = $this->getProjectSetting("age-field");
		$heightField = $this->getProjectSetting("height-field");
		$weightField = $this->getProjectSetting("weight-field");
		$circumferenceField = $this->getProjectSetting("circumference-field");
		$circZscoreField = $this->getProjectSetting("circ-zscore-field");
		$circPercentileField = $this->getProjectSetting("circ-percentile-field");
		$heightZscoreField = $this->getProjectSetting("height-zscore-field");
		$heightPercentileField = $this->getProjectSetting("height-percentile-field");
		$weightZscoreField = $this->getProjectSetting("weight-zscore-field");
		$weightPercentileField = $this->getProjectSetting("weight-percentile-field");
		
		$recordData = \REDCap::getData([
			"records" => $record,
			"project_id" => $project_id,
			"fields" => [$sexField,$ageField,$circumferenceField,$heightField,$weightField,$this->getProject()->getRecordIdField()],
			"return_format" => "json",
			"events" => $event_id
		]);
		
		$sex = false;
		$age = false;
		$circumference = false;
		$height = false;
		$weight = false;
		
		foreach($recordData as $eventDetails) {
			if($eventDetails[$sexField] !== "") {
				$sex = $eventDetails[$sexField];
			}
			
			if($eventDetails["redcap_repeat_instrument"] == $repeat_instance) {
				$age = $eventDetails[$ageField];
				$height = $eventDetails[$heightField];
				$weight = $eventDetails[$weightField];
				$circumference = $eventDetails[$circumferenceField];
			}
		}
		
		$dataToSave = [
			"redcap_repeat_instance" => $repeat_instance,
			"redcap_repeat_instrument" => $instrument,
			"redcap_event_id" => $event_id
		];
		
		if($age !== false && $age !== "") {
			$refData = $this->getCsvData();
			$ageDays = round($age * 30.5);
			$distributionData = $refData[$sex][$ageDays];
		}
		
		## Calculate head circumference percentile and z-score for storage on form
		if($circumference !== false && $circumference !== "" && ($circZscoreField || $circPercentileField)) {
			## Formula for zscore:  Z = [ ((value / M)**L) – 1] / (S * L)
			$zScore = (pow($circumference / $distributionData[1],$distributionData[0]) - 1) /
				($distributionData[0]*$distributionData[2]);
			$percentile = $this->zscoreToPercentile($zScore);
			
			if($circZscoreField) {
				$dataToSave[$circZscoreField] = $zScore;
			}
			if($circPercentileField) {
				$dataToSave[$circPercentileField] = $percentile;
			}
		}
		
		## Calculate height percentile and z-score for storage on form
		if($height !== false && $height !== "" && ($heightZscoreField || $heightPercentileField)) {
			## Formula for zscore:  Z = [ ((value / M)**L) – 1] / (S * L)
			$zScore = (pow($height / $distributionData[4],$distributionData[3]) - 1) /
				($distributionData[3]*$distributionData[5]);
			$percentile = $this->zscoreToPercentile($zScore);
			
			if($heightZscoreField) {
				$dataToSave[$heightZscoreField] = $zScore;
			}
			if($heightPercentileField) {
				$dataToSave[$heightPercentileField] = $percentile;
			}
		}
		
		## Calculate weight percentile and z-score for storage on form
		if($weight !== false && $weight !== "" && ($weightZscoreField || $weightPercentileField)) {
			## Formula for zscore:  Z = [ ((value / M)**L) – 1] / (S * L)
			$zScore = (pow($weight / $distributionData[7],$distributionData[6]) - 1) /
				($distributionData[6]*$distributionData[8]);
			$percentile = $this->zscoreToPercentile($zScore);
			
			if($weightZscoreField) {
				$dataToSave[$weightZscoreField] = $zScore;
			}
			if($weightPercentileField) {
				$dataToSave[$weightPercentileField] = $percentile;
			}
		}
		
		\REDCap::saveData([
			"dataFormat" => "json",
			"data" => json_encode($dataToSave),
			"project_id" => $project_id
		]);
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
			$heightL = $row[$headers["_len_l"]];
			$heightM = $row[$headers["_len_m"]];
			$heightS = $row[$headers["_len_s"]];
			$weightL = $row[$headers["_wei_l"]];
			$weightM = $row[$headers["_wei_m"]];
			$weightS = $row[$headers["_wei_s"]];
			
			$data[$sex][$ageDays] = [$headL,$headM,$headS,$heightL,$heightM,$heightS,$weightL,$weightM,$weightS];
		}
		
		return $data;
	}
	
	function calculateXY($chartDetails,$xValues,$yValues,$thisInstance) {
		$startX = $chartDetails["pixelRange"][0];
		$startY = $chartDetails["pixelRange"][1];
		$xWidth = $chartDetails["pixelRange"][2];
		$yWidth = $chartDetails["pixelRange"][3];
		$startXValue = $chartDetails["graphRange"][0];
		$endXValue = $chartDetails["graphRange"][1];
		$startYValue = $chartDetails["graphRange"][2];
		$endYValue = $chartDetails["graphRange"][3];
		
		$instanceX = false;
		$instanceY = false;
		$x = [];
		$y = [];
		
		foreach($xValues as $instance => $thisXValue) {
			$thisYValue = "";
			if(array_key_exists($instance,$yValues)) {
				$thisYValue = $yValues[$instance];
			}
			
			if($thisXValue === "" || $thisYValue === "") {
				continue;
			}
			
			$thisX = round($startX + ($xWidth / ($endXValue - $startXValue)) * ($thisXValue - $startXValue));
			$thisY = round($startY + ($yWidth / ($endYValue - $startYValue)) * ($thisYValue - $startYValue));

			$x[] = $thisX;
			$y[] = $thisY;
			
			if($instance == $thisInstance) {
				$instanceX = $thisX;
				$instanceY = $thisY;
			}
		}
		
		return [$instanceX,$instanceY,$x,$y];
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