<?php

// namespace Statistic\Inc\Ajax;

use Statistic\Inc\StatisticDB;

if (! defined ('ABSPATH')) {
  return;
}

function statistic_media() {
  $stat = [
    'user' => wp_slash( isset($_POST['user']) ? sanitize_text_field( $_POST['user'] ) : '0' ),
    'stat_action' => wp_slash( isset($_POST['stat_action']) ? sanitize_text_field( $_POST['stat_action'] ) : 'prosto potomu' ),
    'value' => isset($_POST['value']) ? sanitize_text_field( $_POST['value'] ) : '{"Dino":"Tarantino"}',
    'url_page' => wp_slash( isset($_POST['url_page']) ? sanitize_text_field( $_POST['url_page'] ) : '/null'),
    'url_file' => wp_slash( isset($_POST['url_file']) ? sanitize_text_field( $_POST['url_file'] ) : '/null'),
    'date' => isset($_POST['date']) ? sanitize_text_field( $_POST['date'] ) : ''
  ];

  $stat['date'] = strtotime($stat['date']);

  $stat['request'] = StatisticDB::insert_history( $stat );

  $data = [
    'result'  => $stat
  ];
  wp_send_json( $data );
  
  exit();
}

if ( wp_doing_ajax() ) {
  add_action( 'wp_ajax_statistic', 'statistic_media' );
  add_action( 'wp_ajax_nopriv_statistic', 'statistic_media' );
}