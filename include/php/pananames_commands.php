<?php

class Command {
    
    public function __call($name, $arguments) {
        echo 'Unknown command ' . $name;
        exit;
    }
    
    public function runCommand($options)
    {
        $commandName = explode('_', $options['command']);
        $commandName = $commandName[0] . ucfirst($commandName[1]);
        $item = array_key_exists('item', $options) ? (int)$options['item'] : 0;
        $this->$commandName($commandName, $item);
        return;
    }
    
    private function features()
    {
        echo '<?xml version="1.0" encoding="UTF-8"?>
        <doc>
          <itemtypes>
            <itemtype name="domain"/>
          </itemtypes>
          <params>
            <param name="url"/>
            <param name="signature" crypted="yes"/>
          </params>
          <features>
             <feature name="tune_connection"/>
             <feature name="check_connection"/>
             <feature name="open"/>
             <feature name="close"/>
             <feature name="sync_server"/>
          </features>
        </doc>';
        return;
    }
    
    private function tuneConnection()
    {
        $connection_form = simplexml_load_string(file_get_contents('php://stdin'));
        $lang = $connection_form->addChild('slist');
        $lang->addAttribute('name', 'whois_lang');
        $lang->addChild('msg', 'ru');
        $lang->addChild('msg', 'en');
        echo $connection_form->asXML();
        return;
    }
    
    private function checkConnection()
    {
        $connection_param = simplexml_load_string(file_get_contents('php://stdin'));
	try {
	    $signature = $connection_param->processingmodule->signature;
            $url = $connection_param->processingmodule->url . 'account/balance';
    	    $param = [];
    	    $requesttype = 'GET';
    	    $header = ['SIGNATURE: ' . $signature, 'accept: application/json', 'content-type: application/json'];
    	    $result = json_decode(HttpQuery($url, $param, $requesttype, '', '', $header));

            setToLog(json_encode($result));
            
    	    if (isset($result->errors)) {
        	$return = '<?xml version="1.0" encoding="UTF-8"?>
        	<doc>
        	  <error type="xml" object="xpath" report="yes" lang="ru">
        	    <param name="object" type="msg" msg="Ошибка авторизации">signature</param>
        	    <param name="value">bad auth</param>
        	      <stack>
        	        <action level="30" user="root">autherror</action>
        	      </stack>
        	      <group>Возникла ошибка при попытке авторизации</group>
        	      <msg>Возникла ошибка при авторизации через signature (Api key authorization). Ошибка "bad auth"</msg>
        	  </error>
        	</doc>';
    	    } else {
        	$data = $result->data;
        	$return = '<?xml version="1.0" encoding="UTF-8"?>
        	<doc>
        	  <ok/>
        	</doc>';
    	    }
        } catch (Exception $e) {
            throw new Error('Error signature checking');
        }
        echo $return;
        return;
    }
    
    private function open($options)
    {
        $db = GetConnection();
        $iid = $options['item'];
        $item_param = ItemParam($db, $iid);
        $profile_params = ItemProfiles($db, $iid, $item_param['item_module']);

        $params = [
            'domain' => $item_param['domain'],
            'period' => round($item_param['item_period'] / 12, 1),
            'whois_privacy' => true,
            'registrant_contact' => [
                'org' => '',
                'name' => $profile_params['owner']['firstname'] . ' ' . $profile_params['owner']['middlename'] . ' ' . $profile_params['owner']['lastname'],
                'email' => $profile_params['owner']['email'],
                'address' => $profile_params['owner']['location_address'],
                'city' => $profile_params['owner']['location_city'],
                'state' => $profile_params['owner']['location_state'],
                'zip' => $profile_params['owner']['location_postcode'],
                'country' => getCountryISO($db, $profile_params['owner']['location_country']),
                'phone' => str_replace([' (', ') ', '-'], ['.', '', ''], $profile_params['owner']['phone'])
            ],    
            'admin_contact' => [
                'org' => '',
                'name' => $profile_params['admin']['firstname'] . ' ' . $profile_params['admin']['middlename'] . ' ' . $profile_params['admin']['lastname'],
                'email' => $profile_params['admin']['email'],
                'address' => $profile_params['admin']['location_address'],
                'city' => $profile_params['admin']['location_city'],
                'state' => $profile_params['admin']['location_state'],
                'zip' => $profile_params['admin']['location_postcode'],
                'country' => getCountryISO($db, $profile_params['admin']['location_country']),
                'phone' => str_replace([' (', ') ', '-'], ['.', '', ''], $profile_params['admin']['phone'])
            ],
            'tech_contact' => [
                'org' => '',
                'name' => $profile_params['tech']['firstname'] . ' ' . $profile_params['tech']['middlename'] . ' ' . $profile_params['tech']['lastname'],
                'email' => $profile_params['tech']['email'],
                'address' => $profile_params['tech']['location_address'],
                'city' => $profile_params['tech']['location_city'],
                'state' => $profile_params['tech']['location_state'],
                'zip' => $profile_params['tech']['location_postcode'],
                'country' => getCountryISO($db, $profile_params['tech']['location_country']),
                'phone' => str_replace([' (', ') ', '-'], ['.', '', ''], $profile_params['tech']['phone'])
            ],
            'billing_contact' => [
                'org' => '',
                'name' => $profile_params['bill']['firstname'] . ' ' . $profile_params['bill']['middlename'] . ' ' . $profile_params['bill']['lastname'],
                'email' => $profile_params['bill']['email'],
                'address' => $profile_params['bill']['location_address'],
                'city' => $profile_params['bill']['location_city'],
                'state' => $profile_params['bill']['location_state'],
                'zip' => $profile_params['bill']['location_postcode'],
                'country' => getCountryISO($db, $profile_params['bill']['location_country']),
                'phone' => str_replace([' (', ') ', '-'], ['.', '', ''], $profile_params['bill']['phone'])
            ],
            'premium_price' => 0,
            'claims_accepted' => true
        ];
    
        $url = getApiUrl($db, $item_param["item_module"]) . 'domains';
    	$requesttype = 'POST';
    	$header = ['SIGNATURE: ' .  getSignature($db, $item_param["item_module"]), 'accept: application/json', 'content-type: application/json'];
    	
        setToLog('URL for open ' . $url);
	setToLog('SIGNATURE for open ' . getSignature($db, $item_param["item_module"]));
        setToLog('POST ' . json_encode($params));
        
        $result = json_decode(HttpQuery($url, json_encode($params), $requesttype, '', '', $header));

        setToLog(json_encode($result));
        
    	if (!isset($result->errors)) {
            LocalQuery("domain.open", array("elid" => $iid, "sok" => "ok"));
        } else {
            throw new Error("query", 'Error registration domain on Pananames', $result->errors[0]->description);
        }
        return;
    }
    
    private function close($options)
    {
        $db = GetConnection();
        $iid = $options['item'];
        $item_param = ItemParam($db, $iid);
    
        $url = getApiUrl($db, $item_param["item_module"]) . 'domains/' . $item_param['domain'];
    	$param = [];
    	$requesttype = 'DELETE';
    	$header = ['SIGNATURE: ' . getSignature($db, $item_param["item_module"]), 'accept: application/json', 'content-type: application/json'];
    	
        setToLog('URL for close ' . $url);
	setToLog('SIGNATURE for close ' . getSignature($db, $item_param["item_module"]));
        
        $result = json_decode(HttpQuery($url, $param, $requesttype, '', '', $header));

        setToLog(json_encode($result));
        
    	if (!isset($result->errors)) {
    	    LocalQuery("service.postclose", array("elid" => $iid, "sok" => "ok", ));
	} else {
            setToLog('Error delete domain on Pananames', $result->errors[0]->description);
        }
        return;
    }
    
    private function syncItem($options)
    {
        $db = GetConnection();
        $iid = $options['item'];
        $item_param = ItemParam($db, $iid);
        
        $url =  getApiUrl($db, $item_param["item_module"]) . 'domains/' . $item_param['domain'];
    	$param = [];
    	$requesttype = 'GET';
    	$header = ['SIGNATURE: ' . getSignature($db, $item_param["item_module"]), 'accept: application/json', 'content-type: application/json'];
    	$result = json_decode(HttpQuery($url, $param, $requesttype, '', '', $header));

	setToLog('URL for sync_item ' . $url);
	setToLog('SIGNATURE for sync_item ' . getSignature($db, $item_param["item_module"]));
	setToLog('Domain info for sync_item ' . json_encode($result));
	
    	if (!isset($result->errors)) {
	    if ($result->data->status == 'ok') {
	        setToLog('$result->data->status = ' . $result->data->status);
                LocalQuery("service.postresume", array("elid" => $iid, "sok" => "ok", ));
                LocalQuery("service.setstatus", array("elid" => $iid, "service_status" => "2", ));
            } else {
                LocalQuery("service.postsuspend", array("elid" => $iid, "sok" => "ok", ));
                LocalQuery("service.setstatus", array("elid" => $iid, "service_status" => "8", ));
            }
            LocalQuery("service.setexpiredate", array("elid" => $iid, "expiredate" => $param["expiredate"], ));
	} else {
	    setToLog('Error sync_item domain on Pananames $result->errors[0]->description = ' . $result->errors[0]->description);
        }
        return;
    }
}
