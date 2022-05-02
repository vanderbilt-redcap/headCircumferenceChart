<?php

## TODO Need to actually calculate the new chart values

$returnValues = [
	"type" => "weight_fenton",
	"type2" => "boys",
	"field" => "weight",
	"x" => 200,
	"y" => 200,
	"xHistory" => [200,300],
	"yHistory" => [200,300],
	"debug" => "1",
];
echo json_encode($returnValues);