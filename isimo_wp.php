<?php
	/**
	 * @package Isimo_Client
	 * @author Puggan <puggan@spiro.se>
	 * @version 0.0.7
	 */

	/*
	Plugin Name: Isimo Client
	Description: Providing data to the Isimo Server
	Version: 0.0.7
	Author: Puggan <puggan@spiro.se>
	*/

	require_once __DIR__ . 'isimo.php';

	// Add listner.
	add_action('parse_request', '\Puggan\Isimo::url_test');
