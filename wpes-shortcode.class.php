<?php
namespace WPES;

class Shortcode extends Output
{

/**
 * クラスの初期化
 */
    public static function _init() {
        static $instance = false;

        if (!$instance) {
            $instance = new Shortcode;
        }

        return $instance;
    }

/**
 * Constructor.
 */
    public function __construct() {
        parent::__construct();

        // カレンダーショートコード
        add_shortcode('event_calendar', array(&$this, 'event_calendar'));
    }

/*
 * カレンダー表示
 */
    function event_calendar($atts) {
        $get_year = (!empty($_GET['cal_year'])) ? htmlspecialchars($_GET['cal_year']) : date('Y');
        $get_month = (!empty($_GET['cal_month'])) ? sprintf('%02d', htmlspecialchars($_GET['cal_month'])) : date('m');
        extract(shortcode_atts(array(
            'post_type' => POST_TYPE,
            'year' => $get_year,
            'month' => $get_month,
            'num' => 4,
            'format' => 'Y-m',
            'tooltip' => false
        ), $atts));

        $events = $this->set_events($year, $month);
        return $this->get_calendar($year, $month, $num, $format, $events, $tooltip);
    }

}