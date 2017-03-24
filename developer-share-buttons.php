<?php
/**
 * The Developer Share Buttons WordPress plugin
 *
 * @package dev-share-buttons
 * /

/*
Plugin Name: Developer Share Buttons
Description: Share buttons with no CSS and no JavaScript
Version: 1.2.0
Author: Grant Richmond
Author URI: https://grant.codes/
License: GPL3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: dev-share-buttons
 */

if ( ! class_exists( 'DeveloperShareButtons' ) ) {

	register_activation_hook( __FILE__, array( 'DeveloperShareButtons', 'activation_hook' ) );

	require_once dirname( __FILE__ ) . '/settings-class.php';
	require_once dirname( __FILE__ ) . '/developer-share-buttons-widget.php';

	/**
	 * The main class.
	 */
	class DeveloperShareButtons {

		/**
		 * Stores the setting api object
		 *
		 * @var object
		 */
		private $settings_api;

		/**
		 * Human readable name of plugin
		 *
		 * @var string
		 */
		static $name = 'Developer Share Buttons';

		/**
		 * The plugin slug
		 *
		 * @var string
		 */
		static $slug = 'dev-share-buttons';

		/**
		 * The plugin slug using underscores
		 *
		 * @var string
		 */
		static $slug_ = 'dev_share_buttons';

		/**
		 * Whether or not to use rel="me" links
		 *
		 * @var null
		 */
		static $relme = null;

		/**
		 * The version of the plugin
		 *
		 * @var string
		 */
		public static $version = '1.2.0';

		/**
		 * Lets get this party started
		 */
		function __construct() {
			$this->settings_api = new WeDevs_Settings_API;

			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'share_api_script' ) );

			add_shortcode( static::$slug_, array( $this, 'shortcode' ) );
		}

		/**
		 * Just saves the default option values
		 *
		 * @return void
		 */
		public static function activation_hook() {
			$defaults = array(
				'default-services' => array(
					'facebook' => 'facebook',
					'twitter'  => 'twitter',
					'google'   => 'google',
				),
				'share-text' => 'Share On',
			);
			add_option( static::$slug_ . '_options', $defaults );
		}

		/**
		 * Sets up the settings sections and fields
		 *
		 * @return void
		 */
		function admin_init() {
			// Set the settings.
			$this->settings_api->set_sections( $this->get_settings_sections() );
			$this->settings_api->set_fields( $this->get_settings_fields() );

			// Initialize settings.
			$this->settings_api->admin_init();
		}

		/**
		 * Adds the option page
		 *
		 * @return void
		 */
		function admin_menu() {
			add_options_page( static::$name, static::$name, 'delete_posts', static::$slug, array( $this, 'plugin_page' ) );
		}

		/**
		 * Loads the default css file if it is enabled
		 *
		 * @return void
		 */
		function enqueue_style() {
			$load_css = false;
			if ( apply_filters( static::$slug_ . '_css', $load_css ) ) {
				wp_enqueue_style( static::$slug, plugin_dir_url( __FILE__ ) . 'style.css', false, static::$version );
			}
		}

		/**
		 * Enqueues the share api script
		 *
		 * @return void
		 */
		function share_api_script() {
			$share_api = false;
			if ( apply_filters( static::$slug_ . '_share_api', $share_api ) ) {
				wp_enqueue_script( static::$slug . '-share-api', plugin_dir_url( __FILE__ ) . 'share-api.js', false, static::$version, true );
			}
		}

		/**
		 * Sets up the settings sections (tabs)
		 *
		 * @return array The sections
		 */
		function get_settings_sections() {
			$sections = array(
				array(
					'id' => static::$slug_ . '_options',
					'title' => __( 'Main Options', static::$slug ),
				),
				array(
					'id' => static::$slug_ . '_urls',
					'title' => __( 'Social Links', static::$slug ),
				),
			);
			return $sections;
		}

		/**
		 * Returns all the settings fields
		 *
		 * @return array Settings fields
		 */
		function get_settings_fields() {
			// Default stuff.
			$settings_fields = array(
				static::$slug_ . '_options' => array(
					array(
						'name' => 'default-services',
						'label' => __( 'Default Services', static::$slug ),
						'desc' => __( 'The default services that are enabled when calling the function', static::$slug ),
						'type' => 'multicheck',
						'options' => array(),
					),
					array(
						'name' => 'share-text',
						'label' => __( 'Share Text', static::$slug ),
						'desc' => __( 'The text that appears in the links', static::$slug ),
						'type' => 'text',
					),
				),
				static::$slug_ . '_urls' => array(),
			);

			// Loop through all services and add them as options.
			foreach ( static::get_services() as $service_id => $service ) {
				// Add the profile url field.
				$settings_fields[ static::$slug_ . '_urls' ][] = array(
					'name' => $service_id,
					'label' => __( $service['title'], static::$slug ),
					'desc' => __( 'Your ' . $service['title'] . ' profile link', static::$slug ),
					'type' => 'url',
					'default' => '',
				);

				// If service has a sharing url then make it an option to share with.
				if ( $service['url_structure'] ) {
					$settings_fields[ static::$slug_ . '_options' ][0]['options'][ $service_id ] = $service['title'];
				}
			}

			// Add rel="me" option.
			$settings_fields[ static::$slug_ . '_urls' ][] = array(
				'name' => 'relme',
				'label' => __( 'Add rel="me" attributes' ),
				'type' => 'checkbox',
				'default' => false,
			);

			return $settings_fields;
		}

		/**
		 * Creates the settings page
		 *
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
		 *
		 * @param  array $atts Shortcode attributes.
		 * @return string      Html for the share links
		 */
		public static function shortcode( $atts ) {
			extract( shortcode_atts(
				array(
					'services' => false,
					'url' => false,
					'title' => false,
					'text' => false,
					'image' => false,
				),
				$atts
			) );
			return static::get_buttons( $services );
		}

		/**
		 * Returns all the supported services included ones added by filters
		 *
		 * @return array Supported services
		 */
		public static function get_services() {
			// Array of services as key => array( title => human readable title, url_structure => share url structure ).
			$defaults = array(
				// %1$s = url to share.
				// %2$s = share title.
				// %3$s = share text.
				// %4$s = share image.
				'facebook' => array(
					'id' => 'facebook',
					'title' => 'Facebook',
					'url_structure' => 'https://www.facebook.com/sharer.php?u=%1$s',
					'url_after_title' => false,
				),
				'twitter' => array(
					'id' => 'twitter',
					'title' => 'Twitter',
					'url_structure' => 'https://twitter.com/intent/tweet?url=%1$s&text=%2$s',
					'url_after_title' => false,
				),
				'google' => array(
					'id' => 'google',
					'title' => 'Google+',
					'url_structure' => 'https://plus.google.com/share?url=%1$s',
					'url_after_title' => false,
				),
				'reddit' => array(
					'id' => 'reddit',
					'title' => 'Reddit',
					'url_structure' => 'https://reddit.com/submit?url=%1$s&title=%2$s',
					'url_after_title' => false,
				),
				'linkedin' => array(
					'id' => 'linkedin',
					'title' => 'LinkedIn',
					'url_structure' => 'https://www.linkedin.com/shareArticle?mini=true&url=%1$s',
					'url_after_title' => false,
				),
				'stumbleupon' => array(
					'id' => 'stumbleupon',
					'title' => 'StumbleUpon',
					'url_structure' => 'https://www.stumbleupon.com/submit?url=%1$s&title=%2$s',
					'url_after_title' => false,
				),
				'pinterest' => array(
					'id' => 'pinterest',
					'title' => 'Pinterest',
					'url_structure' => 'https://www.pinterest.com/pin/find/?url=%1$s',
					'url_after_title' => false,
				),
				'instagram' => array(
					'id' => 'instagram',
					'title' => 'Instagram',
					'url_structure' => false,
					'url_after_title' => false,
				),
				'github' => array(
					'id' => 'github',
					'title' => 'GitHub',
					'url_structure' => false,
					'url_after_title' => false,
				),
				'medium' => array(
					'id' => 'medium',
					'title' => 'Medium',
					'url_structure' => false,
					'url_after_title' => false,
				),
				'messenger' => array(
					'id' => 'messenger',
					'title' => 'Messenger',
					'url_structure' => false,
					'url_after_title' => false,
				),
				'skype' => array(
					'id' => 'skype',
					'title' => 'Skype',
					'url_structure' => false,
					'url_after_title' => false,
				),
				'snapchat' => array(
					'id' => 'snapchat',
					'title' => 'Snapchat',
					'url_structure' => false,
					'url_after_title' => false,
				),
				'wechat' => array(
					'id' => 'wechat',
					'title' => 'WeChat',
					'url_structure' => false,
					'url_after_title' => false,
				),
				'whatsapp' => array(
					'id' => 'whatsapp',
					'title' => 'WhatsApp',
					'url_structure' => false,
					'url_after_title' => false,
				),
				'youtube' => array(
					'id' => 'youtube',
					'title' => 'YouTube',
					'url_structure' => false,
					'url_after_title' => false,
				),
			);

			$services = apply_filters( DeveloperShareButtons::$slug_ . '_services', $defaults );

			if ( is_array( $services ) ) {
				return $services;
			} else {
				return $defaults;
			}
		}

		/**
		 * Gets the `a` tag for sharing service
		 *
		 * @param  string $service Service id.
		 * @param  string $url     The url you wish to share, defaults to `get_permalink()`.
		 * @param  string $title   The title of the item to share, defaults to `get_the_title()`.
		 * @param  string $text    The text that will appear before the service title, defaults to the value set on the option page.
		 * @param  string $image   The url of an image to share (only used by some services), defaults to the post thumbnail url.
		 * @return string|bool     Return the created `a` tag or false if not found
		 */
		public static function get_link_html( $service, $url = '', $title = '', $text = '', $image = '' ) {
			$services = static::get_services();
			if ( $service = $services[ $service ] ) {
				if ( $service['url_structure'] ) {
					$options = get_option( static::$slug_ . '_options' );
					$share_text = 'Share on ';
					if ( isset( $options['share-text'] ) ) {
						$share_text = trim( $options['share-text'] ) . ' ';
					}

					$css_class = static::$slug;

					// If the page is not a proper post object fall back to defaults.
					if ( is_singular() && get_post() ) {
						if ( ! $title && ! $url ) {
							$title = get_the_title();
						}

						if ( ! $url ) {
							$url = get_permalink();
						}

						if ( ! $image && $image_id = get_post_thumbnail_id() ) {
							$image_object = wp_get_attachment_image_src( $image_id, 'full' );
							$image = $image_object[0];
						}
					} else if ( is_archive() ) {
						$title = get_the_archive_title();
						$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
					} else {
						$title = get_bloginfo( 'title' );
						$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
					}

					if ( $service['url_after_title'] && $title ) {
						$title .= ' ' . $url;
					}

					$url = rawurlencode( $url );
					$title = rawurlencode( $title );
					$text = rawurlencode( $text );
					$image = rawurlencode( $image );

					$share_text = apply_filters( static::$slug_ . '_share_text', $share_text );
					$after_text = apply_filters( static::$slug_ . '_after_share_text', '', $service );
					$before_text = apply_filters( static::$slug_ . '_before_share_text', '', $service );

					$url = sprintf( $service['url_structure'], $url, $title, $text, $image );
					$html = sprintf( '<a target="_blank" href="%1$s" class="%2$s__item %2$s__item--%3$s">%5$s<span class="%2$s__text %2$s__text--%3$s">%4$s</span>%6$s</a> ', $url, $css_class, $service['id'], $share_text . $service['title'], $before_text, $after_text );

					return $html;
				}
			} else {
				return false;
			}
		}

		/**
		 * Gets the html for the share buttons
		 *
		 * @param  array  $services Array of service ids to include, defaults to those set on the options page.
		 * @param  sting  $url      The url to pass to `get_link_html()`.
		 * @param  string $title    The title to pass to `get_link_html()`.
		 * @param  string $text     The text to pass to `get_link_html()`.
		 * @param  string $image    The image url to pass to `get_link_html()`.
		 * @return string           Html `div` containing all the share links
		 */
		public static function get_buttons( $services = false, $url = '', $title = '', $text = '', $image = '' ) {
			if ( ! is_array( $services ) || ! $services ) {
				$options = get_option( static::$slug_ . '_options' );

				if ( isset( $options['default-services'] ) ) {
					$services = $options['default-services'];
				} else {
					return false;
				}
			}

			$html = '<div class="' . static::$slug . '" data-share-title="' . esc_attr( $title ) . '" data-share-text="' . esc_attr( $text ) . '">';

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
		 *
		 * @param  array  $services Array of service ids to include, defaults to those set on the options page.
		 * @param  sting  $url      The url to pass to `get_link_html()`.
		 * @param  string $title    The title to pass to `get_link_html()`.
		 * @param  string $text     The text to pass to `get_link_html()`.
		 * @param  string $image    The image url to pass to `get_link_html()`.
		 * @return bool  Success or no?
		 */
		public static function the_buttons( $services = false, $url = false, $title = false, $text = false, $image = false ) {
			if ( $html = static::get_buttons( $services, $url, $title, $text, $image ) ) {
				echo $html;
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Gets an array of the saved profile urls
		 *
		 * @return array Array of urls with url and title
		 */
		public static function get_profile_links() {
			$options = get_option( static::$slug_ . '_urls' );

			$links = array();

			foreach ( static::get_services() as $service_id => $service ) {
				if ( isset( $options[ $service_id ] ) && $options[ $service_id ] ) {
					$links[ $service_id ] = array(
						'id' => $service_id,
						'title' => $service['title'],
						'url' => $options[ $service_id ],
					);
				}
			}

			return $links;
		}

		/**
		 * Echos all the saved profile urls in a nice html format
		 *
		 * @return bool Success or no?
		 */
		public static function the_profile_links() {
			if ( $links = static::get_profile_links() ) {
				$html = '<div class="' . static::$slug . '-profiles">';
				foreach ( $links as $service_id => $service_link ) {
					$attributes  = 'class="' . static::$slug . '-link ' . static::$slug . '-link--' . $service_id .'" ';
					$attributes .= 'href="' . $service_link['url'] . '" ';
					if ( static::is_rel_me() ) {
						$attributes .= 'rel="me" ';
					}
					$after_text = apply_filters( static::$slug_ . '_after_profile_text', '', $service_link );
					$before_text = apply_filters( static::$slug_ . '_before_profile_text', '', $service_link );
					$html .= '<a ' . $attributes . '>' . $after_text . '<span class="' . static::$slug . '-link__text ' . static::$slug . '-link--' . $service_id .'__text">' . $service_link['title'] . '</span>' . $before_text . '</a> ';
				}
				$html .= '</div>';
				echo $html;
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Checks to see if the rel="me" option is enabled
		 *
		 * @return boolean
		 */
		public static function is_rel_me() {
			if ( isset( static::$relme ) ) {
				return static::$relme;
			} else {
				$options = get_option( static::$slug_ . '_urls' );
				$relme = $options['relme'];
				if ( 'off' === $relme ) {
					$relme = false;
				} elseif ( 'on' === $relme ) {
					$relme = true;
				}
				static::$relme = $relme;
				return $relme;
			}
		}

		/**
		 * Registers the widget
		 *
		 * @return void
		 */
		public static function widget() {
			register_widget( 'Dev_Share_Buttons_Widget' );
			register_widget( 'Dev_Share_Buttons_Profiles_Widget' );
		}
	}

	// Get the show on the road.
	$dev_share_buttons = new DeveloperShareButtons();

	// Set up some wrapper functions for easier access.
	if ( ! function_exists( 'get_dev_share_buttons' ) ) {
		function get_dev_share_buttons( $services = false, $url = '', $title = '', $text = '', $image = '' ) {
			return DeveloperShareButtons::get_buttons( $services, $url, $title, $text, $image );
		}
	}

	if ( ! function_exists( 'the_dev_share_buttons' ) ) {
		function the_dev_share_buttons( $services = false, $url = '', $title = '', $text = '', $image = '' ) {
			DeveloperShareButtons::the_buttons( $services, $url, $title, $text, $image );
		}
	}

	if ( ! function_exists( 'get_dev_profile_links' ) ) {
		function get_dev_profile_links() {
			return DeveloperShareButtons::get_profile_links();
		}
	}

	if ( ! function_exists( 'the_dev_profile_links' ) ) {
		function the_dev_profile_links() {
			DeveloperShareButtons::the_profile_links();
		}
	}
}
