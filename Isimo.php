<?php

	namespace SpiroAB;

	/**
	 * Class Isimo
	 *
	 * @package Isimo_Client
	 *
	 * @property bool $locked
	 * @property string $token
	 * @property int $last_fetch_time
	 */
	class Isimo
	{
		public $locked;
		public $token;
		public $last_fetch_time;


		public function __construct()
		{
			add_action('init', [$this, 'init']);
			if(is_admin())
			{
				add_action('admin_init', [$this, 'admin_init']);
				add_action('admin_menu', [$this, 'option_menu']);
			}
		}

		public function init()
		{
			// Add url-test
			add_action('parse_request', [$this, 'url_test']);

			$this->locked = defined('ISIMO_TOKEN');
			if($this->locked)
			{
				$this->token = ISIMO_TOKEN;
			}
			else
			{
				$this->token = get_option('isimo_token');
			}
			$this->last_fetch_time = get_option('isimo_last_fetch');
		}

		public function admin_init()
		{
			register_setting( 'Isimo', 'isimo_token' );
		}

		public function option_menu()
		{
			add_options_page(
				'Isimo Config',
				'Isimo',
				'manage_options',
				'isimo-config',
				[$this, 'option_page']
			);
		}

		public function generate_tooken()
		{
			/** @noinspection CryptographicallySecureRandomnessInspection */
			return str_replace(array('+', '/'), array('-', '_'), base64_encode(openssl_random_pseudo_bytes(24)));
		}

		public function option_page()
		{
			if(isset($_POST['generate']))
			{
				update_option('isimo_token', $this->generate_tooken());
				if(!$this->locked)
				{
					$this->token = get_option('isimo_token');
				}
			}
			else if(isset($_POST['isimo_token']))
			{
				update_option('isimo_token', (string) $_POST['isimo_token']);
				if(!$this->locked)
				{
					$this->token = get_option('isimo_token');
				}
			}

			echo <<<HTML_BLOCK
       <div class="wrap" id="isimo_options">
            <style>
               #isimo_options label {display: block;}
               #isimo_options label span:first-child {display: inline-block; width: 140px;}
               #isimo_options label input[type=text] {width: 400px;}
            </style>
            <h1>Isimo</h1>
            <form method="post" action="#">

HTML_BLOCK;

			echo '<label><span>Isimo Access-token:</span> ';
			if($this->locked)
			{
				echo '<input type="text" readonly value="' . htmlentities($this->token) . '" />';
			}
			else
			{
				echo '<input type="text" name="isimo_token" value="' . htmlentities($this->token) . '" />';

			}
			echo "</label>";
			$fetch_value = $this->last_fetch_time ? date("Y-m-d H:i:s e", $this->last_fetch_time) : 'Never';
			echo '<label><span>Last fetched:</span> <input type="text" readonly value="' . $fetch_value . '" /></label>';

			submit_button();

			if(!$this->token)
			{
				echo '<input type="submit" name="generate" value="Generate Token" class="button button-primary" />';
			}

			echo <<<HTML_BLOCK
            </form>
        </div>
HTML_BLOCK;

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

			if(!$this->token)
			{
				return FALSE;
			}

			if($token !== $this->token)
			{
				return FALSE;
			}

			update_option('isimo_last_fetch', time());
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

			$isimo_version = json_decode(file_get_contents(__DIR__ . '/version.json'));

			$active_plugins = get_option('active_plugins');
			$active_plugins = array_combine($active_plugins, $active_plugins);

			$data = (object) [];

			$data->report = [
				'core' => get_core_updates(),
				'plugins' => get_plugin_updates(),
			];
			$data->software = 'Wordpress';
			$data->version = $GLOBALS['wp_version'];
			$data->client = 'isimo wp ' . $isimo_version->version;
			$data->plugins = array_intersect_key(get_plugins(), $active_plugins);

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
