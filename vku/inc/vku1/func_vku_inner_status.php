<?php

/**
 * VKU-OLD
 * 
 * @todo DELETE
 */
function vku_item_status_change($vku_id, $item_id){
global $user;

	$vku = new VKUCreator($vku_id);

	$message = 'Unberechtigter Zugriff';

	if($vku -> is()){

		$vkuauthor = $vku -> getAuthor();

		if($user -> uid == $vkuauthor){
			$result = $vku -> toggleItemStatus($item_id);
			
			if($result){
				$vku -> update();
				$message = 'Der Status des Eintrages wurde geändert';	
				$array = array('error' => 0, 'message' => $message, "id" => $item_id, "status" => $result);
				$array["goto"] = 'vku/' . $vku_id;
                                
                                $vku ->logEvent('status', $message . ' ('. $item_id .'/'. $result .')');
                                
				lk_check_ajaxrequest($array);
			}
			else {
                                $message = $vku ->logEvent('error', 'Unberechtigter Zugriff (Result: '. $result .')');
                   	}
		}
		else {
			$message = 'Unberechtigter Zugriff (VKU not accessible)';
                        $vku ->logEvent('error', $message);
            	}

	}
	else {

		$message = 'Unberechtigter Zugriff (VKU is old)';
                $vku ->logEvent('error', $message);
	}

	
	$array = array('error' => 1, 'message' => $message);
	$array["goto"] = '<front>';
	lk_check_ajaxrequest($array);
}

?>