<?php

namespace Vanderbilt\HeadCircChart;

use Aws\Panorama\PanoramaClient;
use ExternalModules\AbstractExternalModule;

class HeadCircChart extends AbstractExternalModule
{
	public static $imageDetails = [
		"headCirc" => [
			"boys" => [
				"imageLocation" => __DIR__ . "/images/who_growth_chart_head_circ_boys.PNG",
				"pixelRange" => [135,100,750,493],
				"graphRange" => [0,24,30,51],
				"logic" => [["sex","=","1"]]
			],
			"girls" => [
				"imageLocation" => __DIR__ . "/images/who_growth_chart_head_circ_girls.PNG",
				"pixelRange" => [139,105,750,495],
				"graphRange" => [0,24,30.5,51],
				"logic" => [["sex","=","2"]]
			]
		],
		"height" => [
			"boys" => [
				"imageLocation" => __DIR__ . "/images/who_growth_chart_height_boys.PNG",
				"pixelRange" => [147,113,805,515],
				"graphRange" => [0,24,41,106],
				"logic" => [["sex","=","1"]]
			],
			"girls" => [
				"imageLocation" => __DIR__ . "/images/who_growth_chart_height_girls.PNG",
				"pixelRange" => [147,115,806,515],
				"graphRange" => [0,24,45,95],
				"logic" => [["sex","=","2"]]
			]
		],
		"weight" => [
			"boys" => [
				"imageLocation" => __DIR__ . "/images/who_growth_chart_weight_boys.PNG",
				"pixelRange" => [146,115,806,516],
				"graphRange" => [0,24,1.5,19],
				"logic" => [["sex","=","1"]]
			],
			"girls" => [
				"imageLocation" => __DIR__ . "/images/who_growth_chart_weight_girls.PNG",
				"pixelRange" => [144,110,806,516],
				"graphRange" => [0,24,1.4,15.6],
				"logic" => [["sex","=","2"]]
			]
		],
		"headCirc_cdc" => [
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
		"height_cdc" => [
			"boys" => [
				"imageLocation" => __DIR__ . "/images/cdc_growth_chart_height_boys.PNG",
				"pixelRange" => [59,71,403,578],
				"graphRange" => [0,36,41,106],
				"logic" => [["sex","=","1"]]
			],
			"girls" => [
				"imageLocation" => __DIR__ . "/images/cdc_growth_chart_height_girls.PNG",
				"pixelRange" => [60,69,405,577],
				"graphRange" => [0,36,41,106],
				"logic" => [["sex","=","2"]]
			]
		],
		"weight_cdc" => [
			"boys" => [
				"imageLocation" => __DIR__ . "/images/cdc_growth_chart_weight_boys.PNG",
				"pixelRange" => [51,68,414,604],
				"graphRange" => [0,36,1.5,19],
				"logic" => [["sex","=","1"]]
			],
			"girls" => [
				"imageLocation" => __DIR__ . "/images/cdc_growth_chart_weight_girls.PNG",
				"pixelRange" => [51,70,412,602],
				"graphRange" => [0,36,1.5,19],
				"logic" => [["sex","=","2"]]
			]
		],
		"headCirc_fenton" => [
			"boys" => [
				"imageLocation" => __DIR__ . "/images/fenton_growth_chart_head_circ_boys.PNG",
				"pixelRange" => [95,57,951,558],
				"graphRange" => [24,50,15,45],
				"logic" => [["sex","=","1"]]
			],
			"girls" => [
				"imageLocation" => __DIR__ . "/images/fenton_growth_chart_head_circ_girls.PNG",
				"pixelRange" => [99,54,950,558],
				"graphRange" => [24,50,15,45],
				"logic" => [["sex","=","2"]]
			]
		],
		"height_fenton" => [
			"boys" => [
				"imageLocation" => __DIR__ . "/images/fenton_growth_chart_height_boys.PNG",
				"pixelRange" => [94,47,951,559],
				"graphRange" => [24,50,20,65],
				"logic" => [["sex","=","1"]]
			],
			"girls" => [
				"imageLocation" => __DIR__ . "/images/fenton_growth_chart_height_girls.PNG",
				"pixelRange" => [100,53,950,557],
				"graphRange" => [24,50,20,65],
				"logic" => [["sex","=","2"]]
			]
		],
		"weight_fenton" => [
			"boys" => [
				"imageLocation" => __DIR__ . "/images/fenton_growth_chart_weight_boys.PNG",
				"pixelRange" => [100,51,950,560],
				"graphRange" => [23,50,0,8],
				"logic" => [["sex","=","1"]]
			],
			"girls" => [
				"imageLocation" => __DIR__ . "/images/fenton_growth_chart_weight_girls.PNG",
				"pixelRange" => [99,54,950,558],
				"graphRange" => [23,50,0,7],
				"logic" => [["sex","=","2"]]
			]
		]
	];
	
	function redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance) {
		$headChartField = $this->getProjectSetting("circ-chart-field");
		$heightChartField = $this->getProjectSetting("height-chart-field");
		$weightChartField = $this->getProjectSetting("weight-chart-field");
		$sexField = $this->getProjectSetting("sex-field");
		$gestationalAgeField = $this->getProjectSetting("gestational-age-field");
		$femaleValue = $this->getProjectSetting("female-value");
		$maleValue = $this->getProjectSetting("male-value");
		$ageField = $this->getProjectSetting("age-field");
		$heightField = $this->getProjectSetting("height-field");
		$weightField = $this->getProjectSetting("weight-field");
		$circumferenceField = $this->getProjectSetting("circumference-field");
		$debugMode = $this->getProjectSetting("debug-mode");
		
		$recordData = \REDCap::getData([
			"records" => $record,
			"project_id" => $project_id,
			"fields" => [$sexField,$ageField,$circumferenceField,$gestationalAgeField,$heightField,$weightField,$this->getProject()->getRecordIdField()],
			"return_format" => "json",
			"events" => $event_id
		]);
		$recordData = json_decode($recordData,true);
		$circInstrument = $this->getProject()->getFormForField($headChartField);
		$heightInstrument = $this->getProject()->getFormForField($heightChartField);
		$weightInstrument = $this->getProject()->getFormForField($weightChartField);
		## Process head circumference, height and weight data
		if($sexField && $ageField && $femaleValue !== "" && $maleValue !== "") {
			echo "<script type='text/javascript' src='".$this->getUrl("js/functions.js")."'></script>
			<script type='text/javascript'>
					var imagePath = '".$this->getUrl("image.php")."';
			</script>";
			
			$age = [];
			$circumference = [];
			$height = [];
			$weight = [];
			$sex = false;
			$gestationalAge = false;
			$thisAge = false;
			$chartType = false;
			
			foreach($recordData as $eventDetails) {
				if($eventDetails[$sexField] !== "") {
					$sex = ($eventDetails[$sexField] === (string)$femaleValue ? "2" :
						($eventDetails[$sexField] === (string)$maleValue ? "1" : false));
				}
				if($eventDetails[$gestationalAgeField] !== "") {
					$gestationalAge = $eventDetails[$gestationalAgeField];
				}
				
				if($eventDetails["redcap_repeat_instrument"] == $instrument) {
					if($eventDetails[$ageField] !== "") {
						$age[$eventDetails["redcap_repeat_instance"]] = $eventDetails[$ageField];
						
						if($eventDetails["redcap_repeat_instance"] == $repeat_instance) {
							$thisAge = $eventDetails[$ageField];
						}
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
			
			## Correct Age for premature children
			if($gestationalAge && $gestationalAge <= 36) {
				$thisAge -= (40 - $gestationalAge) * 7 / 30.5;
			}
			
			$useFentonChart = false;
			if($gestationalAge && $gestationalAge <= 36 && (($thisAge * 30.5 / 7) + 40) < 50) {
				foreach($age as $ageKey => $ageValue) {
					$age[$ageKey] = $gestationalAge + $ageValue * 30.5 / 7;
				}
				$useFentonChart = true;
			}
			else if($gestationalAge && $gestationalAge <= 36) {
				foreach($age as $ageKey => $ageValue) {
					$age[$ageKey] = ($gestationalAge - 40) * 7 / 30.5 + $ageValue;
				}
			}
			
			## Insert head circumference chart if data exists
			if(count($circumference) > 0 && $headChartField && $instrument == $circInstrument) {
				$chartDetails = false;
				
				$chartName = "headCirc";
				if($useFentonChart) {
					$chartName .= "_fenton";
				}
				foreach(self::$imageDetails[$chartName] as $thisType => $thisImage) {
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
					
					echo "Trying to use Fenton Chart $instanceX ~ ".$circumference[0]." ~ ".$age[0]."<br />";
					$debugDetails = false;
					if($debugMode) {
						$debugDetails = $chartDetails["pixelRange"];
					}
					
					echo "<script type='text/javascript'>
								$(document).ready(function() { insertImageChart('".$chartName."','".$chartType2."',".json_encode($headChartField).",".json_encode($instanceX).",".json_encode($instanceY).",".json_encode($x).",",json_encode($y).",".json_encode($debugDetails)."); });
						</script>";
				}
			}
			
			## Insert height chart if data exists
			if(count($height) > 0 && $heightChartField && $instrument == $heightInstrument) {
				$chartDetails = false;
				
				$chartName = "height";
				if($useFentonChart) {
					$chartName .= "_fenton";
				}
				foreach(self::$imageDetails[$chartName] as $thisType => $thisImage) {
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
								$(document).ready(function() { insertImageChart('".$chartName."','".$chartType2."',".json_encode($headChartField).",".json_encode($instanceX).",".json_encode($instanceY).",".json_encode($x).",",json_encode($y).",".json_encode($debugDetails)."); });
						</script>";
				}
			}
			
			## Insert weight chart if data exists
			if(count($weight) > 0 && $weightChartField && $instrument == $weightInstrument) {
				$chartDetails = false;
				
				$chartName = "weight";
				if($useFentonChart) {
					$chartName .= "_fenton";
				}
				
				foreach(self::$imageDetails[$chartName] as $thisType => $thisImage) {
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
								$(document).ready(function() { insertImageChart('".$chartName."','".$chartType2."',".json_encode($headChartField).",".json_encode($instanceX).",".json_encode($instanceY).",".json_encode($x).",",json_encode($y).",".json_encode($debugDetails)."); });
						</script>";
				}
			}
		}
	}
	
	function redcap_save_record($project_id,$record,$instrument,$event_id,$gorup_id,$survey_hash,$response_id,$repeat_instance) {
		$sexField = $this->getProjectSetting("sex-field");
		$femaleValue = $this->getProjectSetting("female-value");
		$maleValue = $this->getProjectSetting("male-value");
		$ageField = $this->getProjectSetting("age-field");
		$gestationalAgeField = $this->getProjectSetting("gestational-age-field");
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
			"fields" => [$sexField,$ageField,$gestationalAgeField,$circumferenceField,$heightField,$weightField,$this->getProject()->getRecordIdField()],
			"return_format" => "json",
			"events" => $event_id
		]);
		$recordData = json_decode($recordData,true);
		
		$sex = false;
		$age = false;
		$gestationalAge = false;
		$circumference = false;
		$height = false;
		$weight = false;
		
		foreach($recordData as $eventDetails) {
			if($eventDetails[$sexField] !== "") {
				$sex = ($eventDetails[$sexField] === (string)$femaleValue ? "2" :
					($eventDetails[$sexField] === (string)$maleValue ? "1" : false));
			}
			if($eventDetails[$gestationalAgeField] !== "") {
				$gestationalAge = $eventDetails[$gestationalAgeField];
			}
			
			if($eventDetails["redcap_repeat_instance"] == $repeat_instance) {
				$age = $eventDetails[$ageField];
				if($heightField) {
					$height = $eventDetails[$heightField];
				}
				if($weightField) {
					$weight = $eventDetails[$weightField];
				}
				if($circumferenceField) {
					$circumference = $eventDetails[$circumferenceField];
				}
			}
		}
		
		$dataToSave = [
			$this->getProject()->getRecordIdField() => $record,
			"redcap_repeat_instance" => $repeat_instance,
			"redcap_repeat_instrument" => $instrument
		];
		
		if($age !== false && $age !== "" && $sex !== false) {
			$refData = $this->getCsvData();
			
			## Correct Age for premature children
			if($gestationalAge && $gestationalAge <= 36) {
				$correctedAge = $gestationalAge + ($age * 30.5 / 7);
			}
		
			if($gestationalAge && $gestationalAge <= 36) {
				if($correctedAge < 50) {
					## Premature data is denoted by "1" being prepended to sex
					$sex = "1".$sex;
					$ageDays = (string)round($correctedAge * 7);
					$distributionData = $refData[$sex][$ageDays];
					
					## Premature benchmark data is in grams
					if($weight) {
						$weight *= 1000;
					}
				}
				else {
					$ageDays = (string)(round(($gestationalAge - 40) * 7 + $age * 30.5));
					$distributionData = $refData[$sex][$ageDays];
				}
//				error_log("Found premature, using $ageDays vs $gestationalAge vs $correctedAge and $sex");
//				error_log(var_export($distributionData,true));
			}
			else {
//				## CDC data is on half months, so need to convert to rounded half month
//				$ageMonths = (string)(round($age - 0.5) + 0.5);
//				$distributionData = $refData[$sex][$ageMonths];
				
				$ageDays = (string)(round($age * 30.5));
				$distributionData = $refData[$sex][$ageDays];
//				error_log("Found term birth, using $ageDays vs $gestationalAge vs $age and $sex");
//				error_log(var_export($distributionData,true));
			}
		}
		
		if(count($distributionData) > 0) {
			## Calculate head circumference percentile and z-score for storage on form
			if($circumference !== false && $circumference !== "" && ($circZscoreField || $circPercentileField)) {
				## Formula for zscore:  Z = [ ((value / M)**L) – 1] / (S * L)
				$zScore = round((pow($circumference / $distributionData[1],$distributionData[0]) - 1) /
					($distributionData[0]*$distributionData[2]),3);
				$percentile = round($this->zscoreToPercentile($zScore)*100);
				
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
				$zScore = round((pow($height / $distributionData[4],$distributionData[3]) - 1) /
					($distributionData[3]*$distributionData[5]),3);
				$percentile = round($this->zscoreToPercentile($zScore) * 100);
				
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
				$zScore = round((pow($weight / $distributionData[7],$distributionData[6]) - 1) /
					($distributionData[6]*$distributionData[8]),3);
				$percentile = round($this->zscoreToPercentile($zScore) * 100);
				
				if($weightZscoreField) {
					$dataToSave[$weightZscoreField] = $zScore;
				}
				if($weightPercentileField) {
					$dataToSave[$weightPercentileField] = $percentile;
				}
			}
			
			if(count($dataToSave) > 3) {
				$results = \REDCap::saveData([
					"dataFormat" => "json",
					"data" => json_encode([$dataToSave]),
					"project_id" => $project_id
				]);
//				error_log("Save data results: ".var_export($results,true));
			}
		}
	}
	
	function getCsvData() {
//		$f = fopen(__DIR__."/data/cdcref.csv","r");
		$f = fopen(__DIR__."/data/WHOref_d.csv","r");
		
		$headers = fgetcsv($f);
		$headers = array_flip($headers);
		$data = [];
		
		while($row = fgetcsv($f)) {
			$sex = $row[$headers["sex"]];
			
			## Parsing info for CDC Ref data
//			$premature = $row[$headers["premature"]];
//			$ageMonths = $row[$headers["agemos"]];
//			$ageWeeks = $row[$headers["ageweeks"]];
//			$headL = $row[$headers["_hcirc_l"]];
//			$headM = $row[$headers["_hcirc_m"]];
//			$headS = $row[$headers["_hcirc_s"]];
//			$heightL = $row[$headers["_height_l"]];
//			$heightM = $row[$headers["_height_m"]];
//			$heightS = $row[$headers["_height_s"]];
//			$weightL = $row[$headers["_weight_l"]];
//			$weightM = $row[$headers["_weight_m"]];
//			$weightS = $row[$headers["_weight_s"]];
//			## Premature data is in weeks instead of months
//			if($premature == "1") {
//				$data[$premature.$sex][$ageWeeks] = [$headL,$headM,$headS,$heightL,$heightM,$heightS,$weightL,$weightM,$weightS];
//			}
//			else {
//				$data[$sex][$ageMonths] = [$headL,$headM,$headS,$heightL,$heightM,$heightS,$weightL,$weightM,$weightS];
//			}
			
			## Parsing info for WHO Ref data
			$premature = $row[$headers["premature"]];
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
			
			$data[$premature.$sex][$ageDays] = [$headL,$headM,$headS,$heightL,$heightM,$heightS,$weightL,$weightM,$weightS];
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