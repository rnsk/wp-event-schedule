<?php
/*
Plugin Name: WP Event Schedule
Plugin URI: http://wordpress.org/plugins/wp-event-schedule/
Description: Create the event schedule.
Author: Yoshika (@rnsk)
Author URI: http://rnsk.net/
Version: 0.9.2
License: GPL2
License URI: license.txt
Text Domain: wpes
Domain Path: /lang/
*/

namespace WPES;

if (!defined('ABSPATH')) exit;

date_default_timezone_set(get_option('timezone_string'));

/**
 * プラグイン定数設定
 */
const VERSION = '1.0';
//const DB_VERSION = '1.0';

const POST_TYPE = 'wpes';

const PLUGIN_DIR = 'wp-event-schedule';
const CLASS_DIR = 'classes';
const TEMPLATE_DIR = 'templates';
const WIDGET_DIR = 'widgets';
const CSS_DIR = 'css';
const JS_DIR = 'js';

const DS = DIRECTORY_SEPARATOR;

// 外部ファイル読み込み
require_once dirname(__FILE__) . DS . 'wpes-admin.class.php';
require_once dirname(__FILE__) . DS . 'wpes-output.class.php';
require_once dirname(__FILE__) . DS . 'wpes-shortcode.class.php';
require_once dirname(__FILE__) . DS . 'wpes-posttype.class.php';
require_once dirname(__FILE__) . DS . 'wpes-template.class.php';
require_once dirname(__FILE__) . DS . WIDGET_DIR . DS . 'wpes-widget.php';

/**
 * 行事予定を作成するプラグイン
 *
 * @copyright  Copyright (c) Yoshika
 * @author     Yoshika (@rnsk)
 * @package    EventSchedule
 * @license    GPL2
 */

class EventSchedule
{

/**
 * クラスの初期化
 */
    public static function _init() {
        static $instance = false;

        if (!$instance) {
            ob_start();
            $instance = new EventSchedule;
        }

        return $instance;
    }

/**
 * Constructor.
 */
    protected function __construct() {
        session_start();

        // プラグイン有効時の処理
        register_activation_hook(__FILE__, array(&$this, 'plugin_active'));
        // プラグイン無効時の処理
        register_deactivation_hook(__FILE__, array(&$this, 'plugin_deactive'));

        // プラグイン用翻訳ファイル読み込み
        //load_plugin_textdomain('wpfb', false, WP_PLUGIN_DIR . DS . PLUGIN_DIR . DS . 'lang');

        // CSS & JS
        add_action('wp_print_styles', array(&$this, 'event_style'));
        add_action('admin_print_styles', array(&$this, 'admin_event_style'));
        add_action('admin_footer', array(&$this, 'admin_event_script'));
    }

/**
 * プラグイン有効時の処理
 */
    function plugin_active() {
        add_option('wpes_version', VERSION);
    }

/**
 * プラグイン無効時の処理
 */
    function plugin_deactive() {
        delete_option('wpes_version');
    }

/**
 * フロントCSS読み込み
 */
    function event_style() {
        wp_enqueue_style('wpes', WP_PLUGIN_URL . DS . PLUGIN_DIR . DS . CSS_DIR . DS . 'wpes.css');
        if (file_exists(TEMPLATEPATH . DS . 'wpes.css')) {
            $template_uri = get_template_directory_uri();
            wp_enqueue_style('wpes_overwrite', $template_uri . DS . 'wpes.css');
        }
    }

/**
 * 管理画面CSS読み込み
 */
    function admin_event_style() {
        global $post_type;
        if ($post_type == POST_TYPE) {
            wp_enqueue_style('wpes_jquery-ui-custom', WP_PLUGIN_URL . DS . PLUGIN_DIR . DS . CSS_DIR . DS . 'smoothness' . DS . 'jquery-ui-1.10.3.custom.min.css');
            wp_enqueue_style('wpes_jquery-ui-timepicker', WP_PLUGIN_URL . DS . PLUGIN_DIR . DS . CSS_DIR . DS . 'jquery-ui-timepicker-addon.css');
        }
    }

/**
 * 管理画面JS読み込み
 */
    function admin_event_script() {
        global $post_type;
        if ($post_type == POST_TYPE) {
            wp_enqueue_script('wpes_jquery-ui-timepicker', WP_PLUGIN_URL . DS . PLUGIN_DIR . DS . JS_DIR . DS . 'jquery-ui-timepicker-addon.js', array('jquery-ui-datepicker'));
            wp_enqueue_script('wpes_common', WP_PLUGIN_URL . DS . PLUGIN_DIR . DS . JS_DIR . DS . 'common.js');
        }
    }
}

EventSchedule::_init();
Admin::_init();
Shortcode::_init();
PostType::_init();

$WPES = new TemplateTag;
