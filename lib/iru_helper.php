<?php

namespace munkireport\module\iru;

use Iru_model;

class Iru_helper
{
    /**
     * Pull device data from the Iru (Kandji) API.
     *
     * @param string $serial_number
     **/
    public function pull_iru_data($serial_number)
    {
        // Error message
        $error = '';

        // Trim off any slashes on the right
        $iru_api_endpoint = rtrim(conf('iru_api_endpoint'), '/');

        // Get computer data from Iru
        $url = "{$iru_api_endpoint}/api/v1/devices/?serial_number={$serial_number}";
        $iru_computer_result = $this->send_iru_query($url);

        if(! $iru_computer_result){
            print_r("No data received from Iru");
            exit();
        }

        // Process computer data
        $json = json_decode($iru_computer_result);

        if( ! $json){
            $error = 'Machine not found in Iru!';
            return $error;
        }

        return $json;
    }

    /**
     * Retrieve url
     *
     * @return JSON object if successful, FALSE if failed
     * @author n8felton, tweaked for Jamf by Tuxudo, then tweaked for Kandji by jc0b, refactored for Iru by delsassergh
     **/
    public function send_iru_query($url)
    {

        $iru_api_key = conf('iru_api_key');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Accept: application/json', 'Authorization: Bearer '.$iru_api_key));

        $return = curl_exec($ch);

        // Check for timeout
        if (curl_errno($ch) && curl_errno($ch) == 28) {
            error_log("MunkiReport:- Iru server timed out for - ".$url, 0);
            return false;
        } else if (curl_errno($ch)) {
            error_log("MunkiReport:- There was an error getting data from the Iru server: ".curl_errno($ch)." - ".$url, 0);
            return false;
        }

        return $return;
    }
}
