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

require_once dirname( __FILE__ ) . '/settings-class.php';
require_once dirname( __FILE__ ) . '/developer-share-buttons-widget.php';

class DeveloperShareButtons {

  private $settings_api;
  static $name      = 'Developer Share Buttons'; //Human-readable name of plugin
  static $slug      = 'dev-share-buttons';       //plugin slug, generally base filename and in url on wordpress.org
  static $slug_     = 'dev_share_buttons';       //slug with underscores (PHP/JS safe)
  public $version   = '1.0.0';
  static $services = array(  //array of services as key => array( title => human readable title, url_structure => share url structure )
      // %1$s = url to share
      // %2$s = share title
      // %3$s = share text
      // %4$s = share image
      'facebook'    => array( 'title' => 'Facebook', 'url_structure' => 'http://www.facebook.com/sharer.php?u=%1$s', 'url_after_title' => false ),
      'twitter'     => array( 'title' => 'Twitter', 'url_structure' => 'http://twitter.com/share?url=%1$s&text=%2$s', 'url_after_title' => true ),
      'google'      => array( 'title' => 'Google+', 'url_structure' => 'https://plus.google.com/share?url=%1$s', 'url_after_title' => false ),
      'reddit'      => array( 'title' => 'Reddit', 'url_structure' => 'http://reddit.com/submit?url=%1$s&title=%2$s', 'url_after_title' => false ),
      'linkedin'    => array( 'title' => 'LinkedIn', 'url_structure' => 'http://www.linkedin.com/shareArticle?mini=true&url=%1$s', 'url_after_title' => false ),
      'stumbleupon' => array( 'title' => 'StumbleUpon', 'url_structure' => 'http://www.stumbleupon.com/submit?url=%1$s&title=%2$s', 'url_after_title' => false ),
      'pinterest'   => array( 'title' => 'Pinterest', 'url_structure' => 'http://pinterest.com/pin/create/button/?url=%1$s&media=%4$s', 'url_after_title' => false ),
  );

  function __construct() {
    $this->settings_api = new WeDevs_Settings_API;

    add_action( 'admin_init', array( $this, 'admin_init' ) );
    add_action( 'admin_menu', array( $this, 'admin_menu' ) );

    add_shortcode( static::$slug_, array( $this, 'shortcode' ) );
  }

  function admin_init() {
      //set the settings
      $this->settings_api->set_sections( $this->get_settings_sections() );
      $this->settings_api->set_fields( $this->get_settings_fields() );

      //initialize settings
      $this->settings_api->admin_init();
  }

  function admin_menu() {
    add_options_page( static::$name, static::$name, 'delete_posts', static::$slug, array($this, 'plugin_page') );
  }

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
   *
   * @return array settings fields
   */
  function get_settings_fields() {
      $settings_fields = array(
        static::$slug_ . '_options' => array(
          array(
            'name' => 'default-services',
            'label' => __( 'Default Services', static::$slug ),
            'desc' => __( 'The default services that are enabled when calling the function', static::$slug ),
            'type' => 'multicheck',
            'default' => array( 'facebook' => 'facebook', 'twitter' => 'twitter', 'google' => 'google' ),
            'options' => array()
          ),
          array(
            'name' => 'share-text',
            'label' => __( 'Share Text', static::$slug ),
            'desc' => __( 'The text that appears in the links', static::$slug ),
            'type' => 'text',
            'default' => 'Share on'
          )
        ),
        static::$slug_ . '_urls' => array()
      );

      foreach( static::$services as $service_id => $service ) {
        $settings_fields[static::$slug_ . '_urls'][] = array(
          'name' => $service_id,
          'label' => __( $service['title'], static::$slug ),
          'desc' => __( 'Your ' . $service['title'] . ' profile link', static::$slug ),
          'type' => 'text',
          'default' => ''
        );
        $settings_fields[static::$slug_ . '_options'][0]['options'][$service_id] = $service['title'];
      }

      return $settings_fields;
  }

  function plugin_page() {
      echo '<div class="wrap">';

      $this->settings_api->show_navigation();
      $this->settings_api->show_forms();

      echo '</div>';
  }

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


  public static function get_link_html( $service, $url = false, $title = false, $text = false, $image = false ) {
    if ( $service = static::$services[$service] ) {
      $options = get_option( static::$slug_ . '_options' );
      $share_text = 'Share on ';
      if ( isset( $options['share-text'] ) ) {
          $share_text = trim( $options['share-text'] ) . ' ';
      }

      $css_class = static::$slug;

      if ( !$title && !$url )
        $title = get_the_title();

      if ( !$url )
        $url = get_permalink();

      if ( $service['url_after_title'] && $title )
        $title .= ' ' . $url;

      $url = urlencode( $url );

      $share_text = apply_filters( static::$slug_ . '_share_text', $share_text );

      $url = sprintf( $service['url_structure'], $url, $title, $text, $image );
      $html = sprintf( '<a target="_blank" href="%1$s" class="%2$s %2$s--%3$s"><span class="%2$s__text %2$s--%3$s__text">%4$s</span></a> ', $url, $css_class, key( $service ), $share_text . $service['title'] );

      return $html;

    } else {
      return false;
    }

  }

  public static function get_buttons ( $services = false, $url = false, $title = false, $text = false, $image = false ) {
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
      if ( $service_html = static::get_link_html( $service, $url ) ) {
        $html .= $service_html;
      }
    }

    ob_start();
    do_action( 'after_' . static::$slug_ );
    $html .= ob_get_clean();

    $html .= '</div>';

    return $html;
  }

  public static function the_buttons ( $services = false, $url = false, $title = false, $text = false, $image = false ) {
    echo static::get_buttons( $services, $url, $title, $text, $image );
  }

  public static function get_profile_links() {
    $options = get_option( static::$slug_ . '_urls' );

    $links = array();

    foreach ( static::$services as $service_id => $service ) {
      if ( $options[$service_id] ) {
        $links[$service_id] = array(
          'title' => $service['title'],
          'url' => $options[$service_id],
        );
      }
    }

    return $links;
  }

  public static function the_profile_links() {
    $links = static::get_profile_links();
    $html = '<div class="' . static::$slug . '-profiles">';
    foreach ($links as $service_id => $service_link) {
      $html .= '<a class="' . static::$slug . '-link ' . static::$slug . '-link--' . $service_id .'" href="' . $service_link['url'] . '">' . $service_link['title'] . '</a>';
    }
    $html .= '</div>';
  }

  public static function widget() {
      register_widget( 'Dev_Share_Buttons_Widget' );
  }

}

$dev_share_buttons = new DeveloperShareButtons();

if ( !function_exists( 'get_dev_share_buttons' ) ) {
  function get_dev_share_buttons( $services = false, $url = false, $title = false, $text = false, $image = false ) {
    DeveloperShareButtons::get_buttons( $services, $url, $title, $text, $image );
  }
}

if ( !function_exists( 'the_dev_share_buttons' ) ) {
  function the_dev_share_buttons( $services = false, $url = false, $title = false, $text = false, $image = false ) {
    DeveloperShareButtons::the_buttons( $services, $url, $title, $text, $image );
  }
}

if ( !function_exists( 'get_dev_share_links' ) ) {
  function get_dev_share_links( $services = false, $url = false, $title = false, $text = false, $image = false ) {
    DeveloperShareButtons::get_profile_links();
  }
}

if ( !function_exists( 'the_dev_share_links' ) ) {
  function the_dev_share_links( $services = false, $url = false, $title = false, $text = false, $image = false ) {
    DeveloperShareButtons::the_profile_links();
  }
}