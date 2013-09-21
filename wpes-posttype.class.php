<?php
namespace WPES;

class PostType
{

    protected $default = array(
        'menu' => 'イベント',
        'name' => 'イベント',
        'post_type' => POST_TYPE,
        'category' => true,
        'taxonomy' => false,
        'supports' => array('title', 'editor', 'thumbnail')
    );

    protected $setting = array();

/**
 * クラスの初期化
 */
    public static function _init() {
        static $instance = false;

        if (!$instance) {
            $instance = new PostType;
        }

        return $instance;
    }

/**
 * Constructor.
 */
    public function __construct($setting = array()) {
        $this->setting = array_merge($this->default, $setting);

        add_action('init', array(&$this, 'wpes_post_init'));
        add_filter('post_updated_messages', array(&$this, 'wpes_updated_messages'));
    }

/**
 * カスタム投稿タイプを作成
 */
    public function wpes_post_init() {
        $setting = $this->setting;

        $labels = array(
            'name' => __($setting['menu']),
            'singular_name' => sprintf(__('%s一覧'), $setting['name']),
            'add_new' => __('新規追加'),
            'add_new_item' => sprintf(__('%sを追加'), $setting['name']),
            'edit_item' => sprintf(__('%sを編集'), $setting['name']),
            'new_item' => sprintf(__('新しい%s'), $setting['name']),
            'view_item' => sprintf(__('%sを表示'), $setting['name']),
            'search_items' => sprintf(__('%sを探す'), $setting['name']),
            'not_found' =>  sprintf(__('%sはありません'), $setting['name']),
            'not_found_in_trash' => sprintf(__('ゴミ箱に%sはありません'), $setting['name']),
            'parent_item_colon' => ''
        );
        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'menu_position' => 5,
            'menu_icon' => WP_PLUGIN_URL . DS . PLUGIN_DIR . DS . 'img' . DS . 'calendar.png',
            'supports' => $setting['supports'],
            'has_archive' => true
        );
        register_post_type($setting['post_type'], $args);

        if ($setting['category'] == true) {
            //カテゴリータイプ
            $args = array(
                'label' => 'カテゴリー',
                'public' => true,
                'show_ui' => true,
                'hierarchical' => true
            );
            register_taxonomy($setting['post_type'] . '_category', $setting['post_type'], $args);
        }

        if ($setting['taxonomy'] == true) {
            //タグタイプ
            $args = array(
                'label' => 'タグ',
                'public' => true,
                'show_ui' => true,
                'hierarchical' => false
            );
            register_taxonomy($setting['post_type'] . '_tag', $setting['post_type'], $args);
        }
    }

/**
 * カスタム投稿タイプのメッセージ設定
 */
    public function wpes_updated_messages($messages) {
        global $post;
        $setting = $this->setting;

        $messages[$setting['post_type']] = array(
            0 => "",// ここは使用しません
            1 => sprintf(__('%sを更新しました <a href="%s">記事を見る</a>'), $setting['name'], esc_url(get_permalink($post->ID))),
            2 => __('カスタムフィールドを更新しました'),
            3 => __('カスタムフィールドを削除しました'),
            4 => sprintf(__('%s更新'), $setting['name']),
            5 => isset($_GET['revision']) ? sprintf(__(' %s 前に%sを保存しました'), wp_post_revision_title((int)$_GET['revision'], false), $setting['name']) : false,
            6 => sprintf(__('%sが公開されました <a href="%s">記事を見る</a>'), $setting['name'], esc_url(get_permalink($post->ID))),
            7 => sprintf(__('%s記事を保存'), $setting['name']),
            8 => sprintf(__('%s記事を送信 <a target="_blank" href="%s">プレビュー</a>'), $setting['name'], esc_url(add_query_arg('preview', 'true', get_permalink($post->ID)))),
            9 => sprintf(__('%sを予約投稿しました: <strong>%s</strong>. <a target="_blank" href="%s">プレビュー</a>'),
                         $setting['name'], date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post->ID))),
            10 => sprintf(__('%sの下書きを更新しました <a target="_blank" href="%s">プレビュー</a>'), $setting['name'], esc_url(add_query_arg('preview', 'true', get_permalink($post->ID)))),
        );

        return $messages;
    }

}