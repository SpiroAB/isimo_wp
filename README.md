# README #
Wordpress plugin for serving data to the Isimo server.
Information served:
 * Wordpress version.
 * Active plugin versions.
 * Available wordpress updates, core and plugins.
 * client version (this plugin)
 * PHP-version and more from phpinfo()
 * MySQL info 'SHOW VARIABLES'
 * Current git commit (sha)
 * Composer.lock file

## Install ##
* Install into `<wp>/wp-content/plugins/isimo_wp/`
* Activate plugin
* Go to Settings -> Isimo, and press Generate
* Save the generated token at the Isimo-server

## Version History ##
Available working versions:
* v1.1.4 Support for composer.lcok
* v1.1.3 Generate tooken, now can use multiple functions for generating the random data. 
* v1.1.1 Generated tookens use url-safe base64 encoding. Ignores Query-parameters.

Older versions, no longer compatile witht the Isimo-server. (Commit 367cb3c, 2018-04-25, Add timestamp when fetching report to avoid cache)
* v1.1.0 Only list active plugins.
* v1.0.0 Can now generate the tooken from admin.
* v0.1.0 Moved version-tracking to SpiroAB/wp_version_tracker.
* v0.0.9 Added version tracking, so wp know when this addon have updates.
* v0.0.8 Rename/Rebrand
* v0.0.7 Made OO, moved all functions to a class.
* v0.0.6 Report available updates.
* v0.0.5 Report installed plugins.
* v0.0.4 Add git-sha to report.
* v0.0.3 Rename/Rebrand to match the isimo-server renameing.
* v0.0.2 Add client version to report, move mysqlinfo in report.
* v0.0.1 Report wordpress-version, php-info and mysql-info to Patchtracker.
