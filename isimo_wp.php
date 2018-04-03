<?php
	/**
	 * Isimo Client
	 *
	 * @package Isimo_Client
	 * @author Puggan Sundragon <puggan@spiro.se>
	 * @version 0.1.0
	 *
	 * @wordpress-plugin
	 * Plugin Name: Isimo Client
	 * Plugin URI: https://github.com/SpiroAB/isimo_wp
	 * Description: Providing data to the Isimo Server
	 * Version: 0.1.0
	 * Author: Puggan Sundragon <puggan@spiro.se>
	 * Author URI: https://spiro.se/
	 */

	require_once __DIR__ . '/Isimo.php';

	// Add listner.
	$isimo = new \SpiroAB\Isimo();
