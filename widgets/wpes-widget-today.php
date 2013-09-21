<?php
if (!defined('ABSPATH')) exit;

class WpesTodayWidget extends WP_Widget
{

    function WpesTodayWidget() {
        parent::WP_Widget(false, $name = __('Today\'s event', 'wpes'));
    }

    function widget($args, $instance) {
        extract($args);
        $post_type = WPES\POST_TYPE;
        $category = ($instance['wpes_today_category'] == '') ? '' : esc_attr($instance['wpes_today_category']);
        $widget_title = apply_filters('widget_title', empty($instance['wpes_today_title']) ? '' : $instance['wpes_today_title']);
        $widget_empty_text = ($instance['wpes_today_empty_text'] == '') ? __('本日のイベントはありません。', 'wpes') : esc_attr($instance['wpes_today_empty_text']);
        $current_day = strtotime(date('Y-m-d'));
        $next_day =strtotime('+1 day', $current_day);

        $params = array(
            'post_type' => $post_type,
            'post_status' => 'publish',	
            'posts_per_page' => -1,
            'orderby' => 'meta_value',
            'order' => 'DESC',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'event_start_date',
                    'value' => array($current_day, $next_day),
                    'compare' => 'BETWEEN'
                ),
                array(
                    'key' => 'event_end_date',
                    'value' => array($current_day, $next_day),
                    'compare' => 'BETWEEN'
                )
            )
        );

        if (!empty($category)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => $post_type . '_category',
                    'field' => 'id',
                    'terms' => $category
                )
            );
        }

        $html = '';
        $newloop = new WP_Query($params);
        if ($newloop->have_posts()) {
            $html = '<ul>';
            while($newloop->have_posts()):
                $newloop->the_post();
                $link = get_permalink();
                $title = get_the_title();
                $html .= '<li class="wpes_item"><a href="' . $link . '">' . $title . '</a></li>';
            endwhile;
            $html .= '</ul>';
        } else {
            $html .= '<p class="wpes_empty_text">' . $widget_empty_text . '</p>';
        }

        echo $before_widget;
        if (!empty($widget_title)) {
            echo $before_title . $widget_title . $after_title;
        }
        echo $html;
        echo $after_widget;
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['wpes_today_title'] = strip_tags($new_instance['wpes_today_title']);
        $instance['wpes_today_empty_text'] = strip_tags($new_instance['wpes_today_empty_text']);
        $instance['wpes_today_category'] = $new_instance['wpes_today_category'];
        return $instance;
    }

    function form($instance) {
        $default = array(
            'wpes_today_title' => '',
            'wpes_today_empty_text' => '',
            'wpes_today_category' => false
        );
        $instance = wp_parse_args((array)$instance, $default);
        $post_cats = get_categories(array('hide_empty' => 0, 'taxonomy' => WPES\POST_TYPE . '_category'));
        $title = strip_tags($instance['wpes_today_title']);
        $empty_text = strip_tags($instance['wpes_today_empty_text']);
?>
        <p>
            <label for="<?php echo $this->get_field_id('wpes_today_title'); ?>"><?php _e('Title', 'wpes'); ?>:</label>
            <input class="widefat" type="text" id="<?php echo $this->get_field_id('wpes_today_title'); ?>" name="<?php echo $this->get_field_name('wpes_today_title'); ?>" value="<?php echo $title; ?>" />
        </p>	
        <p>
            <label for="<?php echo $this->get_field_id('wpes_today_category'); ?>"><?php _e('Select Categories', 'wpes'); ?>:</label>
            <select class="widefat" id="<?php echo $this->get_field_id('wpes_today_category'); ?>" name="<?php echo $this->get_field_name('wpes_today_category'); ?>">
                <option value=""><?php _e('All Categories', 'wpes'); ?></option>
                <?php
                foreach ($post_cats as $post_cat) {
                    echo '<option value="'
                     . intval($post_cat->term_id) . '"'
                     . selected($instance['wpes_today_category'], $post_cat->term_id, false)
                     . '>' . $post_cat->name . "</option>\n";
                }
                ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('wpes_today_empty_text'); ?>"><?php _e('Empty Text', 'wpes'); ?>:</label>
            <input class="widefat" type="text" id="<?php echo $this->get_field_id('wpes_today_empty_text'); ?>" name="<?php echo $this->get_field_name('wpes_today_empty_text'); ?>" value="<?php echo $empty_text; ?>" />
        </p>	
<?php
    }

}
