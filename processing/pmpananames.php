#!/usr/bin/php
<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/billmanager/include/php');
require_once 'pananames_commands.php';
require_once 'pananames_helper.php';

$longopts = array
(
    "command:",
    "subcommand:",
    "id:",
    "item:",
    "lang:",
    "module:",
    "itemtype:",
    "intname:",
    "param:",
    "value:",
    "runningoperation:",
    "level:",
    "addon:",
    "tld:",
    "searchstring:",
);

$options = getopt('', $longopts);
setToLog(json_encode($options));

try {
    $commander = new Command;
    $commander->runCommand($options);
} catch (Exception $e) {
    $runningoperation = array_key_exists("runningoperation", $options) ? (int)$options['runningoperation'] : 0;
    if ($runningoperation > 0) {
        // save error message for operation in BILLmanager
        LocalQuery("runningoperation.edit", array("sok" => "ok", "elid" => $runningoperation, "errorxml" => $e));
        $item = array_key_exists('item', $options) ? (int)$options['item'] : 0;
        if ($item > 0) {
            // set manual rerung
            LocalQuery("runningoperation.setmanual", array("elid" => $runningoperation));
            // create task
            $task_type = LocalQuery("task.gettype", array("operation" => $command))->task_type;
            if ($task_type != "") {
                LocalQuery("task.edit", array("sok" => "ok", "item" => $item, "runningoperation" => $runningoperation, "type" => $task_type));
            }
        }
    }
    echo $e;
}
