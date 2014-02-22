<?php
/*
Plugin Name: Developer Share Buttons
Description: Share buttons with no CSS and no JavaScript
Version: 1.0.0
Author: Terminal Pixel
Author URI: http://wwww.terminalpixel.co.uk/
License: GPL3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: dev-share-buttons
*/

if ( !class_exists( 'DeveloperShareButtons' ) ) {

    register_activation_hook( __FILE__, array( 'DeveloperShareButtons', 'activation_hook' ) );

    require_once dirname( __FILE__ ) . '/settings-class.php';
    require_once dirname( __FILE__ ) . '/developer-share-buttons-widget.php';

    class DeveloperShareButtons {

        private $settings_api;
        static $name      = 'Developer Share Buttons'; //Human-readable name of plugin
        static $slug      = 'dev-share-buttons';       //plugin slug, generally base filename and in url on wordpress.org
        static $slug_     = 'dev_share_buttons';       //slug with underscores (PHP/JS safe)
        public $version   = '1.0.0';

        function __construct() {
            $this->settings_api = new WeDevs_Settings_API;

            add_action( 'admin_init', array( $this, 'admin_init' ) );
            add_action( 'admin_menu', array( $this, 'admin_menu' ) );

            add_shortcode( static::$slug_, array( $this, 'shortcode' ) );
        }

        /**
         * Just saves the default option values
         * @return void
         */
        function activation_hook() {
            $defaults = array(
                'default-services' => array(
                    'facebook' => 'facebook',
                    'twitter'  => 'twitter',
                    'google'   => 'google'
                ),
                'share-text' => 'Share On'
            );
            add_option( static::$slug_ . '_options', $defaults );
        }

        /**
         * Sets up the settings sections and fields
         * @return void
         */
        function admin_init() {
            //set the settings
            $this->settings_api->set_sections( $this->get_settings_sections() );
            $this->settings_api->set_fields( $this->get_settings_fields() );

            //initialize settings
            $this->settings_api->admin_init();
        }

        /**
         * Adds the option page
         * @return void
         */
        function admin_menu() {
            add_options_page( static::$name, static::$name, 'delete_posts', static::$slug, array($this, 'plugin_page') );
        }

        /**
         * Sets up the settings sections (tabs)
         * @return array The sections
         */
        function get_settings_sections() {
            $sections = array(
                array(
                    'id' => static::$slug_ . '_options',
                    'title' => __( 'Main Options', static::$slug )
                ),
                array(
                    'id' => static::$slug_ . '_urls',
                    'title' => __( 'Social Links', static::$slug )
                )
            );
            return $sections;
        }

        /**
         * Returns all the settings fields
         * @return array Settings fields
         */
        function get_settings_fields() {
            // Default stuff
            $settings_fields = array(
                static::$slug_ . '_options' => array(
                    array(
                        'name' => 'default-services',
                        'label' => __( 'Default Services', static::$slug ),
                        'desc' => __( 'The default services that are enabled when calling the function', static::$slug ),
                        'type' => 'multicheck',
                        'options' => array()
                    ),
                    array(
                        'name' => 'share-text',
                        'label' => __( 'Share Text', static::$slug ),
                        'desc' => __( 'The text that appears in the links', static::$slug ),
                        'type' => 'text'
                    )
                ),
                static::$slug_ . '_urls' => array()
            );

            // Loop through all services and add them as options
            foreach( static::get_services() as $service_id => $service ) {
                // Add the profile url field
                $settings_fields[static::$slug_ . '_urls'][] = array(
                    'name' => $service_id,
                    'label' => __( $service['title'], static::$slug ),
                    'desc' => __( 'Your ' . $service['title'] . ' profile link', static::$slug ),
                    'type' => 'text',
                    'default' => ''
                );

                // If service has a sharing url then make it an option to share with
                if ( $service['url_structure'] )
                    $settings_fields[static::$slug_ . '_options'][0]['options'][$service_id] = $service['title'];
            }

            return $settings_fields;
        }

        /**
         * Creates the settings page
         * @return void
         */
        function plugin_page() {
            echo '<div class="wrap">';

            $this->settings_api->show_navigation();
            $this->settings_api->show_forms();

            echo '</div>';
        }

        /**
         * The shortcode
         * @param  array $atts Shortcode attributes
         * @return string      Html for the share links
         */
        function shortcode( $atts ) {
            extract( shortcode_atts( array(
                'services' => false,
                'url' => false,
                'title' => false,
                'text' => false,
                'image' => false
                ), $atts ) );
            return static::get_buttons( $services );
        }

        /**
         * Returns all the supported services included ones added by filters
         * @return array Supported services
         */
        function get_services() {
            $defaults = array(  //array of services as key => array( title => human readable title, url_structure => share url structure )
                // %1$s = url to share
                // %2$s = share title
                // %3$s = share text
                // %4$s = share image
                'facebook'    => array( 'id' => 'facebook', 'title' => 'Facebook', 'url_structure' => 'http://www.facebook.com/sharer.php?u=%1$s', 'url_after_title' => false ),
                'twitter'     => array( 'id' => 'twitter', 'title' => 'Twitter', 'url_structure' => 'http://twitter.com/share?url=%1$s&text=%2$s', 'url_after_title' => true ),
                'google'      => array( 'id' => 'google','title' => 'Google+', 'url_structure' => 'https://plus.google.com/share?url=%1$s', 'url_after_title' => false ),
                'reddit'      => array( 'id' => 'reddit', 'title' => 'Reddit', 'url_structure' => 'http://reddit.com/submit?url=%1$s&title=%2$s', 'url_after_title' => false ),
                'linkedin'    => array( 'id' => 'linkedin', 'title' => 'LinkedIn', 'url_structure' => 'http://www.linkedin.com/shareArticle?mini=true&url=%1$s', 'url_after_title' => false ),
                'stumbleupon' => array( 'id' => 'stumbleupon', 'title' => 'StumbleUpon', 'url_structure' => 'http://www.stumbleupon.com/submit?url=%1$s&title=%2$s', 'url_after_title' => false ),
                'pinterest'   => array( 'id' => 'pinterest', 'title' => 'Pinterest', 'url_structure' => 'http://pinterest.com/pin/create/button/?url=%1$s&media=%4$s', 'url_after_title' => false ),
                'instagram'   => array( 'id' => 'instagram', 'title' => 'Instagram', 'url_structure' => false, 'url_after_title' => false ),
                'github'      => array( 'id' => 'github', 'title' => 'GitHub', 'url_structure' => false, 'url_after_title' => false )
            );

            $services = apply_filters( DeveloperShareButtons::$slug_ . '_services', $defaults );

            if ( is_array( $services ) ) {
                return $services;
            }
            else {
                return $defaults;
            }
        }

        /**
         * Gets the `a` tag for sharing service
         * @param  string $service Service id
         * @param  string $url     The url you wish to share, defaults to `get_permalink()`
         * @param  string $title   The title of the item to share, defaults to `get_the_title()`
         * @param  string $text    The text that will appear before the service title, defaults to the value set on the option page
         * @param  string $image   The url of an image to share (only used by some services), defaults to the post thumbnail url
         * @return string|bool     Return the created `a` tag or false if not found
         */
        public static function get_link_html( $service, $url = '', $title = '', $text = '', $image = '' ) {
            $services = static::get_services();
            if ( $service = $services[$service] ) {
                if ( $service['url_structure'] ) {
                    $options = get_option( static::$slug_ . '_options' );
                    $share_text = 'Share on ';
                    if ( isset( $options['share-text'] ) )
                        $share_text = trim( $options['share-text'] ) . ' ';

                    $css_class = static::$slug;

                    if ( !$title && !$url )
                        $title = get_the_title();

                    if ( !$url )
                        $url = get_permalink();

                    if ( $service['url_after_title'] && $title )
                        $title .= ' ' . $url;

                    $url = urlencode( $url );

                    if ( !$image && $image_id = get_post_thumbnail_id() ) {
                        $image_object = wp_get_attachment_image_src( $image_id, 'full' );
                        $image = $image_object[0];
                    }

                    $share_text = apply_filters( static::$slug_ . '_share_text', $share_text );

                    $url = sprintf( $service['url_structure'], $url, $title, $text, $image );
                    $html = sprintf( '<a target="_blank" href="%1$s" class="%2$s__item %2$s__item--%3$s"><span class="%2$s__text %2$s__text--%3$s">%4$s</span></a> ', $url, $css_class, $service['id'], $share_text . $service['title'] );

                    return $html;
                }
            } else {
                return false;
            }
        }

        /**
         * Gets the html for the share buttons
         * @param  array    $services Array of service ids to include, defaults to those set on the options page
         * @param  sting    $url      The url to pass to `get_link_html()`
         * @param  string   $title    The title to pass to `get_link_html()`
         * @param  string   $text     The text to pass to `get_link_html()`
         * @param  string   $image    The image url to pass to `get_link_html()`
         * @return string             Html `div` containing all the share links
         */
        public static function get_buttons ( $services = false, $url = '', $title = '', $text = '', $image = '' ) {
            if ( !is_array( $services ) || !$services ) {
                $options = get_option( static::$slug_ . '_options' );

                if ( isset( $options['default-services'] ) ) {
                    $services = $options['default-services'];
                } else {
                    return false;
                }
            }

            $html = '<div class="' . static::$slug . '">';

            ob_start();
            do_action( 'before_' . static::$slug_ );
            $html .= ob_get_clean();

            foreach ( $services as $service ) {
                if ( $service_html = static::get_link_html( $service, $url, $title, $text, $image ) ) {
                    $html .= $service_html;
                }
            }

            ob_start();
            do_action( 'after_' . static::$slug_ );
            $html .= ob_get_clean();

            $html .= '</div>';

            return $html;
        }

        /**
         * Wrapper to echo the output of `get_buttons()`
         * @return bool  Success or no?
         */
        public static function the_buttons ( $services = false, $url = false, $title = false, $text = false, $image = false ) {
            if ( $html = static::get_buttons( $services, $url, $title, $text, $image ) ) {
                echo $html;
                return true;
            } else {
                return false;
            }

        }

        /**
         * Gets an array of the saved profile urls
         * @return array Array of urls with url and title
         */
        public static function get_profile_links() {
            $options = get_option( static::$slug_ . '_urls' );

            $links = array();

            foreach ( static::get_services() as $service_id => $service ) {
                if ( isset( $options[$service_id] ) && $options[$service_id] ) {
                    $links[$service_id] = array(
                        'title' => $service['title'],
                        'url' => $options[$service_id],
                    );
                }
            }

            return $links;
        }

        /**
         * Echos all the saved profile urls in a nice html format
         * @return bool Success or no?
         */
        public static function the_profile_links() {
            if ( $links = static::get_profile_links() ) {
                $html = '<div class="' . static::$slug . '-profiles">';
                foreach ($links as $service_id => $service_link) {
                    $html .= '<a class="' . static::$slug . '-link ' . static::$slug . '-link--' . $service_id .'" href="' . $service_link['url'] . '"><span class="' . static::$slug . '-link__text ' . static::$slug . '-link--' . $service_id .'__text">' . $service_link['title'] . '</span></a>';
                }
                $html .= '</div>';
                echo $html;
                return true;
            } else {
                return false;
            }
        }

        /**
         * Registers the widget
         * @return void
         */
        public static function widget() {
            register_widget( 'Dev_Share_Buttons_Widget' );
            register_widget( 'Dev_Share_Buttons_Profiles_Widget' );
        }

    }

    // Get the show on the road
    $dev_share_buttons = new DeveloperShareButtons();

    // Set up some wrapper functions for easier access
    if ( !function_exists( 'get_dev_share_buttons' ) ) {
        function get_dev_share_buttons( $services = false, $url = '', $title = '', $text = '', $image = '' ) {
            return DeveloperShareButtons::get_buttons( $services, $url, $title, $text, $image );
        }
    }

    if ( !function_exists( 'the_dev_share_buttons' ) ) {
        function the_dev_share_buttons( $services = false, $url = '', $title = '', $text = '', $image = '' ) {
            return DeveloperShareButtons::the_buttons( $services, $url, $title, $text, $image );
        }
    }

    if ( !function_exists( 'get_dev_share_links' ) ) {
        function get_dev_profile_links() {
            return DeveloperShareButtons::get_profile_links();
        }
    }

    if ( !function_exists( 'the_dev_share_links' ) ) {
        function the_dev_profile_links() {
            return DeveloperShareButtons::the_profile_links();
        }
    }
}