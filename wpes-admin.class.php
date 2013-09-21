<?php
namespace WPES;

class Admin
{

/**
 * クラスの初期化
 */
    public static function _init() {
        static $instance = false;

        if (!$instance) {
            $instance = new Admin;
        }

        return $instance;
    }

/**
 * Constructor.
 */
    public function __construct() {
        add_action('admin_init', array(&$this, 'plugin_admin_init'));
        add_action('save_post', array(&$this, 'save_event'));
        add_filter('manage_posts_columns', array(&$this, 'event_columns'));
        add_action('manage_posts_custom_column', array(&$this, 'add_event_column'), 10, 2);
        add_action('pre_get_posts', array(&$this, 'wpec_date_pre_get_posts'));
        add_filter('get_the_date', array(&$this, 'date_exchange'));
    }

/**
 * プラグイン初期化
 */
    function plugin_admin_init() {
        // カスタムフィールドを追加
        add_meta_box('events_date', __('イベント日時', 'wpes'), array(&$this, 'event_metabox'), POST_TYPE);
    }

/**
 * カスタムフィールド追加
 */
    function event_metabox() {
        global $post;
        $start_date = $start_time = $end_date = $end_time = '';
        $custom = get_post_custom($post->ID);
        $event_start_date = $custom['event_start_date'][0];
        if (!empty($event_start_date)) {
            $start_date = date('Y-m-d', $event_start_date);
            $start_time = date('H:i', $event_start_date);
        }
        $event_end_date = $custom['event_end_date'][0];
        if (!empty($event_end_date)) {
            $end_date = date('Y-m-d', $event_end_date);
            $end_time = date('H:i', $event_end_date);
        }
        $events_nonce = wp_create_nonce(plugin_basename(__FILE__));
        echo <<< EOF
    <input type="hidden" name="events_nonce" value="{$events_nonce}" />
	<div id="event-meta">
	<table class="form-table">
		<tr>
			<th><strong>イベント開始</strong></th>
			<td>日にち：<input type="text" name="start_date" id="start_date" value="{$start_date}" autocomplete="off" onpaste="return false" oncontextmenu="return false" />　時間：<input type="text" name="start_time" id="start_time" class="small-text" value="{$start_time}" /></td>
		</tr>
		<tr>
			<th><strong>イベント終了</strong></th>
			<td>日にち：<input type="text" name="end_date" id="end_date" value="{$end_date}" autocomplete="off" onpaste="return false" oncontextmenu="return false" />　時間：<input type="text" name="end_time" id="end_time" class="small-text" value="{$end_time}" /></td>
		</tr>
	</table>
	</div>
EOF;
    }

/**
 * カスタムフィールド保存
 */
    function save_event($post_id) {
        extract($_POST);
        if (!wp_verify_nonce($events_nonce, plugin_basename(__FILE__))) {
            return $post_id;
        }

        // 自動保存ルーチンかどうかのチェック
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id; 
        }

        // パーミッションのチェック
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }

        $event_start_date = strtotime($start_date .' '. $start_time);
        $event_end_date = strtotime($end_date .' '. $end_time);

        // 保存
        update_post_meta($post_id, 'event_start_date', $event_start_date);
        update_post_meta($post_id, 'event_end_date', $event_end_date);        
    }

/**
 * 一覧画面にカラムを追加
 */
    function event_columns($columns) {
        global $post_type;
        if ($post_type == POST_TYPE) {
            $columns['start_date'] = '開始日時';
            $columns['end_date'] = '終了日時';
            $columns['event_category'] = 'カテゴリー';
        }
        unset($columns['date']);
        return $columns;
    }

/**
 * 一覧画面表示の内容を生成
 */
    function add_event_column($column_name, $post_id) {
        // 開始日時の表示
        if ($column_name == 'start_date') {
            echo date('Y年m月d日 G時i分', get_post_meta($post_id, 'event_start_date', true));
        }
        // 終了日時の表示
        if ($column_name == 'end_date') {
            echo date('Y年m月d日 G時i分', get_post_meta($post_id, 'event_end_date', true));
        }
        // カテゴリーの表示
        if ($column_name == 'event_category') {
            $terms = get_the_terms($post_id, POST_TYPE . '_category');
            if ((!empty($terms)) && (is_array($terms))) {
                foreach ($terms as $key => $value) {
                        $categories[] = esc_attr($value->name);
                }
                echo implode(', ', $categories);
            }
        }
    }

/**
 * 取得データの並び替え
 */
    function wpec_date_pre_get_posts($query) {
        if (($query->is_date()) && ($query->query['post_type'] == POST_TYPE)) {
            extract($query->query);
            if (!empty($m)) {
                $current_day = strtotime($m);
            } else {
                $current_day = strtotime($year . $monthnum . $day);
            }
            $next_day = strtotime('+1 day', $current_day);
            $meta_query = array(
                array(
                    'key' => 'event_start_date',
                    'value' => $current_day,
                    'compare' => '>='
                ),
                array(
                    'key' => 'event_start_date',
                    'value' => $next_day,
                    'compare' => '<'
                )
            );

            $query->set('meta_query', $meta_query);
            $query->set('year' , '');
            $query->set('monthnum' , '');
            $query->set('day' , '');
            $query->set('m' , '');
            $query->set('orderby', 'meta_value');
            $query->set('order', 'DESC');
        }
    }

/**
 * 日時を開始日に入れ替え
 */
    function date_exchange($date) {
        global $post;
        if ($post->post_type == POST_TYPE) {
            $start_date = $start_time = $end_date = $end_time = '';
            $date_format = (get_option('date_format')) ? get_option('date_format') : 'Y-m-d';
            $time_format = (get_option('time_format')) ? get_option('time_format') : 'H:i';
            $custom = get_post_custom($post->ID);
            $event_start_date = $custom['event_start_date'][0];
            if (!empty($event_start_date)) {
                $start_date = date($date_format, $event_start_date);
                $start_time = date($time_format, $event_start_date);
            }
            return $start_date;
        }
        return $date;
    }

}
