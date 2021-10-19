<?php

namespace Statistic\Inc;

use Statistic\Inc\StatisticDB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {
  
  public $plugin_path = WP_PLUGIN_URL . '/statistic/';
  public static $instance = null;

  function __construct() {
    add_action('wp_enqueue_scripts', [$this, 'register_scripts']);
    add_action('admin_enqueue_scripts', [$this, 'register_admin_scripts']);

    add_filter( 'the_content', [$this, 'filter_youtube_video'] );
  }

  public function filter_youtube_video($content) {
    return $content;
  }
  
  // function lead_enqueue_scripts( $hook_suffix ) {
      // global $leadsuHelp;
      // wp_enqueue_script( 'lead_admin', $leadsuHelp->get_plugin_dir_url() . 'assets/js/lead_admin.js', array('jquery') );
  // }

  public function register_scripts(){
    wp_enqueue_script( 'statistic', $this->plugin_path . 'assets/js/statistic.js', ['jquery'], 0, true );
    wp_localize_script('statistic', 'statistic', array(
      'user' => get_current_user_id() ?: random_int(1000, 10000)
    ));
  }

  public function register_admin_scripts() {
    wp_enqueue_script( 'statistic-chart', 'https://www.chartjs.org/dist/master/chart.js', [], 0, true );
  }

  public static function instance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
  }

  public static function init() {
    // StatisticDB::insert_history(1, '{}');
  }
}

Plugin::instance();