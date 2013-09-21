<?php
namespace WPES;

if ((!defined('ABSPATH')) && (!defined('WP_UNINSTALL_PLUGIN'))) {
    exit();
} else {
    Uninstall::plugin_uninstall();
}

class Uninstall
{

/**
 * プラグインアンインストール時の処理
 */
    public static function plugin_uninstall() {
        global $wpdb;

        delete_option('wpes_version');

        $posts = get_posts(array(
            'numberposts' => -1,
            'post_type' => 'wpes',
            'post_status' => 'any'
        ));

        foreach ($posts as $post) {
            wp_delete_post($post->ID, true);
        }
    }

}
