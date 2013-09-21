<?php
if (!defined('ABSPATH')) exit;

class WpecCalendarWidget extends WP_Widget
{

    function WpecCalendarWidget() {
        parent::WP_Widget(false, $name = __('Event Calendar', 'wpes'));
    }

    function widget($args, $instance) {
        $Output = new WPES\Output;

        extract($args);
        $widget_title = apply_filters('widget_title', empty($instance['wpes_calendar_title']) ? '' : $instance['wpes_calendar_title']);
        $widget_category = (!empty($instance['wpes_calendar_category'])) ? esc_attr($instance['wpes_calendar_category']) : '';
        $widget_months = (!empty($instance['wpes_calendar_months'])) ? $instance['wpes_calendar_months'] : '';
        $widget_date_format = (!empty($instance['wpes_date_format'])) ? $instance['wpes_date_format'] : '';

        $html = array();
        $timestamp = strtotime(date('Ym'));
        for ($i = 0; $i < $widget_months; $i++) {
            $year = date('Y', $timestamp);
            $month = date('m', $timestamp);
            $events = $Output->set_events($year, $month, $widget_category);
            $html[] = $Output->get_calendar($year, $month, null, $widget_date_format, $events, false, true);
            $timestamp = strtotime(date('Y-m', $timestamp) . ' +1 month');
        }

        echo $before_widget;
        if (!empty($widget_title)) {
            echo $before_title . $widget_title . $after_title;
        }
        echo implode('<br>', $html);
        echo $after_widget;
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['wpes_calendar_title'] = strip_tags($new_instance['wpes_calendar_title']);
        $instance['wpes_calendar_category'] = $new_instance['wpes_calendar_category'];
        $instance['wpes_calendar_months'] = $new_instance['wpes_calendar_months'];
        $instance['wpes_date_format'] = $new_instance['wpes_date_format'];
        return $instance;
    }

    function form($instance) {
        $default = array(
            'wpes_calendar_title' => '',
            'wpes_calendar_category' => false,
            'wpes_calendar_wpes_calendar_months' => false,
            'wpes_date_format' => 'Y/m'
        );
        $instance = wp_parse_args((array)$instance, $default);
        $title = strip_tags($instance['wpes_calendar_title']);
        $post_cats = get_categories(array('hide_empty' => 0, 'taxonomy' => WPES\POST_TYPE . '_category'));
        $months = array('1' => __('1ヶ月', 'wpes'), '2' => __('2ヶ月', 'wpes'), '3' => __('3ヶ月', 'wpes'));
        $formats = array(__('Y年n月', 'wpes'), 'Y/m', 'm/Y');
?>
        <p>
            <label for="<?php echo $this->get_field_id('wpes_calendar_title'); ?>"><?php _e('Title', 'wpes'); ?>:</label>
            <input class="widefat" type="text" id="<?php echo $this->get_field_id('wpes_calendar_title'); ?>" name="<?php echo $this->get_field_name('wpes_calendar_title'); ?>" value="<?php echo $title; ?>" />
        </p>	
        <p>
            <label for="<?php echo $this->get_field_id('wpes_calendar_category'); ?>"><?php _e('Select Categories', 'wpes'); ?>:</label>
            <select class="widefat" id="<?php echo $this->get_field_id('wpes_calendar_category'); ?>" name="<?php echo $this->get_field_name('wpes_calendar_category'); ?>">
                <option value=""><?php _e('All Categories', 'wpes'); ?></option>
                <?php
                foreach ($post_cats as $post_cat) {
                    echo '<option value="'
                     . intval($post_cat->term_id) . '"'
                     . selected($instance['wpes_calendar_category'], $post_cat->term_id, false)
                     . '>' . $post_cat->name . '</option>';
                    echo "\n";
                }
                ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('wpes_calendar_months'); ?>"><?php _e('Select Months', 'wpes'); ?>:</label>
            <select id="<?php echo $this->get_field_id('wpes_calendar_months'); ?>" name="<?php echo $this->get_field_name('wpes_calendar_months'); ?>">
                <?php
                foreach ($months as $month => $val) {
                    echo '<option value="'
                     . $month . '"'
                     . selected($instance['wpes_calendar_months'], $month, false)
                     . '>' . $val . '</option>';
                    echo "\n";
                }
                ?>
            </select>
        </p>
        <p>
            <legend><span><?php _e('Select Formats', 'wpes'); ?>:</span></legend>
                <?php
                foreach ($formats as $format) {
                    echo '<label title="'
                     . esc_attr($format) . '"><input type="radio" name="'
                     . $this->get_field_name('wpes_date_format') . '" value="'
                     . esc_attr($format) . '"';
                     if ($instance['wpes_date_format'] == $format) {
                        echo ' checked="checked"';
                     }
                     echo ' /> <span>' . date($format) . '</span></label><br />';
                     echo "\n";
                }
                ?>
        </p>
<?php
    }

}
