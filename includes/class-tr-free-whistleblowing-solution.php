<?php
require_once plugin_dir_path( __FILE__ ) . '../config/formConstants.php';


/**
 * The file that defines the core plugin class
 *
 * @link       http://trusty.com
 * @since      1.0.0
 *
 * @package    tr-free-whistleblowing-solution
 * @subpackage tr-free-whistleblowing-solution/includes
 */

/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    tr-free-whistleblowing-solution
 * @subpackage tr-free-whistleblowing-solution/includes
 * @author     Trusty <email@trusty.com>
 */
class Tr_Free_Whistleblowing_Solution {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Tr_Free_Whistleblowing_Solution_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $tr_free_whistleblowing_solution    The string used to uniquely identify this plugin.
	 */
	protected $tr_free_whistleblowing_solution;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'TR_FREE_WHISTLEBLLOWING_SOLUTION_VERSION' ) ) {
			$this->version = TR_FREE_WHISTLEBLOWING_SOLUTION_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->tr_free_whistleblowing_solution = 'tr-free-whistleblowing-solution';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Tr_Free_Whistleblowing_Solution_Loader. Orchestrates the hooks of the plugin.
	 * - Tr_Free_Whistleblowing_Solution_i18n. Defines internationalization functionality.
	 * - Tr_Free_Whistleblowing_Solution_Admin. Defines all hooks for the admin area.
	 * - Tr_Free_Whistleblowing_Solution_Public. Defines all hooks for the public side of the site.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tr-free-whistleblowing-solution-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-tr-free-whistleblowing-solution-public.php';

		$this->loader = new Tr_Free_Whistleblowing_Solution_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		//$plugin_i18n = new Tr_Free_Whistleblowing_Solution_i18n();

		//$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_public = new Tr_Free_Whistleblowing_Solution_Public( $this->get_tr_free_whistleblowing_solution(), $this->get_version() );

	    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        /**
         * Hooks to add options menu
         */
		$this->loader->add_action( 'admin_menu', $this, 'tr_free_whistleblowing_solution_menu' );
		$this->loader->add_action( 'admin_init', $this, 'register_free_wbs_plugin_settings' );
		
		
        /**
         * Ajax functions
         */
        add_action( 'wp_ajax_nopriv_post_form_info', array( $this , 'post_form_info' ) );
        add_action( 'wp_ajax_nopriv_status_check', array( $this , 'status_check' ) );
        add_action( 'wp_ajax_post_form_info', array( $this , 'post_form_info' ) );
        add_action( 'wp_ajax_status_check', array( $this , 'status_check' ) );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_tr_free_whistleblowing_solution() {
		return $this->tr_free_whistleblowing_solution;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Check the status to show conditional strings
	 */
	function status_check(){
	    $myArray = json_decode( wp_unslash( $_POST['formData'] ) );
	    $request_id = '';
		foreach( $myArray as $arr ){
			if( sanitize_text_field( $arr->name ) == "request_id" ){ 
				$request_id = sanitize_text_field( $arr->value );
			}
		}
		trWriteLog('Status check on endpoint: :'.$this->get_endpoint( 'status' ) . '?request_id=' . $request_id);
        $response = wp_remote_post(
            $this->get_endpoint( 'status' ) . '?request_id=' . $request_id, array(
                'body' => [],
            )
        );
        
        $response_body = wp_remote_retrieve_body( $response );
		trWriteLog('Status check response');
		trWriteLog($response_body);
        
        if( json_encode( $response_body, true ) == null ){
            var_dump( $response_body );
        } else {
            $response_body = json_decode( $response_body, true );
        
            isset( $response_body['domain'] ) ? update_option( trTrustyUrl, 'https://' . sanitize_text_field( $response_body['domain'] ) ) : null ;
            isset( $response_body['email'] ) ? update_option( trEmail, sanitize_email( $response_body['email'] ) ) : null ;
            isset( $response_body['request_id'] ) ? update_option( trRegistrationKey, sanitize_text_field( $response_body['request_id'] ) ) : null ;
			//isset( $response_body['magic_link'] ) ? update_option( trMagicLink, sanitize_text_field( $response_body['magic_link'] ) ) : null ;
			// build magic link
			if( isset( $response_body['domain'] ) && isset( $response_body['request_id'] ) ) {
				$magic_link = 'https://' . sanitize_text_field( $response_body['domain'] ).'/crm/set-password?request_id='.sanitize_text_field( $response_body['request_id']);
				update_option( trMagicLink, $magic_link );
				$response_body['magic_link'] = $magic_link;
			}
			
            echo json_encode( $response_body, true );
        }
	    exit;
    }

    /**
     * Set options to match with available keys
     */
	public static function set_trusty_option( $source, $schema ) {
		if( !empty( $source ) && !empty( $schema ) ){
		    foreach( $schema as $src_key => $src_value ){
		        if( isset( $source[$src_key] ) ){
                    update_option ( $src_value, trim( $source[$src_key] ) );
		        }
		    }
        }
	}

    /**
     * Set form information with available keys
     */
    public function post_form_info(){
        $myArray = json_decode( wp_unslash( sanitize_text_field( $_POST['formData'] ) ) );
		$sanitizedField = array();
		foreach( $myArray as $arr ){
			if(in_array($arr->name, formInputKeys)) {
				$sanitizedField[$arr->name] = sanitize_text_field( $arr->value ); 
			}
		}

        $myArray = array(
		    'name'              =>  $sanitizedField[trName],
		    'email'             =>  $sanitizedField[trEmail],
		    'organization'      =>  $sanitizedField[trOrganizazion],
		    'website'           =>  $sanitizedField[trWebsite],
		    'city'              =>  $sanitizedField[trCity],
		    'country'           =>  $sanitizedField[trCountry],
		    'reference_number'  =>  $sanitizedField[trName],
		    'terms'             =>  $sanitizedField[trTerms],
		    'source_url'        =>  $sanitizedField[trName],
		    'language'          =>  $sanitizedField[trLanguage],
		    'api_key'           =>  'HoQoFen6pBpKesm',
			'last_name'			=>  $sanitizedField[trLastName],
			'size'				=>  $sanitizedField[trSize],
			'add_communication' =>  isset($sanitizedField[trOptional]) ? $sanitizedField[trOptional] : '0',
			'phone_code'		=> 	$sanitizedField[trCountryCode],
			'role'				=>	$sanitizedField[trRole],
			'phone'				=> 	$sanitizedField[trPhoneNumber],
        );

		trWriteLog('post on endpoint '.$this->get_endpoint( 'user' ));
		trWriteLog(json_encode( $myArray ));
		
	    $response = wp_remote_post( $this->get_endpoint( 'user' ), [
                'headers'       => ['Content-Type' => 'application/json'],
                'body'          => json_encode( $myArray ),
                'data_format'   => 'body',
            ]
        );

        $response_body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if( !empty( $response_body ) ){
            $tr_keys = array(
                'url' => 'tr_domain_id'
            );
        }
		trWriteLog('post response');
        trWriteLog($response_body);
        echo json_encode($response_body);
		die();
    }

    /**
     * Include Trusty form
     */
	public function tr_free_wbs_plugin_settings_page() {
		include( __DIR__.'/../templates/form.tpl.php' );
	}

	/**
	 * Add an Admin menu to show the form or show details.
	 */
	public function tr_free_whistleblowing_solution_menu() {
    	add_menu_page( 'Trusty Free Whistleblowing Solution Form', 'Trusty WBS Form', 'manage_options', 'trusty-form', array( $this,'tr_free_wbs_plugin_settings_page' ), plugins_url( "tr-free-whistleblowing-solution/public/img/WBS.png") );
	}

	/**
	 * Register required plugin's settings.
	 */
	public function register_free_wbs_plugin_settings() {
		foreach(formInputKeys as $key) {
			register_setting( 'tr-free-wbs-plugin-settings-group', $key );
		}

		foreach(formHiddenInputKeys as $key) {
			register_setting( 'tr-free-wbs-plugin-settings-group', $key );
		}

		register_setting( 'tr-free-wbs-plugin-settings-group', trUsername );
		register_setting( 'tr-free-wbs-plugin-settings-group', trPassword );
		register_setting( 'tr-free-wbs-plugin-settings-group', trTrustyUrl );
		register_setting( 'tr-free-wbs-plugin-settings-group', trMagicLink );
	}

	/**
	 * Get the endpoint to submit data.
	 */
    public function get_endpoint( $method ){
        if( $_SERVER['HTTP_HOST'] === 'wordpress.test' || !TRPRODUCTION ){
            return 'https://admin.trustystaging.online/api/' . $method ;
        }
        return 'https://admin.trusty.report/api/' . $method .'/';
    }

}

?>