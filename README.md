# README #
Wordpress plugin for serving data to the Patchtracker server.

## Install ##
* Install into `<wp>/wp-content/plugins/spiro_patchtracker/`
* Genereate a access token, `base64_encode(openssl_random_pseudo_bytes(24))`
* add `define('SPIRO_PATCHTRACKER_TOKEN', '<token>');` in wp-config.php
* Activate plugin
