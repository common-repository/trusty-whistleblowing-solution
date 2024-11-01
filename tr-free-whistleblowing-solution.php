<?php

/**
 * The plugin bootstrap file
 *
 *
 * @link              https://www.trusty.report
 * @since             1.0.0
 * @package           tr_free_whistleblowing_solution
 *
 * @wordpress-plugin
 * Plugin Name:       Trusty Whistleblowing Solution
 * Plugin URI:        https://www.trusty.report
 * Description:       Trusty is a free, web-based whistleblowing solution, helping SMEs comply with the EU Whistleblower Protection Directive. It is installed on the virtual server in Germany provided by Hetzner Online GmbH. The solution consists of the front-end in multiple languages and the case management tool, which is intuitive and simple to use.
 * Version:           1.5.2
 * Author:            Trusty AG
 * Author URI:        https://www.trusty.report
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tr_free_whistleblowing_solution
 * Domain Path:       /languages
 */

/**
 * If this file is called directly, abort.
 */
if ( ! defined( 'WPINC' ) ) {
    die;
}

define("TRPRODUCTION", true);
define("TRGENERATEDATA", false);
if(!TRPRODUCTION) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

/**
 *  For Settings Link in Plugin Listing
 */
function trusty_plugin_settings_link( $links ) { 
  $settings_link = '<a href="admin.php?page=trusty-form">Settings</a>'; 
  array_unshift( $links, $settings_link ); 
  return $links; 
}
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'trusty_plugin_settings_link' );


/**
 * Current plugin version.
 */
define( 'TWBS_FREE_WHISTLEBLOWING_SOLUTIION_VERSION', '1.0.0' );
define( 'TWBS_PLUGIN_URL', esc_url( plugins_url( "/", __FILE__ ) ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_tr_free_whistleblowing_solution() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-tr-free-whistleblowing-solution-activator.php';
    Tr_Free_Whistleblowing_Solution_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_tr_free_whistleblowing_solution() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-tr-free-whistleblowing-solution-deactivator.php';
    Tr_Free_Whistleblowing_Solution_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_tr_free_whistleblowing_solution' );
register_deactivation_hook( __FILE__, 'deactivate_tr_free_whistleblowing_solution' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tr-free-whistleblowing-solution.php';

function trWriteLog( $message ) {
    if(!TRPRODUCTION) {
        if ( is_array( $message ) || is_object( $message ) ) {
            $message = print_r( $message, true );
        }
        file_put_contents(plugin_dir_path( __FILE__ ).'/logs/debug.log', date("Y-m-d H:i:s").':'.$message.PHP_EOL, FILE_APPEND | LOCK_EX);    
    }
}

function generateData() {
    // retrieve data from https://trusty.report/signup-business/
    // and update constants with them
    // write multiple files
    $curl = curl_init();

    // Set cURL options
    curl_setopt($curl, CURLOPT_URL, "https://trusty.report/signup-business/");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_HEADER, false);

    // Execute cURL request
    $content = curl_exec($curl);

    // Close cURL session
    curl_close($curl);

    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Suppress warnings

    // Load HTML content into DOMDocument
    $dom->loadHTML($content);

    $selectValuesFn = function($dom, string $id) {
        $options = $dom->getElementById($id)->getElementsByTagName('option');
        $values = array();
        foreach($options as $option) {
            if(!empty($option->textContent)) {
                $values[$option->getAttribute('value')] = $option->textContent;
            }
        }
        return $values;
    };

    // load Values
    $selectIds = [
        'trCountryList' => 'form-field-country',
        'trPhoneCodeList' => 'form-field-phone_code',
        'trRoleList' => 'form-field-role',
        'trSizeList' => 'form-field-field_a432d1b',
        'trLanguageList' => 'form-field-account_language',
    ];

    $selectValues = [];

    $content = "<?php".PHP_EOL;
    $content .= "const trTermsLink = 'https://trusty.report/wp-content/uploads/2023/04/Trusty-Terms-of-Service-Bilingual-14042023.pdf';".PHP_EOL;
    $content .= "const trPolicyLink = 'https://trusty.report/privacy-policy/';".PHP_EOL.PHP_EOL;

    foreach($selectIds as $key => $value) {
        $selectValues[$key] = $selectValuesFn($dom, $value);
        $content .= "const ".$key." = [".PHP_EOL;
        foreach($selectValues[$key] as $vKey => $vValue) {
            $content .= "\t"."'".addslashes($vKey)."' => '".addslashes($vValue)."',".PHP_EOL;
        }
        $content = rtrim($content, ",");
        $content .= "];".PHP_EOL.PHP_EOL;
    }

    // custom country code for libphonenumber
    $content .= "const "."trPhoneCodeListCountry"." = [".PHP_EOL;
    foreach($selectValues["trPhoneCodeList"] as $vKey => $vValue) {
        $phoneCountry = trim(preg_replace("/\(.+\)/",'',$vValue));
        $isoCountry = array_search($phoneCountry, $selectValues["trCountryList"]);
        if($isoCountry) {
            $content .= "\t"."'".addslashes($isoCountry)."' => '".addslashes($vValue)."',".PHP_EOL;
        }
    }
    $content = rtrim($content, ",");
    $content .= "];".PHP_EOL.PHP_EOL;

    file_put_contents(plugin_dir_path( __FILE__ ).'/config/constants.php', $content);    
}
if(!TRPRODUCTION && TRGENERATEDATA) {
    generateData();
}


/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_tr_free_whistleblowing_solution() {

    $plugin = new Tr_Free_Whistleblowing_Solution();
   
    $plugin->run();

}

 

run_tr_free_whistleblowing_solution();


?>