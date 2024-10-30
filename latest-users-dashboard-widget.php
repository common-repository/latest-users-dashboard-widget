<?php
    /*
        * Plugin Name: Latest Users Dashboard Widget
        * Version: 1.0.1
        * Description: Latest Users Dashboard Widget extension integrates a welcome widget to display new users added to the system in a tabular format.
        * Author: Clarion Technologies
        * Author URI: http://www.clariontechnologies.co.in
        * Plugin URI: 
        * Text Domain: lu-widget
        * License: GPLv2
        * @package Latest Users Dashboard Widget
    */

    defined('ABSPATH') or die('Direct access is restricted!');

    add_action('wp_dashboard_setup', array('Latest_Users_Dashboard_Widget','init') );

        class Latest_Users_Dashboard_Widget {

            /**
             * The id of this widget, an unique identifier.
             */
            const wid = 'latest_users_widget';

            /**
                 * Hook to wp_dashboard_setup to add the widget.
            */
            
            public static function init() {
                //Register widget settings...
                self::update_dashboard_widget_options(
                    self::wid,                                  //The  widget id
                    array(                                      //Associative array of options & default values
                        'query_count' => 5,
                        'days_count' => 7,
                        'user_roles' => "all",
                    ),
                    true                                        //Add only (will not update existing options)
                );

                //Register the widget...
                wp_add_dashboard_widget(
                    self::wid,                                  //A unique slug/ID
                    __( 'Latest Users', 'nouveau' ),//Visible name for the widget
                    array('Latest_Users_Dashboard_Widget','widget'),      //Callback for the main widget content
                    array('Latest_Users_Dashboard_Widget','config')       //Optional callback for widget configuration content
                );
            }

            /**
             * Load the widget code
             */
            public static function widget() {
                require_once( 'widget.php' );
            }

            /**
             * Load widget config code.
             *
             * This is what will display when an admin clicks
             */
            public static function config() {
                require_once( 'widget-config.php' );
            }

            /**
             * Gets the options for a widget of the specified name.
             *
             * @param string $widget_id Optional. If provided, will only get options for the specified widget.
             * @return array An associative array containing the widget's options and values. False if no opts.
             */
            public static function get_dashboard_widget_options( $widget_id='' ) {
                //Fetch ALL dashboard widget options from the db...
                $opts = get_option( 'dashboard_widget_options' );

                //If no widget is specified, return everything
                if ( empty( $widget_id ) ){
                    return $opts;
                }

                //If we request a widget and it exists, return it
                if ( isset( $opts[$widget_id] ) ){
                    return $opts[$widget_id];
                }

                //Something went wrong...
                return false;
            }

            /**
             * Gets one specific option for the specified widget.
             * @param $widget_id
             * @param $option
             * @param null $default
             *
             * @return string
             */
            public static function get_dashboard_widget_option( $widget_id, $option, $default=NULL ) {

                $opts = self::get_dashboard_widget_options($widget_id);

                //If widget opts dont exist, return false
                if ( ! $opts ){
                    return false;
                }

                //Otherwise fetch the option or use default
                if ( isset( $opts[$option] ) && ! empty($opts[$option]) ){
                    return $opts[$option];   
                }else{
                    return ( isset($default) ) ? $default : false;
                }

            } // end function: get_dashboard_widget_option()

            /**
             * Saves an array of options for a single dashboard widget to the database.
             * Can also be used to define default values for a widget.
             *
             * @param string $widget_id The name of the widget being updated
             * @param array $args An associative array of options being saved.
             * @param bool $add_only If true, options will not be added if widget options already exist
             */
            public static function update_dashboard_widget_options( $widget_id , $args=array(), $add_only=false ) {
                //Fetch ALL dashboard widget options from the db...
                $opts = get_option( 'dashboard_widget_options' );

                //Get just our widget's options, or set empty array
                $w_opts = ( isset( $opts[$widget_id] ) ) ? $opts[$widget_id] : array();

                if ( $add_only ) {
                    //Flesh out any missing options (existing ones overwrite new ones)
                    $opts[$widget_id] = array_merge($args,$w_opts);
                }
                else {
                    //Merge new options with existing ones, and add it back to the widgets array
                    $opts[$widget_id] = array_merge($w_opts,$args);
                }

                //Save the entire widgets array back to the db
                return update_option('dashboard_widget_options', $opts);
            } // end function: update_dashboard_widget_options()

            public static function wpb_recently_registered_users() {
                global $wpdb;

                #$date_limit = 14;
                $date_limit = self::get_dashboard_widget_option(self::wid, 'days_count');
                $limit_count = self::get_dashboard_widget_option(self::wid, 'query_count');

                //Get today's date.
                $today = getdate();

                //Calculate midnight a week ago.
                $start_time = mktime(0, 0, 0,$today['mon'],($today['mday'] - $date_limit), $today['year']);

                //Get all posts from one week ago to the present.
               # $end_time = getdate();
                $end_time = mktime(0, 0, 0,$today['mon'],($today['mday']), $today['year']);
                $start_date = date('Y-m-d 00:00:00.000000', $start_time);
                $end_date = date('Y-m-d 23:59:59.000000', $end_time); 

                $recentusers = '<table class="recent-users widefat">';

                    if(isset($limit_count)){
                        $limit = esc_attr($limit_count);
                    }else{
                        $limit = 2;
                    }
                    
                    $sql_query = "SELECT ID, user_nicename, user_url, user_email FROM {$wpdb->users} WHERE"; 
                    $sql_query .= " user_registered BETWEEN '%s' AND '%s' ORDER BY ID DESC LIMIT %d";

                    $usernames = $wpdb->get_results($wpdb->prepare($sql_query, $start_date, $end_date, $limit));
                    
                    $recentusers .= '<thead>';
                        $recentusers .= '<th>&nbsp;</th>';
                        $recentusers .= '<th>'.esc_html__("USER", "lu-widget").'</th>';
                        $recentusers .= '<th>'.esc_html__("ROLE", "lu-widget").'</th>';
                        $recentusers .= '<th>'.esc_html__("EMAIL", "lu-widget").'</th>';
                    $recentusers .= '</thead>';

                    if($usernames){

                        foreach ($usernames as $username) {

                            $capabilities = $wpdb->prefix.'capabilities';
                            $user_data = get_userdata($username->ID);
                            $role = $user_data->roles[0];
                            $user_id = (int)$username->ID;
                        
                            $recentusers .= '<tr>';

                                $recentusers .= '<td><a href="'.admin_url( 'user-edit.php?user_id='.$user_id.'').'" title="'.esc_html__($username->user_nicename, "lu-widget").'">'.get_avatar($username->user_email, 45).'</a></td>';
                                $recentusers .= '<td><a href="'.admin_url( 'user-edit.php?user_id='.$user_id.'').'" title="'.esc_html__($username->user_nicename, "lu-widget").'">'.esc_html__($username->user_nicename, "lu-widget").'</a></td>';
                                $recentusers .= '<td>'.esc_html__($role).'</td>';
                                $recentusers .= '<td><a href="mailto:'.esc_html__($username->user_email, "lu-widget").'">'.esc_html__($username->user_email, "lu-widget").'</a></td>';
                            
                            $recentusers .= '</tr>';
                        } // end: foreach loop
                    }else{
                                $recentusers .= '<tr class="no-items">';
                                    $recentusers .= '<td colspan="4">'.esc_html__("No Latest Users Found.", "lu-widget").'</td>';
                                $recentusers .= '</tr>';
                    } // end: if / else condition - $usernames

                    //<p>'.count($usernames).' user record(s) were added in '.$date_limit.' days to the system.</p>
                    $recentusers .= '<tfoot>';
                        $recentusers .= '<th colspan="4"></th>';
                    $recentusers .= '</tfoot>';

                $recentusers .= '</table>';

                return $recentusers;  
            } // end function : wpb_recently_registered_users()

            public static function wpb_user_roles(){

                $roles_array = wp_dropdown_roles( 'editor' );

                return $roles_array;
            }
    } // end class: LU_Dashboard_Widget
