<?php
namespace WPES;

class TemplateTag
{

/**
 * Constructor.
 */
    public function __construct() {
    }

/*
 * 開始日取得表示
 */
    function the_start_date($format = 'Y-m-d') {
        echo $this->get_the_start_date($format);
    }

/*
 * 開始日取得
 */
    function get_the_start_date($format = 'Y-m-d') {
        $event_start_date = $this->get_custom_date('event_start_date');
        $start_date = date($format, $event_start_date);
        return $start_date;
    }

/*
 * 開始時間取得表示
 */
    function the_start_time($format = 'H:i') {
        echo $this->get_the_start_time($format);
    }

/*
 * 開始時間取得
 */
    function get_the_start_time($format = 'H:i') {
        $event_start_date = $this->get_custom_date('event_start_date');
        $start_date = date($format, $event_start_date);
        return $start_date;
    }

/*
 * 終了日取得表示
 */
    function the_end_date($format = 'Y-m-d') {
        echo $this->get_the_end_date($format);
    }

/*
 * 終了日取得
 */
    function get_the_end_date($format = 'Y-m-d') {
        $event_end_date = $this->get_custom_date('event_end_date');
        $end_date = date($format, $event_end_date);
        return $end_date;
    }

/*
 * 終了時間取得表示
 */
    function the_end_time($format = 'H:i') {
        echo $this->get_the_end_time($format);
    }

/*
 * 終了時間取得
 */
    function get_the_end_time($format = 'H:i') {
        $event_end_date = $this->get_custom_date('event_end_date');
        $end_date = date($format, $event_end_date);
        return $end_date;
    }

/*
 * カスタムフィールドデータを取得
 */
    function get_custom_date($field = null) {
        if (!$field) {
            return;
        }

        global $post;
        $custom = get_post_custom($post->ID);
        $data = $custom[$field][0];
        return $data;
    }

}
