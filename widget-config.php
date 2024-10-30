<?php
/**
 * This file could be used to catch submitted form data. When using a non-configuration
 * view to save form data, remember to use some kind of identifying field in your form.
    * Description: Author filters plugin integrates an author filter drop down to sort listing with respect to an author on post, page, custom post type in administration.
    * Author: Clarion Technologies
    * Author URI: http://www.clariontechnologies.co.in
    * Plugin URI: 
    * Text Domain: lu-widget
    * License: GPLv2
    * @package Latest Users Dashboard Widget
 */

    defined('ABSPATH') or die('Direct access is restricted!');

    $pre_query_count = self::get_dashboard_widget_option(self::wid, 'query_count');
    $pre_days_count = self::get_dashboard_widget_option(self::wid, 'days_count');
    $pre_user_roles = self::get_dashboard_widget_option(self::wid, 'user_roles');

    $query_count = (filter_var('query_count', FILTER_SANITIZE_STRING)) ? stripslashes(filter_input(INPUT_POST, "query_count", FILTER_SANITIZE_STRING)) : '';
    $days_count = (filter_var('days_count', FILTER_SANITIZE_STRING)) ? stripslashes(filter_input(INPUT_POST, "days_count", FILTER_SANITIZE_STRING)) : '';
    $user_roles = (filter_var('user_roles', FILTER_SANITIZE_STRING)) ? stripslashes(filter_input(INPUT_POST, "user_roles", FILTER_SANITIZE_STRING)) : '';
    
    self::update_dashboard_widget_options(
            self::wid,                                  //The  widget id
            array(                                      //Associative array of options & default values
                'query_count' => $query_count,
                'days_count' => $days_count,
                'user_roles' => $user_roles,
            )
    );
?>
<h4><?php _e("Configuration : Latest users dashboard widget", "lu-widget"); ?></h4>
<?php /*
<p>This is the configuration part of the widget, and can be found and edited from <tt><?php echo __FILE__ ?></tt></p>
 * 
 */
?>
<?php /*
<input type="text" name="query_count" value="<?php echo $query_count; ?>" />
 * 
 */ ?>
<ul>
    <li>
        <p><?php _e("Number of records:", "lu-widget"); ?></p>
        <select name="query_count">
            <option value=""></option>
            <?php for($n=1; $n<=20; $n++){ ?>
                <?php if($n == $pre_query_count){ $selected = "selected='selected'"; }else{$selected="";} ?>
                <option value="<?php _e($n, "lu-widget"); ?>" <?php _e($selected, "lu-widget"); ?>><?php _e($n, "lu-widget"); ?></option>
            <?php } ?>
        </select>
    </li>
    <li>
        <p><?php _e("Range of days:", "lu-widget"); ?></p>
        <select name="days_count">
            <option value=""></option>
            <?php for($n=1; $n<=180; $n++){ ?>
                <?php $selected = ($n == $pre_days_count) ? "selected='selected'" : ""; ?>
                <option value="<?php _e($n, "lu-widget"); ?>" <?php _e($selected, "lu-widget"); ?>><?php _e($n, "lu-widget"); ?></option>
            <?php } ?>
        </select>
    </li>   
</ul>