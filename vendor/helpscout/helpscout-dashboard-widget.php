<?php
/**
 * Copyright (c) 2015 WebDevStudios / Pluginize (email : contact@pluginize.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! function_exists( 'helpscout_customer_autoload_classes' ) ) {

	/**
	 * Autoloads files with classes when needed
	 *
	 * @since  0.1.0
	 * @param  string $class_name Name of the class being requested
	 * @return  null
	 */
	function helpscout_customer_autoload_classes( $class_name ) {
		if ( 0 !== strpos( $class_name, 'Helpscout_Customer' ) ) {
			return;
		}

		$filename = strtolower( str_ireplace(
			array( 'Helpscout_Customer_', '_' ),
			array( '', '-' ),
			$class_name
		) );

		Helpscout_Customer::include_file( $filename );
	}
	spl_autoload_register( 'helpscout_customer_autoload_classes' );


	/**
	 * Main initiation class
	 *
	 * @since  0.1.0
	 * @var  string $version  Plugin version
	 * @var  string $basename Plugin basename
	 * @var  string $url      Plugin URL
	 * @var  string $path     Plugin Path
	 */
	class Helpscout_Customer {

		/**
		 * Current version
		 *
		 * @var  string
		 * @since  0.1.0
		 */
		const VERSION = '0.1.0';

		/**
		 * URL of plugin directory
		 *
		 * @var string
		 * @since  0.1.0
		 */
		protected $url = '';

		/**
		 * Path of plugin directory
		 *
		 * @var string
		 * @since  0.1.0
		 */
		protected $path = '';

		/**
		 * Plugin basename
		 *
		 * @var string
		 * @since  0.1.0
		 */
		protected $basename = '';

		/**
		 * Singleton instance of plugin
		 *
		 * @var Helpscout_Scanner
		 * @since  0.1.0
		 */
		protected static $single_instance = null;

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @since  0.1.0
		 * @return helpscout_Scanner A single instance of this class.
		 */
		public static function get_instance() {
			if ( null === self::$single_instance ) {
				self::$single_instance = new self();
			}

			return self::$single_instance;
		}

		/**
		 * Sets up our plugin
		 *
		 * @since  0.1.0
		 */
		protected function __construct() {
			$this->basename = plugin_basename( __FILE__ );
			$this->url      = plugin_dir_url( __FILE__ );
			$this->path     = plugin_dir_path( __FILE__ );

			$this->plugin_classes();
			$this->hooks();
		}

		/**
		 * Attach other plugin classes to the base plugin class.
		 *
		 * @since 0.1.0
		 * @return  null
		 */
		protected function plugin_classes() {
			// Load only on admin side
			if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
				$this->dashboard = new Helpscout_Customer_Dashboard( $this );
			}
		}

		/**
		 * Add hooks and filters
		 *
		 * @since 0.1.0
		 * @return null
		 */
		public function hooks() {
			register_activation_hook( __FILE__, array( $this, '_activate' ) );
			register_deactivation_hook( __FILE__, array( $this, '_deactivate' ) );

			add_action( 'init', array( $this, 'init' ) );
		}

		/**
		 * Activate the plugin
		 *
		 * @since  0.1.0
		 * @return null
		 */
		function _activate() {
			// Make sure any rewrite functionality has been loaded
			flush_rewrite_rules();
		}

		/**
		 * Deactivate the plugin
		 * Uninstall routines should be in uninstall.php
		 *
		 * @since  0.1.0
		 * @return null
		 */
		function _deactivate() {}

		/**
		 * Init hooks
		 *
		 * @since  0.1.0
		 * @return null
		 */
		public function init() {
			if ( $this->check_requirements() ) {
				load_plugin_textdomain( 'helpscout', false, dirname( $this->basename ) . '/languages/' );
			}
		}

		/**
		 * Check that all plugin requirements are met
		 *
		 * @since  0.1.0
		 * @return boolean
		 */
		public static function meets_requirements() {
			// Do checks for required classes / functions
			// function_exists('') & class_exists('')

			// We have met all requirements
			return true;
		}

		/**
		 * Check if the plugin meets requirements and
		 * disable it if they are not present.
		 *
		 * @since  0.1.0
		 * @return boolean result of meets_requirements
		 */
		public function check_requirements() {
			if ( ! $this->meets_requirements() ) {
				// Display our error
				echo '<div id="message" class="error">';
				echo '<p>' . sprintf( __( 'Helpscout Scanner is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'helpscout' ), admin_url( 'plugins.php' ) ) . '</p>';
				echo '</div>';
				// Deactivate our plugin
				deactivate_plugins( $this->basename );

				return false;
			}

			return true;
		}

		/**
		 * Magic getter for our object.
		 *
		 * @since  0.1.0
		 * @param string $field
		 * @throws Exception Throws an exception if the field is invalid.
		 * @return mixed
		 */
		public function __get( $field ) {
			switch ( $field ) {
				case 'version':
					return self::VERSION;
				case 'basename':
				case 'url':
				case 'path':
					return $this->$field;
				default:
					throw new Exception( 'Invalid '. __CLASS__ .' property: ' . $field );
			}
		}

		/**
		 * Include a file from the includes directory
		 *
		 * @since  0.1.0
		 * @param  string  $filename Name of the file to be included
		 * @return bool    Result of include call.
		 */
		public static function include_file( $filename ) {
			$file = self::dir( 'includes/'. $filename .'.php' );

			if ( file_exists( $file ) ) {
				return include_once( $file );
			}
			return false;
		}

		/**
		 * Include a file from the includes directory
		 *
		 * @since  0.1.0
		 * @param  string  $filename Name of the file to be included
		 * @return bool    Result of include call.
		 */
		public static function include_vendor( $filename ) {
			$file = self::dir( 'vendor/'. $filename .'.php' );

			if ( file_exists( $file ) ) {
				return include_once( $file );
			}

			return false;
		}

		/**
		 * Include a file from the includes directory
		 *
		 * @since  0.1.0
		 * @param  string  $filename Name of the file to be included
		 * @return bool    Result of include call.
		 */
		public static function include_view( $filename ) {
			$file = self::dir( 'views/'. $filename .'.php' );

			if ( file_exists( $file ) ) {
				return include( $file );
			}

			return false;
		}

		/**
		 * This plugin's directory
		 *
		 * @since  0.1.0
		 * @param  string $path (optional) appended path
		 * @return string       Directory and path
		 */
		public static function dir( $path = '' ) {
			static $dir;
			$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
			return $dir . $path;
		}

		/**
		 * This plugin's url
		 *
		 * @since  0.1.0
		 * @param  string $path (optional) appended path
		 * @return string       URL and path
		 */
		public static function url( $path = '' ) {
			static $url;
			$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
			return $url . $path;
		}

		/**
		 * Return plugin version
		 */
		public function version() {
			return self::VERSION;
		}
	}

	/**
	 * Grab the Helpscout_Customer object and return it.
	 * Wrapper for Helpscout_Customer::get_instance()
	 *
	 * @since  0.1.0
	 * @return Helpscout_Customer  Singleton instance of plugin class.
	 */
	function helpscout_customer() {
		return Helpscout_Customer::get_instance();
	}

	// Kick it off
	helpscout_customer();

}
