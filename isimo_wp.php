<?php
	/**
	 * @package Isimo Client
	 * @author Puggan
	 * @version 0.0.3
	 */
	/*
	Plugin Name: Isimo Client
	Description: Providing data to the Isimo Server
	Version: 0.0.3
	Author: Puggan
	*/

	add_action('parse_request', 'isimo_url_test');

	function isimo_url_test()
	{
		$needle = '/isimo/status/';
		$token_test = strstr($_SERVER["REQUEST_URI"], $needle);
		if(!$token_test) return;
		$token = substr($token_test, strlen($needle));

		if(!defined('ISIMO_TOKEN')) return false;

		if(!ISIMO_TOKEN) return false;

		if($token !== ISIMO_TOKEN) return false;

		isimo();
		die();
	}

	function isimo()
	{
		global $wpdb;
		global $wp_version;

		$data = (object) [];

		$data->report = [];
		$data->software = 'Wordpress';
		$data->version = $wp_version;
		$data->client = 'isimo wp 0.0.3';

		ob_start();
		phpinfo();
		$data->phpinfo = ob_get_clean();

		$data->mysql = [];
		foreach($wpdb->get_results('SHOW VARIABLES') as $row) {
			$data->mysql[$row->Variable_name] = $row->Value;
		};

		header('Content-Type: application/json');
		echo json_encode($data, JSON_PRETTY_PRINT);
		die();
	}
