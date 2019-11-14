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
				/** @noinspection PhpUndefinedConstantInspection defined() is used */
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
			register_setting('Isimo', 'isimo_token');
			$plugin_basename = plugin_basename(__DIR__ . '/isimo_wp.php');
			add_filter("plugin_action_links_{$plugin_basename}", [$this, 'add_settings_link']);
		}

		public function option_menu()
		{
			$slug = add_options_page(
				'Isimo Config',
				'Isimo',
				'manage_options',
				'isimo-config',
				[$this, 'option_page']
			);
			add_action('admin_print_scripts-' . $slug, [$this, 'admin_css']);
			return $slug;
		}

		public static function generate_tooken()
		{
			return str_replace(
				array('+', '/'),
				array('-', '_'),
				base64_encode(
					self::generate_tooken_data()
				)
			) ?: 'MsX3oFUobc8iWyDQ6JfPjk36MCIkNYLQ';
		}

		public static function generate_tooken_data()
		{
			if(function_exists('random_bytes'))
			{
				try
				{
					return random_bytes(24);
				}
				catch(\Exception $e)
				{
				}
			}
			if(function_exists('openssl_random_pseudo_bytes'))
			{
				/** @noinspection CryptographicallySecureRandomnessInspection */
				/** @noinspection PhpComposerExtensionStubsInspection */
				return openssl_random_pseudo_bytes(24);
			}
			if(function_exists('mcrypt_create_iv'))
			{
				/** @noinspection CryptographicallySecureRandomnessInspection */
				/** @noinspection PhpComposerExtensionStubsInspection */
				/** @noinspection PhpDeprecationInspection */
				return mcrypt_create_iv(24);
			}

			if(function_exists('mt_rand'))
			{
				return implode(
					'',
					array_map(
						function () {
							/** @noinspection RandomApiMigrationInspection */
							return hex2bin(substr('00000' . dechex(mt_rand(0, 0xFFFFFF)), -6));
						},
						range(1, 6)
					)
				);
			}

			if(function_exists('rand'))
			{
				return implode(
					'',
					array_map(
						function () {
							/** @noinspection RandomApiMigrationInspection */
							return hex2bin(substr('00000' . dechex(rand(0, 0xFFFFFF)), -6));
						},
						range(1, 6)
					)
				);
			}

			return NULL;
		}

		public static function admin_css()
		{
			echo <<<HTML_BLOCK
            <style>
               #isimo_options label {display: block;}
               #isimo_options label span:first-child {display: inline-block; width: 140px;}
               #isimo_options label input[type=text] {width: 400px;}
            </style>
HTML_BLOCK;
		}

		public function option_page()
		{
			if(isset($_POST['generate']))
			{
				update_option('isimo_token', Isimo::generate_tooken());
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
            <h1>Isimo</h1>
            <form method="post" action="#">

HTML_BLOCK;

			echo '<label><span>Isimo Access-token:</span> ';
			if($this->locked)
			{
				echo '<input type="text" readonly value="' . esc_attr($this->token, ENT_QUOTES) . '" />';
			}
			else
			{
				echo '<input type="text" name="isimo_token" value="' . esc_attr($this->token, ENT_QUOTES) . '" />';
			}
			echo '</label>';
			$fetch_value = $this->last_fetch_time ? date('Y-m-d H:i:s e', $this->last_fetch_time) : 'Never';
			echo '<label><span>Last fetched:</span> <input type="text" readonly value="' . $fetch_value . '" /></label>';

			echo get_submit_button();

			if(!$this->token)
			{
				echo get_submit_button('Generate Token', ['primary'], 'generate', false);
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
			$url_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			$token_test = strstr($url_path, $needle);
			if(!$token_test)
			{
				return FALSE;
			}

			$token = trim(substr($token_test, strlen($needle)), '/');

			if(!$this->token)
			{
				return FALSE;
			}

			if($token !== $this->token && urldecode($token) !== $this->token)
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
			}

			$data->gitsha = NULL;

			$paths = [
				$_SERVER['DOCUMENT_ROOT'],
				ABSPATH . '/..',
				ABSPATH,
			];

			foreach($paths as $path)
			{
			  $git_dir = $path.'/.git';
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

				$cmd = 'cd ' . escapeshellarg($path) . ' ; git status --porcelain -uall';
				$data->gitstatus = shell_exec($cmd);
				if(!$data->gitstatus)
				{
					unset($data->gitstatus);
				}
				break;
			}

			foreach($paths as $path)
			{
				if(is_file($path.'/composer.lock'))
				{
					$data->composer_lock = file_get_contents($path.'/composer.lock');
					if(is_file($path.'/composer.json'))
					{
					  $data->composer_json = file_get_contents($path.'/composer.json');
					}
					break;
				}
			}

			header('Content-Type: application/json');
			echo json_encode($data, JSON_PRETTY_PRINT);
			die();
		}

		/**
		 * Add settings link on the plugins-table
		 * @hook plugin_action_links_<plugin_file>
		 * @param string[] $links
		 * @return string[] modifed $links
		 */
		public function add_settings_link($links)
		{
			$url = htmlentities(get_admin_url(null, 'options-general.php?page=isimo-config'), ENT_COMPAT);
			$title = htmlentities(__('Settings'), ENT_NOQUOTES);
			array_unshift($links, "<a href=\"{$url}\">{$title}</a>");
			return $links;
		}
	}
