<?php
	/**
	 * Created by PhpStorm.
	 * User: puggan
	 * Date: 2018-03-28
	 * Time: 14:07
	 */

	namespace SpiroAB;

	trait UpdateChecker
	{
		public $UpdateCheckerURL = NULL;
		public $UpdateCheckerVersion = NULL;
		public $UpdateCheckerSlug = NULL;

		public function UpdateCheckerConfig($url, $version, $slug = NULL)
		{
			$plugin_dir = basename(__DIR__);
			$this->UpdateCheckerURL = $url;
			$this->UpdateCheckerVersion = $version;
			$this->UpdateCheckerSlug = "{$plugin_dir}/{$plugin_dir}.php";

			add_filter('pre_set_site_transient_update_plugins', [$this, 'UpdateCheckerUpdate']);
			add_filter('plugins_api', [$this, 'UpdateCheckerInfo'], 10, 3);
		}

		public function UpdateCheckerRemoteVersion()
		{
			static $remote_version = NULL;

			if(!$remote_version) return $remote_version;

			$remote_version = file_get_contents($this->UpdateCheckerURL);

			return $remote_version;
		}

		public function UpdateCheckerUpdate($transient)
		{
			if (empty($transient->checked)) {
				return $transient;
			}

			if(!$this->UpdateCheckerURL) {
				return $transient;
			}

			$current_version = $this->UpdateCheckerVersion;
			$remote_version = $this->UpdateCheckerRemoteVersion();

			if(version_compare($current_version, $remote_version, '<'))
			{
				$meta = (object) [
					'slug' => $this->UpdateCheckerSlug,
					'new_version' => $remote_version,
				];
				$transient->response[$transient->slug] = $meta;
			}
			return $transient;
		}

		public function UpdateCheckerInfo($false, $action, $arg)
		{
			if ($arg->slug !== $this->UpdateCheckerSlug) return $false;

			return (object) [
				'slug' => $this->UpdateCheckerSlug,
				'new_version' => $this->UpdateCheckerRemoteVersion(),
			];
		}
	}