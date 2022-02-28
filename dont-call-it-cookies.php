<?php
/**
 * Don't Call It Cookies
 *
 * @package     dont-call-it-cookies
 * @author      Vítor Manfredini
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Don't Call It Cookies
 * Plugin URI:  https://placeholder.com
 * Description: This plugin changes the term "Cookies" for "Data Collectors" in cookie consent screens/popups.
 * Version:     1.0.0
 * Author:      Vítor Manfredini
 * Author URI:  https://vitormanfredini.com
 * Text Domain: dont-call-it-cookies
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
* Check dependencies
*/
function dcic_check_dependencies() {

    if ( is_admin() && current_user_can( 'activate_plugins' ) ){

        //look for at least one of these plugins
        $arrDependenciesOr = array(
            'cookie-law-info/cookie-law-info.php',
            'gdpr-cookie-compliance/moove-gdpr.php',
            'complianz-gdpr/complianz-gpdr.php'
        );

        $countDependenciesInstalled = 0;
        foreach($arrDependenciesOr as $pluginMainFile){
            if(is_plugin_active($pluginMainFile)){
                $countDependenciesInstalled++;
            }
        }

        // if none are installed
        if ($countDependenciesInstalled == 0) {
            //show notice
            add_action('admin_notices', 'dcic_dependencies_notice');
            //deactive plugin if activated
            deactivate_plugins(plugin_basename( __FILE__ ));
            //do not let it be activated
            if ( isset( $_GET['activate'] ) ) {
                unset( $_GET['activate'] );
            }
        }
    }
}

/**
* Outputs dependencies notice HTML
*/
function dcic_dependencies_notice(){
    ?><div class="error">
        <h1>Don't Call It Cookies</h1>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        <p>This plugin needs to be used alongside with one of these:</p>
        <ul>
            <li><a target="_blank" href="<?php echo get_admin_url(); ?>plugin-install.php?tab=plugin-information&plugin=cookie-law-info">CookieYes | GDPR Cookie Consent & Compliance Notice (CCPA Ready)</a></li>
            <li><a target="_blank" href="<?php echo get_admin_url(); ?>plugin-install.php?tab=plugin-information&plugin=gdpr-cookie-compliance">GDPR Cookie Compliance (CCPA ready)</a></li>
            <li><a target="_blank" href="<?php echo get_admin_url(); ?>plugin-install.php?tab=plugin-information&plugin=complianz-gdpr">Complianz – GDPR/CCPA Cookie Consent</a></li>
        </ul>
    </div><?php
}

/**
* Starting point of the plugin
*/
function dcic_run(){
    //public pages only
    if(is_admin()) return;

    //cookie-law-info
    if(defined('CLI_SETTINGS_FIELD')){
        add_filter('option_'.CLI_SETTINGS_FIELD, 'dcic_filter_cookie_law_info');
        add_filter('option_cookielawinfo_privacy_overview_content_settings', 'dcic_filter_cookie_law_info');
    }

    //gdpr-cookie-compliance
    if(defined('MOOVE_GDPR_VERSION')){
        //make all output go through first through a callback function
        add_action('wp_head', function(){
            ob_start("dcic_filter_gdpr_cookie_compliance");
        });
        add_action('wp_footer', function(){
            ob_end_flush();
        },999999);
    }

    //complianz-gdpr
    if(defined('cmplz_version')){
        //make all output go through first through a callback function
        add_action('wp_head', function(){
            ob_start("dcic_filter_complianz_gdpr");
        });
        add_action('wp_footer', function(){
            ob_end_flush();
        },999999);
    }

}

/**
* Filters whole HTML to change plugin complianz-gdpr's output
*/
function dcic_filter_complianz_gdpr($buffer){

    $marker1 = 'id="cmplz-message-1-optin"';
    $marker2 = '</div>';
    $arrParts = explode($marker1,$buffer);
    if(isset($arrParts[1])){
        $arrParts2 = explode($marker2,$arrParts[1]);
        if(isset($arrParts2[1])){
            $arrParts2[0] = dcic_filter_string($arrParts2[0]);
            $arrParts[1] = implode($arrParts2,$marker2);
            $buffer = implode($arrParts,$marker1);
        }
    }

    $marker1 = 'id="cmplz-header-1-optin"';
    $marker2 = '</div>';
    $arrParts = explode($marker1,$buffer);
    if(isset($arrParts[1])){
        $arrParts2 = explode($marker2,$arrParts[1]);
        if(isset($arrParts2[1])){
            $arrParts2[0] = dcic_filter_string($arrParts2[0]);
            $arrParts[1] = implode($arrParts2,$marker2);
            $buffer = implode($arrParts,$marker1);
        }
    }

    return $buffer;

}

/**
* Filters whole HTML to change plugin gdpr-cookie-compliance's output
*/
function dcic_filter_gdpr_cookie_compliance($buffer) {

    $marker = 'moove-gdpr-cookie-notice';
    $arrParts = explode($marker,$buffer);
    if(isset($arrParts[1])){
        $arrParts[1] = dcic_filter_string($arrParts[1]);
    }
    $buffer = implode($marker,$arrParts);

    $marker = 'privacy_overview';
    $arrParts = explode($marker,$buffer);
    if(isset($arrParts[3])){
        $arrParts[3] = dcic_filter_string($arrParts[3]);
    }
    $buffer = implode($marker,$arrParts);

    $marker1 = 'moove-gdpr-strict-warning-message';
    $marker2 = 'moove-gdpr-tab-main-content';
    $arrParts = explode($marker1,$buffer);
    if(isset($arrParts[1])){
        $arrParts2 = explode($marker2,$arrParts[1]);
        if(isset($arrParts2[1])){
            $arrParts2[0] = dcic_filter_string($arrParts2[0]);
            $arrParts[1] = implode($arrParts2,$marker2);
            $buffer = implode($arrParts,$marker1);
        }
    }

    $marker1 = 'gdpr-nav-tab-title';
    $marker2 = '</span>';

    $arrParts = explode($marker1,$buffer);
    if(isset($arrParts[1])){
        $arrParts2 = explode($marker2,$arrParts[1]);
        if(isset($arrParts2[1])){
            $arrParts2[0] = dcic_filter_string($arrParts2[0]);
            $arrParts[1] = implode($arrParts2,$marker2);
            $buffer = implode($arrParts,$marker1);
        }
    }

    $arrParts = explode($marker1,$buffer);
    if(isset($arrParts[2])){
        $arrParts2 = explode($marker2,$arrParts[2]);
        if(isset($arrParts2[1])){
            $arrParts2[0] = dcic_filter_string($arrParts2[0]);
            $arrParts[2] = implode($arrParts2,$marker2);
            $buffer = implode($arrParts,$marker1);
        }
    }

    $marker1 = 'strict-necessary-cookies';
    $marker2 = '</p>';

    $arrParts = explode($marker1,$buffer);
    if(isset($arrParts[1])){
        $arrParts2 = explode($marker2,$arrParts[1]);
        if(isset($arrParts2[1])){
            $arrParts2[0] = dcic_filter_string($arrParts2[0]);
            $arrParts[1] = implode($arrParts2,$marker2);
            $buffer = implode($arrParts,$marker1);
        }
    }

    $buffer = str_replace(
        '<span class="gdpr-sr-only">Enable or Disable Cookies</span>',
        '<span class="gdpr-sr-only">Enable or Disable Data Collectors</span>',
        $buffer
    );

    $buffer = str_replace(
        '<span class="gdpr-sr-only">Close GDPR Cookie Settings</span>',
        '<span class="gdpr-sr-only">Close GDPR Data Collector Settings</span>',
        $buffer
    );

    $buffer = str_replace(
        'aria-label="Strictly Necessary Cookies"',
        'aria-label="Strictly Necessary Data Collectors"',
        $buffer
    );

    $marker1 = 'id="strict-necessary-cookies"';
    $marker2 = '</p>';

    $arrParts = explode($marker1,$buffer);
    if(isset($arrParts[1])){
        $arrParts2 = explode($marker2,$arrParts[1]);
        if(isset($arrParts2[1])){
            $arrParts2[0] = dcic_filter_string($arrParts2[0]);
            $arrParts[1] = implode($arrParts2,$marker2);
            $buffer = implode($arrParts,$marker1);
        }
    }

    return $buffer;

}

/**
* Filters mixed data from cookie-law-info plugin's options.
*/
function dcic_filter_cookie_law_info($mixed){
    if(is_array($mixed)){
        $arrOptionsToFilter = array(
            'button_1_text',
            'button_2_text',
            'button_3_text',
            'button_4_text',
            'button_7_text',
            'notify_message',
            'cookielawinfo_privacy_overview_content_settings',
            'privacy_overview_content',
            'privacy_overview_title'
        );
        foreach($arrOptionsToFilter as $option){
            if(isset($mixed[$option])){
                $mixed[$option] = dcic_filter_string($mixed[$option]);
            }
        }
    }
    if(is_string($mixed)){
        $mixed = dcic_filter_string($mixed);
    }
    return $mixed;
}

/**
* Replaces the term "cookies" for "data collectors" in a string.
* Supports singular and plural, capitalized or not.
*/
function dcic_filter_string($stringToFilter){
    $stringToFilter = preg_replace('/\b('.preg_quote('Cookies', '/').')\b/', 'Data Collectors', $stringToFilter);
    $stringToFilter = preg_replace('/\b('.preg_quote('cookies', '/').')\b/', 'data collectors', $stringToFilter);
    $stringToFilter = preg_replace('/\b('.preg_quote('Cookie', '/').')\b/', 'Data Collector', $stringToFilter);
    $stringToFilter = preg_replace('/\b('.preg_quote('cookie', '/').')\b/', 'data collector', $stringToFilter);
    return $stringToFilter;
}

//check dependencies
add_action( 'admin_init', 'dcic_check_dependencies' );
//and run
add_action( 'template_redirect', 'dcic_run' );
