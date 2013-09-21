<?php
namespace WPES;

if (!defined('ABSPATH')) exit;

// ウィジェットファイル読み込み
require_once dirname(__FILE__) . DS . 'wpes-widget-today.php';
require_once dirname(__FILE__) . DS . 'wpes-widget-calendar.php';

// ウィジェット実行
add_action('widgets_init', create_function('', 'return register_widget("WpesTodayWidget");'));
add_action('widgets_init', create_function('', 'return register_widget("WpecCalendarWidget");'));
