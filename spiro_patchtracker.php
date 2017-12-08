<?php
	/**
	 * @package Spiro Patchtracker Client
	 * @author Puggan
	 * @version 0.0.1
	 */
	/*
	Plugin Name: Spiro Patchtracker Client
	Description: Providing data to the Spiro Patchtracker Server
	Version: 0.0.1
	Author: Puggan
	*/

	add_action('parse_request', 'spiro_patchtracker_url_test');

	function spiro_patchtracker_url_test()
	{
		$needle = '/patchtracker/status/';
		$token_test = strstr($_SERVER["REQUEST_URI"], $needle);
		if(!$token_test) return;
		$token = substr($token_test, strlen($needle));

		if(!defined('SPIRO_PATCHTRACKER_TOKEN')) return false;

		if(!SPIRO_PATCHTRACKER_TOKEN) return false;

		if($token !== SPIRO_PATCHTRACKER_TOKEN) return false;

		spiro_patchtracker();
		die();
	}

	function spiro_patchtracker()
	{
		global $wpdb;
		global $wp_version;

		$data = (object) [];

		$data->report = [];
		$data->software = 'Wordpress';
		$data->version = $wp_version;

		ob_start();
		phpinfo();
		$data->phpinfo = ob_get_clean();

		$data->mysql = [];
		foreach($wpdb->get_results('SHOW VARIABLES') as $row) {
			$data->mysqlinfo[$row->Variable_name] = $row->Value;
		};

		header('Content-Type: application/json');
		echo json_encode($data, JSON_PRETTY_PRINT);
		die();
	}
