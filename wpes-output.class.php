<?php
namespace WPES;

class Output
{

/**
 * クラスの初期化
 */
    public static function _init() {
        static $instance = false;

        if (!$instance) {
            $instance = new Output;
        }

        return $instance;
    }

/**
 * Constructor.
 */
    public function __construct() {
    }

/**
 * カレンダー生成
 *
 * @param $year 年
 * @param $month 月
 * @param $events イベントデータ
 * @return str
 */
    function get_calendar($year, $month, $num, $format, $events, $tooltip = false, $notitle = false) {
        // 月初月末の取得
        $timestamp = mktime(0, 0, 0, $month, 1, $year);
        $first_day = date('d', $timestamp);
        $last_day = date('t', $timestamp);

        // 日時情報を取得
        $days = $this->set_days($year, $month, $first_day, $last_day);

        // 月初の曜日を見て前に余白を追加
        if ($days[$first_day]['wday'] > 0) {
            $temp_days = array_merge(array_fill(0, $days[$first_day]['wday'], ''), $days);
        } else {
            $temp_days = $days;
        }
        // 月末の曜日を見て後ろに余白を追加
        if ($days[$last_day]['wday'] < 6) {
            $temp_days = array_merge($temp_days, array_fill(0, 6 - $days[$last_day]['wday'], ''));
        }
        $days = $temp_days;

        $weeks = array_chunk($days, 7);

        $html = '';
        if ((!empty($num)) && (is_numeric($num))) {
            $current_month = date('n');
            $max_month = $current_month + $num;
            $active = '';
            $link = get_permalink();
            for ($i = $current_month; $i < $max_month; $i++) {
                $tab_year = date('Y', strtotime(date('Y-m') . '+' . $i . ' Month'));
                $tab_month = date('n', strtotime(date('Y-m') . '+' . $i . ' Month'));
                if ($tab_month == $month) $active = ' class="wpes-active-month"';
                $params = array(
                    'cal_year' => $tab_year,
                    'cal_month' => $tab_month
                );
                $link_tags[] = '<li' . $active . '><a href="' . $this->http_build_url($link, $params) . '">' . $tab_month . '月</a></li>';
                $active = '';
            }
            $link_tag = implode("\n", $link_tags);
            $html = <<< EOF
<ul class="wpes-tabs">
{$link_tag}
</ul>
EOF;
        }

        $cal_title = date($format, $timestamp);

        $html .= <<< EOF
<table class="wpes-calendar">
	<caption>{$cal_title}</caption>
	<thead>
	<tr>
		<th scope="col" class="wpes-sun">日</th>
		<th scope="col" class="wpes-mon">月</th>
		<th scope="col" class="wpes-tue">火</th>
		<th scope="col" class="wpes-wed">水</th>
		<th scope="col" class="wpes-thu">木</th>
		<th scope="col" class="wpes-fri">金</th>
		<th scope="col" class="wpes-sat">土</th>
	</tr>
	</thead>
	<tbody>\n
EOF;

        foreach ($weeks as $week) {
            $html .= '<tr>';
            foreach ($week as $day => $data) {
                if (!empty($data['mday']) && (array_key_exists($data['time'], $events))) {
                    $class = ' class="wpes-' . $data['status'] . ' wpes-' . $data['ewday'] . '"';
                    $link = $this->get_event_link($events[$data['time']], $data['time'], $tooltip, $notitle);
                    $html .= '<td' . $class . '>' . $link . '</td>';
                } elseif (!empty($data['mday'])) {
                    $class = ' class="wpes-' . $data['status'] . ' wpes-' . $data['ewday'] . '"';
                    $html .= '<td' . $class . '>' . $data['mday'] . '</td>';
                } else {
                    $html .= '<td>&#160;</td>';
                }
                $html .= "\n";
            }
            $html .= '</tr>';
            $html .= "\n";
        }
        $html .= '</tbody>';
        $html .= "\n";
        $html .= '</table>';
        $html .= "\n";
        return $html;
    }

/*
 * 日時情報を取得整形
 *
 * @param $year 年
 * @param $month 月
 * @param $first_day 月初の日付
 * @param $last_day 月末の日付
 * @return array
 */
    function set_days($year, $month, $first_day, $last_day) {
        $days = array();
        $week_names_ja = array('日', '月', '火', '水', '木', '金', '土');
        $week_names_en = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
        $now_time = strtotime(date('Ymd'));

        for ($i = $first_day; $i <= $last_day; $i++) {
            $day = sprintf('%02d', $i);
            $time = strtotime($year . $month . $day);
            $tmp_array = getdate($time);

            unset($tmp_array['seconds']);
            unset($tmp_array['minutes']);
            unset($tmp_array['hours']);
            unset($tmp_array[0]);

            $tmp_array['jwday'] = $week_names_ja[$tmp_array['wday']];
            $tmp_array['ewday'] = $week_names_en[$tmp_array['wday']];
            $tmp_array['time'] = $time;

            if ($now_time == $time) {
                $tmp_array['status'] = 'today';
            } elseif ($now_time > $time) {
                $tmp_array['status'] = 'past';
            } elseif ($now_time < $time) {
                $tmp_array['status'] = 'future';
            } else {
                $tmp_array['status'] = 'other';
            }

            $days[date('d', $time)] = $tmp_array;
        }

        return $days;
    }

/*
 * イベント情報を取得整形
 *
 * @param $year 年
 * @param $month 月
 * @return array
 */
    function set_events($year, $month, $category = null) {
        $events = array();
        $current_month = strtotime($year . $month . '01');
        $next_month = strtotime('+1 month', $current_month);
        $params = array(
            'post_type' => POST_TYPE,
            'post_status' => 'publish',	
            'posts_per_page' => -1,
            'orderby' => 'meta_value',
            'order' => 'DESC',
            'meta_query' => array(
                'relation'=>'OR',
                array(
                    'key' => 'event_start_date',
                    'value' => array($current_month, $next_month),
                    'compare' => 'BETWEEN'
                ),
                array(
                    'key' => 'event_end_date',
                    'value' => array($current_month, $next_month),
                    'compare' => 'BETWEEN'
                )
            )
        );

        if (!empty($category)) {
            $params['tax_query'] = array(
                array(
                    'taxonomy' => POST_TYPE . '_category',
                    'field' => 'id',
                    'terms' => $category
                )
            );
        }

        $results = get_posts($params);

        $one_day = 60 * 60 * 24;
        foreach ($results as $result) {
            $custom = get_post_custom($result->ID);
            $start_date = strtotime(date('Ymd', $custom['event_start_date'][0]));
            $end_date = strtotime(date('Ymd', $custom['event_end_date'][0]));

            $tmp_array['id'] = $result->ID;
            $tmp_array['post_date'] = $result->post_date;
            $tmp_array['post_content'] = $result->post_content;
            $tmp_array['post_title'] = $result->post_title;
            $tmp_array['post_excerpt'] = $result->post_excerpt;
            $tmp_array['post_name'] = $result->post_name;
            $tmp_array['guid'] = $result->guid;
            $tmp_array['event_start_date'] = $start_date;
            $tmp_array['event_end_date'] = $end_date;

            for ($i = $start_date; $i <= $end_date; $i += $one_day) {
                $events[$i][] = $tmp_array;
            }
        }
        return $events;
    }

/**
 * イベントリンクを生成
 *
 * @param array $events イベントデータ
 * @return str
 */
    function get_event_link($events = null, $timestamp, $tooltip = false, $notitle = false) {
        if (!$events) return date('j', $timestamp);

        if ($notitle) {
            $datetime = getdate($timestamp);
            $daylink = get_day_link($datetime['year'], $datetime['mon'], $datetime['mday']);
            $params = array(
                'post_type' => POST_TYPE
            );
            $link = $this->http_build_url($daylink, $params);
            $link = '<a href="' . $link . '">' . date('j', $timestamp) . '</a>';
        } elseif ($tooltip) {
            $link = $this->set_event_link_tooltip($events);
            $link = date('j', $timestamp) . '<br>' . $link;
        } else {
            $link = $this->set_event_link($events);
            $link = date('j', $timestamp) . '<br>' . $link;
        }
        return $link;
    }

/**
 * URLを解析してパラメータを付与する
 *
 * @param str $url ページのURL
 * @param array $params 付与するパラメータ
 * @return str
 */
    protected function http_build_url($url = null, $params = array()) {
        if (!$url) return;
        $parse = parse_url($url);
        $parent_params = (array_key_exists('query', $parse)) ? $parse['query'] : '';
        if (!empty($parent_params)) {
            $url = $url . '&';
        } else {
            $url = $url . '?';
        }
        return $url . http_build_query($params);
    }

/**
 * リンク生成
 */
    protected function set_event_link($events = null) {
        if (!$events) {
            return;
        }

        for ($i = 0; $i < count($events); $i++) {
            $links[$i] = '<a href="' . $events[$i]['guid'] . '" class="wpes-event-title">' . $events[$i]['post_title'] . '</a>';
        }
        $link = implode('<br />', $links);
        return $link;
    }

/**
 * リンク生成（tooltip）
 */
    protected function set_event_link_tooltip($events = null) {
        if (!$events) {
            return;
        }

        for ($i = 0; $i < count($events); $i++) {
            $links[$i] = '<a href="' . $events[$i]['guid'] . '" class="wpes-event-title-tooltip">●<span>' . $events[$i]['post_title'] . '</span></a>';
        }
        $link = implode(' ', $links);
        return $link;
    }

}