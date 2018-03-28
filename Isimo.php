<?php

	namespace SpiroAB;

	/**
	 * Class Isimo
	 *
	 * @package Isimo_Client
	 */
	class Isimo
	{
		use UpdateChecker;

		public function __construct()
		{
			add_action('init', [$this, 'init']);
			$this->UpdateCheckerConfig("https://raw.githubusercontent.com/SpiroAB/isimo_wp/master/VERSION", file_get_contents(__DIR__ . '/VERSION'));
		}

		public function init()
		{
			// Add url-test
			add_action('parse_request', [$this, 'url_test']);
		}


		/**
		 * On parse_requestm check if an isimo-request
		 * print isimo, or return false
		 *
		 * @return false
		 */
		public function url_test()
		{
			$needle = '/isimo/status/';
			$token_test = strstr($_SERVER['REQUEST_URI'], $needle);
			if(!$token_test)
			{
				return FALSE;
			}

			$token = substr($token_test, strlen($needle));

			if(!defined('ISIMO_TOKEN'))
			{
				return FALSE;
			}

			if(!ISIMO_TOKEN)
			{
				return FALSE;
			}

			if($token !== ISIMO_TOKEN)
			{
				return FALSE;
			}

			self::isimo_print();
			die();
		}


		/**
		 * Print the isimo json
		 *
		 * @return void
		 */
		public static function isimo_print()
		{
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
			include_once ABSPATH . 'wp-admin/includes/update.php';

			$data = (object) [];

			$data->report = [
				'core' => get_core_updates(),
				'plugins' => get_plugin_updates(),
			];
			$data->software = 'Wordpress';
			$data->version = $GLOBALS['wp_version'];
			$data->client = 'isimo wp 0.0.6';
			$data->plugins = get_plugins();

			ob_start();
			phpinfo();
			$data->phpinfo = ob_get_clean();

			$data->mysql = [];
			foreach($GLOBALS['wpdb']->get_results('SHOW VARIABLES') as $row)
			{
				$data->mysql[$row->Variable_name] = $row->Value;
			};

			$data->gitsha = NULL;
			$git_dirs = [
				$_SERVER['DOCUMENT_ROOT'] . '/.git',
				ABSPATH . '/../.git',
				ABSPATH . '/.git',
			];
			foreach($git_dirs as $git_dir)
			{
				if(!is_dir($git_dir))
				{
					continue;
				}

				if(!is_file($git_dir . '/HEAD'))
				{
					continue;
				}

				$git_head = file_get_contents($git_dir . '/HEAD');
				if(!$git_head)
				{
					continue;
				}

				$git_head = trim(substr($git_head, 4));
				if(!$git_head)
				{
					continue;
				}

				if(!is_file($git_dir . '/' . $git_head))
				{
					continue;
				}

				$git_ref = file_get_contents($git_dir . '/' . $git_head);
				if(!$git_ref)
				{
					continue;
				}

				$git_ref = trim($git_ref);
				if(!$git_ref)
				{
					continue;
				}

				$data->gitsha = $git_ref;
				break;
			}

			header('Content-Type: application/json');
			echo json_encode($data, JSON_PRETTY_PRINT);
			die();
		}
	}
