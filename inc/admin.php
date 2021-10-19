<?php

namespace Statistic\Inc;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {
  public static $instance = null;

  public static function instance() {
    if (!self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  function __construct() {
    add_action('admin_menu', [$this, 'create_statistic_admin_menu'], 9);
    add_action('admin_menu', [$this, 'add_admin_menu_page']);
    add_filter("plugin_action_links_" . \StatisticHelp::get_plugin_basename() , [$this, 'statistic_plugin_settings_link'] );
  }
  public function statistic_plugin_settings_link($links) { 
    $settings_link = "<a href='edit.php?page=statistic_settings'>Статистика. Жамкай поглядеть</a>"; 
    array_unshift( $links, $settings_link ); 
    return $links; 
  }
  public function add_admin_menu_page() {
    add_submenu_page( 'edit.php', 'Статистика видео', 'Статистика видео', 'manage_options', 'statistic_settings', [$this, 'get_settings_page'], 10 ); 
  }

  public function create_statistic_admin_menu() {
    register_setting( 'statistic_option_group', 'statistic_options', [$this, 'statistic_validation_settings'] );
    add_settings_section( 'statistic_sections', 'Статистика', '', 'statistic_settings' ); 
    // $period = array(
    //   'type'      => 'date',
    //   'id'        => 'period',
    //   'label_for' => 'period',
    //   'value'     => strtotime(date('Y-m-d'))
    // );
    // add_settings_field( 'period', 'Статистика от: ', [$this, 'statistic_display'], 'statistic_settings', 'statistic_sections', $period );
  }
  public function statistic_display($args) {
    extract( $args );
    $val = '';
    if (!empty($this->options[$id])) {
        $val = esc_attr( stripslashes($this->options[$id]) );
    } 

    switch ( $id ) {
        case 'period': 
            echo "<input class='regular-text' type='date' id='$id' name='statistic_options[$id]' value='$val' />";  
            echo "<br />"; 
        break;
    }    
  }
  public function statistic_validation_settings() {
    foreach($input as $k => $v) {
      $valid_input[$k] = trim($v);
    }
    return $valid_input;
  }
  public function get_settings_page() { ?>
    <div class="wrap">
        
        <form id="statistic_options_form" action="options.php" method="POST">
            <?php 
                settings_fields( 'statistic_option_group' );   
                do_settings_sections( 'statistic_settings' ); 
                // submit_button('Сохранить настройки');
            ?>
            <!-- <label>Посмотрели и хватит</label> -->
            <?= $this->render_table() ?>
        </form>
    </div>
  <?php
  }  

  private function get_unique_pages($data) {
    $result = [];
    foreach ( $data as $row ) {
      if ( ! in_array($row->url_page, $result) ) {
        $result[] = $row->url_page;
      }
    }
    return $result;
  }

  private function get_unique_files($data) {
    $result = [];
    foreach ( $data as $row ) {
      if ( ! in_array($row->url_file, $result) ) {
        $result[] = $row->url_file;
      }
    }
    return $result;
  }

  private function get_statistic_file($file_rows) {
    $rounds = [
      'all_time'  => 0,
      'count'     => 0
    ];
    
    $result = [
      'starts'   => 0,
      'playings' => 0,
      'pauses'   => 0,
      'endeds'   => 0,
      'average'  => 0
    ];
    foreach ( $file_rows as $key => $row ) {
      switch ( $row->stat_action ) {
        case 'pause':
          $result['pauses']++;
          if (isset($file_rows[$key - 1])) {
            if ($file_rows[$key - 1]->stat_action == 'playing') {
              $rounds['all_time'] += $row->value->time - $file_rows[$key - 1]->value->time;
              $rounds['count']++;
            }
          }
        break;
        case 'playing':
          if ($row->value->time - 1 <= 0) {
            $result['starts']++;
          } else {
            $result['playings']++;
          }
        break;
        case 'ended':
          $result['endeds']++;
          if (isset($file_rows[$key - 1])) {
            if ($file_rows[$key - 1]->stat_action == 'playing') {
              $rounds['all_time'] += $row->value->time - $file_rows[$key - 1]->value->time;
              $rounds['count']++;
            }
          }
        break;
      }
    }

    if ($rounds['count']) {
      $result['average'] = round( $rounds['all_time'] / $rounds['count'], 2 );
    }
    return $result;
  }

  private function get_page_table_data($page, $data) {
    $page_data = wp_list_filter( $data, ['url_page' => $page] );
    $files = $this->get_unique_files($page_data);

    $stats = [];
    foreach ( $files as $file ) {
      $file_rows = wp_list_filter( $page_data, ['url_file'=>$file] );
      $stat = $this->get_statistic_file($file_rows);
      $stat['url_file'] = $file;
      $stats[] = $stat;
    }
    return $stats;
  }

  private function render_table() {
    ob_start();
    $data = StatisticDB::get_history();
    $pages = $this->get_unique_pages($data);
    if (count($pages)) {
      foreach ($pages as $page) {
        $page_table = $this->get_page_table_data($page, $data);
        echo "<h3>Страница: <a target='_blanck' href='$page'>$page</a></h3>";
        echo '<table border="1" style="width: 100%; text-align: left; border-collapse: collapse;">';
          echo '<thead>';;
            echo '<tr>';
              echo '<th>Файл:</th>';
              echo '<th>Воспроизведений с начала:</th>';
              echo '<th>Воспроизведений после паузы:</th>';
              echo '<th>Приостановлено:</th>';
              echo '<th>Досматриваний:</th>';
              echo '<th>Среднее время просмотра:</th>';
            echo '</tr>';
          echo '</thead>';
          echo '<tbody>';

            foreach ($page_table as $row) { 
              echo "<tr>";
                echo "<td><a target='_blanck' href='{$row['url_file']}'>{$row['url_file']}</a> </td>";
                echo "<td> {$row['starts']} </td>";
                echo "<td> {$row['playings']} </td>";
                echo "<td> {$row['pauses']} </td>";
                echo "<td> {$row['endeds']} </td>";
                echo "<td> {$row['average']} сек.</td>";
              echo "</tr>";
              ?>
            <?php } 
        echo '</table>';
      } // end foreach page
    } else {
      echo "<p>Нет данных для статистики</p>";
    }
    return ob_get_clean();
  }


}

Admin::instance();