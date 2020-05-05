<?php
/**
 * Plugin Name: WP Google DFP
 * Plugin URI: https://github.com/jucostag/wp-google-dfp
 * Description: Manage and show Google DFP ads in wordpress.
 * Version: 1.0.0
 * Author: Juliana Gonçalves
 * Author URI: https://github.com/jucostag
 * License: GPL
 */

require __DIR__ . "/vendor/autoload.php";

use AdsGoogleDFP\Plugin;

define('GDFPURL', WP_PLUGIN_URL."/".dirname(plugin_basename(__FILE__)));
define('GDFPPATH', WP_PLUGIN_DIR."/".dirname(plugin_basename(__FILE__)));
define('GDFPINDEX', __FILE__);

$gdfp = new Plugin();
$gdfp->init();
