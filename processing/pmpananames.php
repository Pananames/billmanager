#!/usr/bin/php
<?php
  

// https://128.199.40.199/billmgr?access=null&activate=on&annually151=120.00&authinfo=root:vetpolreg123&billdaily=off&billhourly=off&billprorata=off&changepolicy=0&changeprolongpolicy=0&chargestoped=on&create_addon=on&custom_tld=off&description_markdown=&description_markdown_ua=&enable_requery_phone=&func=pricelist.add.step2&hide_chargestoped=&hide_custom_tld=&intname=1&itemmax=&itemtype=61&label=&lang=ru&license=null&minperiodlen=&minperiodtype=0&modulepreset=&name=org&name_ua=org&nostopholidays=no&note=&opennotify=null&orderpage=&orderpolicy=0&orderpriority=&processingmodule=1&project=1&prolong151=off&prolong_annually151=&prorataday=&quickorder=off&returnpolicy=0&setup151=100.00&show_addon_image=no&show_on_dashboard=off&showmodulepreset=&suspendpenaltypercent=&suspendpenaltysum151=&suspendpenaltytype=0&suspendperiod=&sv_field=typelist&tld_idn_type=0&tld_max_lenght=63&tld_min_lenght=2&tld_name=&tld_whois_find_string=%20available&tld_whois_host=whois.nic.ac&tld_whois_timeout=&transfer151=&transfer_policy=0&clicked_button=finish&sok=ok
    
// {"setvalues": { "nullmsg" : "-- none --","hide_chargestoped": { "value": ""},"tparams": { "value": "nullon120.00offoffoff00onfinishonoff161runull0orgorgnonull011offoff0100.00nooff0typelist0632 availablewhois.nic.ac0pricelist.add.step2"},"saved_filters": { "value": ""},"tips": { "value": "textarea_resize"} }}    
    
    
   
set_include_path(get_include_path() . PATH_SEPARATOR . '/usr/local/mgr5/include/php');
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
