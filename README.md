# headCircumferenceChart
Inserts a chart for typical head circumference into REDCap form and marks the current record's location on the chart based on age/head circumference.

##Configuration
###Field to display head circumference/height/weight chart
Charts will be loaded after this field. Field should be on a repeating instrument.

###Field for child's sex
Field for child's gender at birth. Should be on a non-repeating instrument.

###Raw coded value for male/female
Raw value for enum field for child's sex.

###Field for child's gestational age at birth (in weeks)
Must be in units of weeks and must be on a non-repeating instrument. Used to calculate premature status.

###Field for child's height/weight/head circumference/age
Must be on a repeating instrument and should be on the same instrument as the chart display fields
Height must be in cm
Weight must be in kg
Head Circumference in cm
Age must be in months and can be to one or two decimal places to increase accuracy

###Field to save head circumference/height/weight zscore/percentile
If this field is on the same repeating instrument as age and height/weight/head circumference, this field will be filled in with the zscore/percentile based on CDC data for that child's gestational age and sex.

###Debug mode
Used to test the pixel locations for chart images during development, not needed during normal use.