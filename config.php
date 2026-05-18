<?php

	/*
	|===============================================
	| Iru
	|===============================================
	|
	| A working Iru (formerly Kandji) instance is required for use of this module.
	|
	| To use the Iru module, set 'iru_enable' to TRUE and
	| enter the instance URL and API key for accessing your
	| Iru instance.
	|
	| This module pulls data about Macs that are in Iru.
	|
	*/

return [
  'iru_enable' => env('IRU_ENABLE', false),
  'iru_api_key' => env('IRU_API_KEY', ""),
  'iru_api_endpoint' => env('IRU_API_ENDPOINT', ""),
  'iru_tenant_address' => env('IRU_TENANT_ADDRESS', ""),
];
