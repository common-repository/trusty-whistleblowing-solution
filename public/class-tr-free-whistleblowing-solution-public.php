<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://trusty.com
 * @since      1.0.0
 *
 * @package    Tr_Free_Whistleblowing_Solution
 * @subpackage Tr_Free_Whistleblowing_Solution/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Tr_Free_Whistleblowing_Solution
 * @subpackage Tr_Free_Whistleblowing_Solution/public
 * @author     Trusty <email@trusty.com>
 */
class Tr_Free_Whistleblowing_Solution_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $tr_free_whistleblowing_solution    The ID of this plugin.
	 */
	private $tr_free_whistleblowing_solution;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $tr_free_whistleblowing_solution       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $tr_free_whistleblowing_solution, $version ) {

		$this->tr_free_whistleblowing_solution = $tr_free_whistleblowing_solution;
		$this->version = $version;
		
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->tr_free_whistleblowing_solution, plugin_dir_url( __FILE__ ) . 'css/tr-free-whistleblowing-solution-public.css', array(), '1.' . time(), 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'tr_mail_checker', plugin_dir_url( __FILE__ ) . 'js/lib/MailChecker/MailChecker.js');
		wp_enqueue_script( 'tr_libphonenumber', plugin_dir_url( __FILE__ ) . 'js/lib/libphonenumber/libphonenumber-js.min.js');

		wp_enqueue_script( $this->tr_free_whistleblowing_solution, plugin_dir_url( __FILE__ ) . 'js/tr-free-whistleblowing-solution-public.js', array( 'jquery' ), '1.' . time(), false );
    
	}


}

?>