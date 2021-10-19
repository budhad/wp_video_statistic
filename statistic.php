<?php
 /* Plugin Name: Statistic video
 * Description: Статистика видео на сайте
 * Author:      NickyTikiTa
 * Version:     0.0.1
 *
 * Text Domain: statistic
 *
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * */

// namespace Statistic;

use Statistic\Inc\StatisticDB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

register_activation_hook( __FILE__, 'my_plugin_activate' );
function my_plugin_activate() {
	StatisticDB::create_default_table();
}

class StatisticHelp {
	public static $instance = null;
	private static $plugin_basename ;
	private static $plugin_dir_path;
	private static $plugin_dir_url;

	public static function instance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function __construct() {
		self::$plugin_basename = plugin_basename(__FILE__);
		self::$plugin_dir_path = plugin_dir_path(__FILE__);
		self::$plugin_dir_url = plugin_dir_url(__FILE__);
	}

	public static function get_plugin_basename() {
		return self::$plugin_basename;
	}
	public static function get_plugin_dir_path() {
		return self::$plugin_dir_path;
	}
	public static function get_plugin_dir_url() {
		return self::$plugin_dir_url;
	}
}
StatisticHelp::instance();


require_once StatisticHelp::get_plugin_dir_path() . '/inc/db.php';
require_once StatisticHelp::get_plugin_dir_path() . '/inc/controller.php';
