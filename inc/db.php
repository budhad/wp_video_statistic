<?php

namespace Statistic\Inc;

if ( ! defined("ABSPATH") ) {
  return;
}

class StatisticDB {
	public static $instance = null;
	private static $prefix = '';
	private static $table_name = 'statistic_history';

	public static function instance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function __construct() {
		global $wpdb;
		self::$prefix = $wpdb->prefix;
	}

	public static function create_default_table() {
		global $wpdb;
		$table_name = self::$prefix . self::$table_name;
	
		$sql = "CREATE TABLE " . $table_name . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id bigint(11) DEFAULT '0' NOT NULL,
			stat_action tinytext NOT NULL,
			url_page tinytext NOT NULL,
			url_file tinytext NOT NULL,
			value longtext NOT NULL,
			date int(11) NOT NULL,
			UNIQUE KEY id (id)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	public static function insert_history($stat) {
		if (!$stat) return;

		global $wpdb;
		$table_name = self::$prefix . self::$table_name;

		$request = $wpdb->insert( $table_name, 
		array( 
			'user_id' => $stat['user'], 
			'stat_action' => $stat['stat_action'], 
			'value' => $stat['value'],
			'url_page' => $stat['url_page'],
			'url_file' => $stat['url_file'],
			'date' => $stat['date']
		));

		return $request;
	}

	public static function get_history( $dateStart = null, $dateEnd = null) {
		global $wpdb;
		$table_name = self::$prefix . self::$table_name;

		$dateS = strtotime( "-1 month", time() );

		if ( $dateStart ) {
			$dateS = strtotime( $dateStart );
		} 

		// var_dump("SELECT id, user_id, stat_action, url_page, url_file, value, date FROM " . $table_name . " WHERE date >= '" . $dateS . "'");

		$request = $wpdb->get_results( "SELECT id, user_id, stat_action, url_page, url_file, value, date FROM " . $table_name . " WHERE date >= '" . $dateS . "' ORDER BY url_page"); 

		$request = wp_unslash( $request );

		foreach ( $request as $row ) {
			$row->date = date('Y-m-d', $row->date);
			$row->value = json_decode( $row->value );
		}

		return $request;
	}
}

StatisticDB::instance();