<?php

namespace Vanderbilt\HeadCircChart;

use Aws\Panorama\PanoramaClient;
use ExternalModules\AbstractExternalModule;

class HeadCircChart extends AbstractExternalModule
{
	// NOTE: this cannot be set in a constructor because REDCap::isLongitudinal may only be called in a project context
	public $isLong = false;
	const CUR_VALUE_FLAG_NAME = "headCircChartEMcurrentValueFlag";

	public static $imageDetails = [
		"headCirc" => [
			"boys" => [
				"imageLocation" => __DIR__ . "/images/who_growth_chart_head_circ_boys.PNG",
				"pixelRange" => [135,100,750,493],
				"graphRange" => [0,24,31.5,52],
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
				"graphRange" => [0,24,45,95],
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
				"graphRange" => [0,24,1.4,16.6],
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
				"pixelRange" => [81,54,951,558],
				"graphRange" => [24,50,15,45],
				"logic" => [["sex","=","1"]]
			],
			"girls" => [
				"imageLocation" => __DIR__ . "/images/fenton_growth_chart_head_circ_girls.PNG",
				"pixelRange" => [82,54,951,558],
				"graphRange" => [24,50,15,45],
				"logic" => [["sex","=","2"]]
			]
		],
		"height_fenton" => [
			"boys" => [
				"imageLocation" => __DIR__ . "/images/fenton_growth_chart_height_boys.PNG",
				"pixelRange" => [84,58,951,559],
				"graphRange" => [24,50,20,65],
				"logic" => [["sex","=","1"]]
			],
			"girls" => [
				"imageLocation" => __DIR__ . "/images/fenton_growth_chart_height_girls.PNG",
				"pixelRange" => [79,52,950,557],
				"graphRange" => [24,50,20,65],
				"logic" => [["sex","=","2"]]
			]
		],
		"weight_fenton" => [
			"boys" => [
				"imageLocation" => __DIR__ . "/images/fenton_growth_chart_weight_boys.PNG",
				"pixelRange" => [80,52,950,560],
				"graphRange" => [23,50,0,8],
				"logic" => [["sex","=","1"]]
			],
			"girls" => [
				"imageLocation" => __DIR__ . "/images/fenton_growth_chart_weight_girls.PNG",
				"pixelRange" => [72,53,951,558],
				"graphRange" => [23,50,0,7],
				"logic" => [["sex","=","2"]]
			]
		]
	];
	
	function redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance) {
		$this->isLong = \REDCap::isLongitudinal();

		$headChartField = $this->getProjectSetting("circ-chart-field");
		$heightChartField = $this->getProjectSetting("height-chart-field");
		$weightChartField = $this->getProjectSetting("weight-chart-field");
		$ageField = $this->getProjectSetting("age-field");
		$heightField = $this->getProjectSetting("height-field");
		$weightField = $this->getProjectSetting("weight-field");
		$circumferenceField = $this->getProjectSetting("circumference-field");
		$debugMode = $this->getProjectSetting("debug-mode");
		
		list($sex,$age,$circumference,$height,$weight,$useFentonChart, $highlightedDatumIndex) = $this->getChartDataForRecord($project_id,$record,$event_id,$instrument,$repeat_instance);
		
		$circInstrument = $this->getProject()->getFormForField($headChartField);
		$heightInstrument = $this->getProject()->getFormForField($heightChartField);
		$weightInstrument = $this->getProject()->getFormForField($weightChartField);

		## Process head circumference, height and weight data
		if(count($age) > 0) {
			echo "<script type='text/javascript'>
					var HCC_Image_Path = '".$this->getUrl("image.php")."';
					var HCC_Update_Path = '".$this->getUrl("updateImage.php")."';
                    var HCC_Age_Field = ".json_encode($ageField).";
                    var HCC_Height_Field = ".json_encode($heightField).";
                    var HCC_Weight_Field = ".json_encode($weightField)."
                    var HCC_Circ_Field = ".json_encode($circumferenceField).";
			</script>
			<script type='text/javascript' src='".$this->getUrl("js/functions.js")."'></script>";
			
			## Insert head circumference chart if data exists
			if(count($circumference) > 0 && $headChartField && $instrument == $circInstrument) {
				$this->addChartToDataEntryForm($sex,"headCirc",$useFentonChart,$age,$circumference,$highlightedDatumIndex,$headChartField,$debugMode);
			}
			
			## Insert height chart if data exists
			if(count($height) > 0 && $heightChartField && $instrument == $heightInstrument) {
				$this->addChartToDataEntryForm($sex,"height",$useFentonChart,$age,$height,$highlightedDatumIndex,$heightChartField,$debugMode);
			}
			
			## Insert weight chart if data exists
			if(count($weight) > 0 && $weightChartField && $instrument == $weightInstrument) {
				$this->addChartToDataEntryForm($sex,"weight",$useFentonChart,$age,$weight,$highlightedDatumIndex,$weightChartField,$debugMode);
			}
		}
	}
	
	function getChartDataForRecord($projectId,$record,$eventId,$instrument,$repeatInstance,$tempAge = false,$tempValue = false,$tempType = false) {
		$highlightedDatumIndex = $repeatInstance;

		$sexField = $this->getProjectSetting("sex-field");
		$gestationalAgeField = $this->getProjectSetting("gestational-age-field");
		$femaleValue = $this->getProjectSetting("female-value");
		$maleValue = $this->getProjectSetting("male-value");
		$ageField = $this->getProjectSetting("age-field");
		$heightField = $this->getProjectSetting("height-field");
		$weightField = $this->getProjectSetting("weight-field");
		$circumferenceField = $this->getProjectSetting("circumference-field");
		
		$sex  = false;
		$age = [];
		$circumference = [];
		$height = [];
		$weight = [];
		$useFentonChart = false;
		
		$recordData = $this->getRecordData($projectId,$record,$eventId, $repeatInstance);

		if($femaleValue === "" || $maleValue === "" || $sexField === "" || $ageField === "") {
			return [$sex,$age,$circumference,$height,$weight,$useFentonChart];
		}
		
		$foundInstance = false;
		$i = 1;
		foreach($recordData as $eventDetails) {
			if($eventDetails[$sexField] !== "") {
				$sex = ($eventDetails[$sexField] === (string)$femaleValue ? "2" :
					($eventDetails[$sexField] === (string)$maleValue ? "1" : false));
			}
			if($eventDetails[$gestationalAgeField] !== "") {
				$gestationalAge = $eventDetails[$gestationalAgeField];
			}

			if ($eventDetails[self::CUR_VALUE_FLAG_NAME]) {
				$foundInstance = true;
				$highlightedDatumIndex = $i;
				if ($tempType) { ${'temp' . ucfirst($tempType)} = $tempValue; }
				$thisAge = ($tempAge) ?: $eventDetails[$ageField];

				$highlightedAge = $thisAge;
			} else {
				$thisAge = $eventDetails[$ageField];
				// reset values to prevent overriding all future y axis values
				$tempHeadCirc = false;
				$tempHeight = false;
				$tempWeight = false;
			}

			$age[$i] = $thisAge;
			$circumference[$i] = ($tempHeadCirc) ?: $eventDetails[$circumferenceField];
			$height[$i] = ($tempHeight) ?: $eventDetails[$heightField];
			$weight[$i] = ($tempWeight) ?: $eventDetails[$weightField];

			$i++;
		}
		
		if(!$foundInstance && !$this->isLong) {
			$highlightedAge = $tempAge;
			$age[$repeatInstance] = $tempAge;
			if($tempType == "headCirc") {
				$circumference[$repeatInstance] = $tempValue;
			}
			if($tempType == "height") {
				$circumference[$repeatInstance] = $tempValue;
			}
			if($tempType == "weight") {
				$circumference[$repeatInstance] = $tempValue;
			}
		}
		
		## Correct Age for premature children
		if($gestationalAge && $gestationalAge <= 36) {
			$highlightedAge -= (40 - $gestationalAge) * 7 / 30.5;
		}
		
		if($gestationalAge && $gestationalAge <= 36 && (($highlightedAge * 30.5 / 7) + 40) < 50) {
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
		
		return [$sex,$age,$circumference,$height,$weight,$useFentonChart, $highlightedDatumIndex];
	}
	
	function addChartToDataEntryForm($sex,$chartType,$useFentonChart,$age,$values,$highlightedDatumIndex,$chartField,$debugMode) {
		$chartSex = "";
		$chartDataSet = "";
		$chartDetails = false;
		if($useFentonChart) {
			$chartDataSet .= "_fenton";
		}
		
		foreach(self::$imageDetails[$chartType.$chartDataSet] as $thisSex => $thisImage) {
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
			list($instanceX,$instanceY,$x,$y) = $this->calculateXY($chartDetails,$age,$values,$highlightedDatumIndex);
			
			$debugDetails = false;
			if($debugMode) {
				$debugDetails = $chartDetails["pixelRange"];
			}
			
			echo "<script type='text/javascript'>
					$(document).ready(function() { insertImageChart('".$chartType."','".$chartDataSet."','".$chartSex."',".json_encode($chartField).",".json_encode($instanceX).",".json_encode($instanceY).",".json_encode($x).",",json_encode($y).",".json_encode($debugDetails)."); });
				</script>";
		}
	}
	
	function redcap_save_record($project_id,$record,$instrument,$event_id,$group_id,$survey_hash,$response_id,$repeat_instance) {
		$this->calculateAndSaveScores($project_id, $record, $instrument, $event_id, $repeat_instance);
	}

	function calculateAndSaveScores($project_id, $record, $instrument, $event_id, $repeat_instance) {
		// TODO: split this function into separate calculation and saveData functions
		// Refactor to allow calculation to be an AJAX call, enable injection of values directly on frontend
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

		$thisFormFields = $this->framework->getFieldNames($instrument);
		$calcFieldsPresent = [
			$circZscoreField => false,
			$circPercentileField => false,
			$heightZscoreField => false,
			$heightPercentileField => false,
			$weightZscoreField => false,
			$weightPercentileField => false
		];

		// HACK: flag fields present in current form to prevent error from saving data outside of current instrument
		array_walk($calcFieldsPresent, function(&$isPresent, $fieldName) use ($thisFormFields) {
			if( in_array($fieldName, $thisFormFields) ) {
				$isPresent = true;
			}
		});
		if (!in_array(true, $calcFieldsPresent, true)) { return; }

		$recordData = $this->getRecordData($project_id, $record, $event_id, $repeat_instance);
		
		$sex = false;
		$age = false;
		$gestationalAge = false;
		$circumference = false;
		$height = false;
		$weight = false;
		$distributionData = false;

		$isRepeat = false;
		
		foreach($recordData as $eventDetails) {
			if($eventDetails[$sexField] !== "") {
				$sex = ($eventDetails[$sexField] === (string)$femaleValue ? "2" :
					($eventDetails[$sexField] === (string)$maleValue ? "1" : false));
			}
			if($eventDetails[$gestationalAgeField] !== "") {
				$gestationalAge = $eventDetails[$gestationalAgeField];
			}
			
			if($eventDetails[self::CUR_VALUE_FLAG_NAME]) {
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
				if($eventDetails["redcap_repeat_instrument"] !== "") {
					$isRepeat = true;
				}
			}
		}
		## TODO Need to validate the height, weight and circumference are integers
		$dataToSave = [
			$this->getProject()->getRecordIdField() => $record
		];

		if ($isRepeat) {
			$dataToSave["redcap_repeat_instance"] = $repeat_instance;
			$dataToSave["redcap_repeat_instrument"] = $instrument;
		}
		
		if($age !== false && $age !== "" && $sex !== false) {
			$refData = $this->getCsvData();
			
			## Correct Age for premature children
			if($gestationalAge && $gestationalAge <= 36) {
				$correctedAge = $gestationalAge + ($age * 30.5 / 7);
				
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
		
		if($distributionData && count($distributionData) > 0) {
			## Calculate head circumference percentile and z-score for storage on form
			if($circumference !== false && $circumference !== "" && ($circZscoreField || $circPercentileField)) {
				## Formula for zscore:  Z = [ ((value / M)**L) – 1] / (S * L)
				$zScore = round((pow($circumference / $distributionData[1],$distributionData[0]) - 1) /
					($distributionData[0]*$distributionData[2]),3);
				$percentile = round($this->zscoreToPercentile($zScore)*100);
				
				if($circZscoreField && $calcFieldsPresent[$circZscoreField]) {
					$dataToSave[$circZscoreField] = $zScore;
				}
				if($circPercentileField && $calcFieldsPresent[$circPercentileField]) {
					$dataToSave[$circPercentileField] = $percentile;
				}
			}
			
			## Calculate height percentile and z-score for storage on form
			if($height !== false && $height !== "" && ($heightZscoreField || $heightPercentileField)) {
				## Formula for zscore:  Z = [ ((value / M)**L) – 1] / (S * L)
				$zScore = round((pow($height / $distributionData[4],$distributionData[3]) - 1) /
					($distributionData[3]*$distributionData[5]),3);
				$percentile = round($this->zscoreToPercentile($zScore) * 100);
				
				if($heightZscoreField && $calcFieldsPresent[$heightZscoreField]) {
					$dataToSave[$heightZscoreField] = $zScore;
				}
				if($heightPercentileField && $calcFieldsPresent[$heightPercentileField]) {
					$dataToSave[$heightPercentileField] = $percentile;
				}
			}
			
			## Calculate weight percentile and z-score for storage on form
			if($weight !== false && $weight !== "" && ($weightZscoreField || $weightPercentileField)) {
				## Formula for zscore:  Z = [ ((value / M)**L) – 1] / (S * L)
				$zScore = round((pow($weight / $distributionData[7],$distributionData[6]) - 1) /
					($distributionData[6]*$distributionData[8]),3);
				$percentile = round($this->zscoreToPercentile($zScore) * 100);
				
				if($weightZscoreField && $calcFieldsPresent[$weightZscoreField]) {
					$dataToSave[$weightZscoreField] = $zScore;
				}
				if($weightPercentileField && $calcFieldsPresent[$weightPercentileField]) {
					$dataToSave[$weightPercentileField] = $percentile;
				}
			}

			$dataSaveArr = [
				"data" => [$record => [
					$event_id => $dataToSave
				]],
				"project_id" => $project_id
			];
			if ($isRepeat) {
				$dataSaveArr[$record][$event_id]["redcap_repeat_instance"] = $repeat_instance;
				$dataSaveArr[$record][$event_id]["redcap_repeat_instrument"] = $instrument;
			}
			if(count($dataToSave) >= 2) {
				$results = \REDCap::saveData($dataSaveArr);
//				error_log("Save data results: ".var_export($results,true));
			}
		}
	}
	
	function getRecordData($projectId, $record, $eventId, $repeatInstance = 1) {
		$sexField = $this->getProjectSetting("sex-field");
		$ageField = $this->getProjectSetting("age-field");
		$gestationalAgeField = $this->getProjectSetting("gestational-age-field");
		$heightField = $this->getProjectSetting("height-field");
		$weightField = $this->getProjectSetting("weight-field");
		$circumferenceField = $this->getProjectSetting("circumference-field");

		$getDataParams = [
			"records" => $record,
			"project_id" => $projectId,
			"fields" => [$sexField,$ageField,$gestationalAgeField,$circumferenceField,$heightField,$weightField,$this->getProject()->getRecordIdField()],
			"return_format" => "json",
		];

		$this->isLong = \REDCap::isLongitudinal();

		if (!$this->isLong) { $getDataParams["events"] = $eventId; }

		$recordData = \REDCap::getData($getDataParams);
		$recordData = json_decode($recordData,true);

		// copied from Records::getData as Proj object form $this->framework->getProject does not have getUniqueEventNames function
		$Proj = new \Project(PROJECT_ID);
		$eventLabel = $Proj->getUniqueEventNames()[$eventId];

		// ensure recordData is sorted by age so index can be reliably used for highlighting
		$ageCol = array_column($recordData, $ageField);
		array_multisort($ageCol, SORT_ASC, $recordData);

		// add flag marking value associated with actively viewed instrument/instance
		// flag is set here as it's needed for both addChartToDataEntryForm and computation of z-score in redcap_save_record
		// TODO: consider checking for presence of CUR_VALUE_FLAG_NAME to prevent unlikely naming clash
		array_walk($recordData,
				   function(&$eventDetails) use ($repeatInstance, $eventLabel) {
					   $isCurrentValue = false;

					   if ($eventLabel === $eventDetails["redcap_event_name"]) {
						   if (($eventDetails["redcap_repeat_instrument"] !== "")) {
							   if($eventDetails["redcap_repeat_instance"] == $repeatInstance) {
								   $isCurrentValue = true;
							   }
						   } elseif ($this->isLong) { $isCurrentValue = true; }
					   }
					   $eventDetails[self::CUR_VALUE_FLAG_NAME] = $isCurrentValue;
				   });

		return $recordData;
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
			
			if($thisXValue === "" || $thisYValue === "" ||
					($thisYValue < $startYValue) ||
					($thisYValue > $endYValue) ||
					($thisXValue < $startXValue) ||
					($thisXValue > $endXValue)) {
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
