<?php

/**
 * Iru module class
 *
 * @package munkireport
 * @author jc0b (original Kandji module), delsassergh (Iru fork)
 **/
use munkireport\models\MRModel as Eloquent;

use Illuminate\Support\Facades\DB;

class iru_controller extends Module_controller
{
    public function __construct()
    {
        // No authentication, the client needs to get here
        // Store module path
        $this->module_path = dirname(__FILE__);

        // Add local config
        configAppendFile(__DIR__ . '/config.php');
    }

    public function index()
    {
        echo "You've loaded the Iru module!";
    }

    // Add the admin page
    public function admin()
    {
        $obj = new View();
        $obj->view('iru_admin', [], $this->module_path.'/views/');
    }


    /**
     * Get Iru agent version for widget
     *
     * @return void
     **/
    public function get_iru_version()
    {
        $iru_version_data = Iru_model::selectRaw("COALESCE(SUM(CASE WHEN iru_agent_version IS NOT NULL THEN 1 END), 0) AS count, iru_agent_version")->filter()->groupBy('iru_agent_version')->orderBy('count', 'desc')->get()->toArray();
        $obj = new View();
        $obj->view('json', array('msg' => $iru_version_data));
    }


    /**
     * REST API for retrieving last checkin data for widget
     **/
     public function get_last_checkin()
     {
        $currentdate = date_timestamp_get(date_create());
        $week = $currentdate - 604800;
        $month = $currentdate - 2592000;

        $checkin_data = Iru_model::selectRaw("COALESCE(SUM(CASE WHEN last_check_in <= $month THEN 1 END), 0) AS red,
            COALESCE(SUM(CASE WHEN last_check_in <= $week AND last_check_in > $month THEN 1 END), 0) AS yellow,
            COALESCE(SUM(CASE WHEN last_check_in > $week AND last_check_in > 0 THEN 1 END), 0) AS green")
        ->filter()
        ->first()
        ->toLabelCount();

        $obj = new View();
        $obj->view('json', array('msg' => $checkin_data));
     }

    /**
    * REST API for retrieving stats on passport enablement
    **/
    public function get_passport_stats()
    {
        $passport_data = Iru_model::selectRaw("COALESCE(SUM(case when passport_enabled = 'True' THEN 1 END), 0) AS enabled,
            COALESCE(SUM(case when passport_enabled <> 'True' THEN 1 END), 0) AS disabled")
        ->filter()
        ->first()
        ->toLabelCount();

        $obj = new View();
        $obj->view('json', array('msg' => $passport_data));
    }

    /**
     * Pull in Iru data for all serial numbers
     *
     * @return void
     **/
    public function pull_all_iru_data($incoming_serial = '')
    {
        if ( $incoming_serial == ''){
            // APP_ROOT is set in index.php
            require_once(APP_ROOT.'vendor/munkireport/machine/machine_model.php');

            $machinedata = Machine_model::selectRaw("machine.serial_number")->filter()->get()->toArray();
            $out = array();
            foreach ($machinedata as $serialobj) {
                $out[] = $serialobj['serial_number'];
            }
            $obj = new View();
            $obj->view('json', array('msg' => $out));
        } else {

            $iru = new Iru_model();
            $iru->serial_number = $incoming_serial;
            $iru->device_id = 0;
            $iru_status = $this->run_iru_stats($iru->serial_number);

            // Check if machine exists in Iru
            if ($iru->device_id == 0 ){
                $out = array("serial"=>$incoming_serial,"status"=>"Machine not found in Iru!");
            } else {
                $out = array("serial"=>$incoming_serial,"status"=>"Machine processed");
            }
            $obj = new View();
            $obj->view('json', array('msg' => $out));
        }
    }

    /**
    * Get Iru data
    *
    * @return void
    **/
    function run_iru_stats(&$iru_model)
    {
        $module_dir = dirname(__FILE__);
        // Check if we should enable Iru lookup
        if (conf('iru_enable')) {
            // Load Iru helper
            require_once($module_dir.'/lib/iru_helper.php');
            $iru_helper = new munkireport\module\iru\Iru_helper;
            $iru_helper->pull_iru_data($iru_model);
        }

        return $this;
    }

    /**
     * Force data pull from Iru
     *
     * @return void
     **/
    public function recheck_iru($serial = '')
    {
        if (authorized_for_serial($serial)) {
            $iru = new Iru_model();
            $iru->serial_number = $serial;
            $this->run_iru_stats($iru);
        }

        redirect("clients/detail/$serial#tab_iru-tab");
    }

    /**
     * Get Iru information for serial_number
     *
     * @param string $serial serial number
     **/
    public function get_data($serial_number = '')
    {
        $machinedata = Iru_model::select("iru.*")->where("iru.serial_number", $serial_number)->filter()->get();
        $obj = new View();
        $obj->view('json', array('msg' => $machinedata[0]));
    }
} // End class iru_module
