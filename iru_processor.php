<?php

/**
 * Iru processor class
 *
 * @package munkireport
 * @author jc0b (original Kandji module), delsassergh (Iru fork)
 **/

use CFPropertyList\CFPropertyList;
use munkireport\processors\Processor;

class Iru_processor extends Processor
{
    public function run($plist)
    {
        if ( ! $plist){
            throw new Exception("Error Processing Request: No property list found", 1);
        }
        configAppendFile(__DIR__ . '/config.php');
        $module_dir = dirname(__FILE__);

        $parser = new CFPropertyList();
        $parser->parse($plist, CFPropertyList::FORMAT_XML);
        $mylist = $parser->toArray();

        $mylist['serial_number'] = $this->serial_number;
        // Retrieve Iru MR record (if existing)
        try {
            $model = Iru_model::select()
                ->where('serial_number', $this->serial_number)
                ->firstOrFail();
        } catch (\Throwable $th) {
            $model = new Iru_model();
        }

        // Check if we should enable Iru lookup
        if (conf('iru_enable')) {
            // Load Iru helper
            require_once($module_dir.'/lib/iru_helper.php');
            $iru_helper = new munkireport\module\iru\Iru_helper;
            $json = $iru_helper->pull_iru_data($this->serial_number);

            // Transpose Iru API output into Iru model
            // General section
            $mylist['name'] = $json[0]->device_name;
            $mylist['asset_tag'] = $json[0]->asset_tag;
            $mylist['blueprint_id'] = $json[0]->blueprint_id;
            $mylist['blueprint_name'] = $json[0]->blueprint_name;
            $mylist['last_check_in'] = $this->convert_time_to_epoch($json[0]->last_check_in);
            $mylist['last_enrollment'] = $this->convert_time_to_epoch($json[0]->last_enrollment);
            $mylist['first_enrollment'] = $this->convert_time_to_epoch($json[0]->first_enrollment);

            // Location section
            $mylist['realname'] = $json[0]->user->name;
            $mylist['email_address'] = $json[0]->user->email;

        }

        $model->fill($mylist)->save();
    }

    /**
     * Convert Iru timestamps to epochs
     *
     * @return Unix epoch
     **/
    private function convert_time_to_epoch($date)
    {
        $dt = new \DateTime($date);
        return $dt->getTimestamp();
    }
}
