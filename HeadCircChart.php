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
				echo "<script type='text/javascript'>$(document).ready(function() { $('body').append('found it!'); });</script>";
			}
		}
	}
}