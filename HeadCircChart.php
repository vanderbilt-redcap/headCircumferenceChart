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
				echo "<script type='text/javascript' src='".$this->getUrl("js/functions.js")."'></script>
				<script type='text/javascript'>
						var headCircImagePath = '".$this->getUrl("image.php")."';
						$(document).ready(function() { insertImageChart('girls','".$chartField."'); });
				</script>";
			}
		}
	}
}