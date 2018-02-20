<?php
	/**
	 * @package Isimo Client
	 * @author Puggan
	 * @version 0.0.5
	 */
	/*
	Plugin Name: Isimo Client
	Description: Providing data to the Isimo Server
	Version: 0.0.5
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

        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/update.php';

		$data = (object) [];

		$data->report = [
		    'core' => get_core_updates(),
		    'plugins' => get_plugin_updates(),
        ];
		$data->software = 'Wordpress';
		$data->version = $wp_version;
		$data->client = 'isimo wp 0.0.6';
		$data->plugins = get_plugins();

		ob_start();
		phpinfo();
		$data->phpinfo = ob_get_clean();

		$data->mysql = [];
		foreach($wpdb->get_results('SHOW VARIABLES') as $row) {
			$data->mysql[$row->Variable_name] = $row->Value;
		};

		$data->gitsha = null;
		$git_dirs = ["{$_SERVER['DOCUMENT_ROOT']}/.git", ABSPATH . '/../.git', ABSPATH . '/.git'];
		foreach($git_dirs as $git_dir)
		{
			if(!is_dir($git_dir)) continue;
			if(!is_file($git_dir . '/HEAD')) continue;
			$git_head = file_get_contents($git_dir . '/HEAD');
			if(!$git_head) continue;
			$git_head = trim(substr($git_head, 4));
			if(!$git_head) continue;
			if(!is_file($git_dir . '/' . $git_head)) continue;
			$git_ref = file_get_contents($git_dir . '/' . $git_head);
			if(!$git_ref) continue;
			$git_ref = trim($git_ref);
			if(!$git_ref) continue;
			$data->gitsha = $git_ref;
			break;
		}

		header('Content-Type: application/json');
		echo json_encode($data, JSON_PRETTY_PRINT);
		die();
	}
