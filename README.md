# README #
Wordpress plugin for serving data to the Isimo server.

## Install ##
* Install into `<wp>/wp-content/plugins/isimo_wp/`
* Genereate a access token, `echo '<?php echo base64_encode(openssl_random_pseudo_bytes(24)) . PHP_EOL;' | php`
* add `define('ISIMO_TOKEN', '<token>');` in wp-config.php
* Activate plugin
