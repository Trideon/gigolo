<?php   
/*
Plugin Name: iQ Block Country
Plugin URI: http://www.redeo.nl/2013/12/iq-block-country-wordpress-plugin-blocks-countries/
Version: 1.1.25
Author: Pascal
Author URI: http://www.redeo.nl/
Description: Block visitors from visiting your website and backend website based on which country their IP address is from. The Maxmind GeoIP lite database is used for looking up from which country an ip address is from.
License: GPL2
Text Domain: iq-block-country
Domain Path: /lang
*/

/* This script uses GeoLite Country from MaxMind (http://www.maxmind.com) which is available under terms of GPL/LGPL */

/*  Copyright 2010-2015  Pascal  (email : pascal@redeo.nl)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
 * 
 * This software is dedicated to my one true love.
 * Luvya :)
 * 
 */

/*
 * Try to make this plugin the first plugin that is loaded.
 * Because we output header info we don't want other plugins to send output first.
 */
function iqblockcountry_this_plugin_first() 
{
	$wp_path_to_this_file = preg_replace('/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR."/$2", __FILE__);
	$this_plugin = plugin_basename(trim($wp_path_to_this_file));
	$active_plugins = get_option('active_plugins');
	$this_plugin_key = array_search($this_plugin, $active_plugins);
	if ($this_plugin_key) { // if it's 0 it's the first plugin already, no need to continue
		array_splice($active_plugins, $this_plugin_key, 1);
		array_unshift($active_plugins, $this_plugin);
		update_option('active_plugins', $active_plugins);
	}     
}


/*
 * Attempt on output buffering to protect against headers already send mistakes 
 */
function iqblockcountry_buffer() {
	ob_start();
} 

/*
 * Attempt on output buffering to protect against headers already send mistakes 
 */
function iqblockcountry_buffer_flush() {
	ob_end_flush();
} 


/*
 * Localization
 */
function iqblockcountry_localization()
{
    load_plugin_textdomain( 'iq-block-country', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}

/*
 * Get list of countries from Geo Database
 */
function iqblockcountry_get_countries()
{
    global $countrylist;
    
    $countrylist = array();
    if (!class_exists('GeoIP'))
    {
	include_once("libs/geoip.inc");
    }
    if (class_exists('GeoIP'))
    {
	/* Create an array with all countries that the database knows */
	$geo = new GeoIP ();
	$countrycodes = $geo->GEOIP_COUNTRY_CODE_TO_NUMBER;
	$countries = $geo->GEOIP_COUNTRY_NAMES;
	$countrylist = array ();
	foreach ( $countrycodes as $key => $value ) {
            if (!empty($value))
            {
		$countrylist [$key] = $countries [$value];
            }
	}
	array_multisort($countrylist);

        return $countrylist;
    }

    return $countylist;
 }    

 /*
  * Retrieves the IP address from the HTTP Headers
 */
function iqblockcountry_get_ipaddress() {
    global $ip_address;
    
    if ( isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) ) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    }
    elseif ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
    $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $ip_address = trim($ips[0]);
    } elseif ( isset($_SERVER['HTTP_X_REAL_IP']) && !empty($_SERVER['HTTP_X_REAL_IP']) ) {
    $ip_address = $_SERVER['HTTP_X_REAL_IP'];
    } elseif ( isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) ) {
    $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    } elseif ( isset($_SERVER['HTTP_X_TM_REMOTE_ADDR']) && !empty($_SERVER['HTTP_X_TM_REMOTE_ADDR']) ) {
    $ip_address = $_SERVER['HTTP_X_TM_REMOTE_ADDR'];
    }
    
    return $ip_address;
}


function iqblockcountry_upgrade()
{
    /* Check if update is necessary */
    $dbversion = get_option( 'blockcountry_version' );
    update_option('blockcountry_version',VERSION);
    if ($dbversion != "" && version_compare($dbversion, "1.1.21", '<') )
    {
        if (!get_option('blockcountry_geoapilocation'))
        {
            iqblockcountry_find_geoip_location();
        }
    }
    elseif ($dbversion != "" && version_compare($dbversion, "1.1.19", '<') )
    {
        update_option('blockcountry_blocksearch','on');
    }
    if ($dbversion != "" && version_compare($dbversion, "1.1.17", '<') )
    {
        delete_option('blockcountry_automaticupdate');
        delete_option('blockcountry_lastupdate');
    }
    elseif ($dbversion != "" && version_compare($dbversion, "1.1.11", '<') )
    {
        update_option('blockcountry_nrstatistics', 15);
    }
    elseif ($dbversion != "" && version_compare($dbversion, "1.0.10", '<') )
    {
        $frontendbanlist = get_option('blockcountry_banlist');
        update_option('blockcountry_backendbanlist',$frontendbanlist);
        update_option('blockcountry_backendnrblocks', 0);
        update_option('blockcountry_frontendnrblocks', 0);
        update_option('blockcountry_header', 'on');
    }
    elseif ($dbversion != "" && version_compare($dbversion, "1.0.10", '=') )
    {
        iqblockcountry_install_db();
        update_option('blockcountry_backendnrblocks', 0);
        update_option('blockcountry_frontendnrblocks', 0);
        update_option('blockcountry_header', 'on');
    }        
    elseif ($dbversion == "")
    {
        iqblockcountry_install_db();
        add_option( "blockcountry_dbversion", DBVERSION );
        update_option('blockcountry_blockfrontend' , 'on');
        update_option('blockcountry_version',VERSION);
        update_option('blockcountry_backendnrblocks', 0);
        update_option('blockcountry_frontendnrblocks', 0);
        update_option('blockcountry_header', 'on');
        $frontendbanlist = get_option('blockcountry_banlist');
        update_option('blockcountry_backendbanlist',$frontendbanlist);
    }    

    iqblockcountry_update_db_check();
   
}


/*
 * Main plugin works.
 */
$upload_dir = wp_upload_dir();
define("CHOSENJS", plugins_url('/chosen.jquery.js', __FILE__));
define("CHOSENCSS", plugins_url('/chosen.css', __FILE__));
define("IPV6DB","http://geolite.maxmind.com/download/geoip/database/GeoIPv6.dat.gz"); // Used to display download location.
define("IPV4DB","http://geolite.maxmind.com/download/geoip/database/GeoLiteCountry/GeoIP.dat.gz"); // Used to display download location.
define("IPV4DBFILE",$upload_dir['basedir'] . "/GeoIP.dat");
define("IPV6DBFILE",$upload_dir['basedir'] . "/GeoIPv6.dat");
define("TRACKINGURL","http://tracking.webence.nl/iq-block-country-tracking.php");
define("BANLISTRETRIEVEURL","http://tracking.webence.nl/iq-block-country-retrieve.php");
define("GEOIPAPIURL","http://geoip.webence.nl/geoipapi.php");
define("GEOIPAPIURLUS","http://us.geoip.webence.nl/geoipapi.php");
define("GEOIPAPICHECKURL","http://geoip.webence.nl/geoipapi-keycheck.php");
define("ADMINAPICHECKURL","http://tracking.webence.nl/adminapi-keycheck.php");
define("VERSION","1.1.22");
define("DBVERSION","121");
define("PLUGINPATH",plugin_dir_path( __FILE__ )); 



/*
 * Include libraries
 */
require_once('libs/blockcountry-checks.php');
require_once('libs/blockcountry-settings.php');
require_once('libs/blockcountry-validation.php');
require_once('libs/blockcountry-logging.php');
require_once('libs/blockcountry-tracking.php');
require_once('libs/blockcountry-search-engines.php');
    
global $apiblacklist;
$apiblacklist = FALSE;
$backendblacklistcheck = FALSE;

$blockcountry_is_login_page = iqblockcountry_is_login_page();

register_activation_hook(__file__, 'iqblockcountry_this_plugin_first');
register_activation_hook(__file__, 'iqblockcountry_set_defaults');
register_uninstall_hook(__file__, 'iqblockcountry_uninstall');

 // Check if upgrade is necessary
 iqblockcountry_upgrade();
  
 /* Clean logging database */
 iqblockcountry_clean_db();
 

if (isset($_POST['action']))
{    
    $iqaction = filter_var($_POST['action'],FILTER_SANITIZE_STRING);
    if ($iqaction == 'csvoutput') {
        global $wpdb;
        $output = "";
        $table_name = $wpdb->prefix . "iqblock_logging";
        $format = get_option('date_format') . ' ' . get_option('time_format');
        foreach ($wpdb->get_results( "SELECT * FROM $table_name ORDER BY datetime ASC" ) as $row)
        {
            $datetime = strtotime($row->datetime);
            $mysqldate = date($format, $datetime);
            $output .= '"' . $mysqldate . '"' . ';"' . $row->ipaddress . '";"' . $row->url . '"'. "\n";
        }
        $iqtempvalue = preg_replace("/[^A-Za-z0-9]/", "", get_bloginfo());
        $filename = $iqtempvalue . "-iqblockcountry-logging-export.csv";
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=$filename");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $output;
        exit();
    }
}

    $ip_address = iqblockcountry_get_ipaddress();
    $country = iqblockcountry_check_ipaddress($ip_address);
    iqblockcountry_debug_logging($ip_address,$country,'');


  /*
 * Check first if users want to block the backend.
 */
if (($blockcountry_is_login_page || is_admin()) && get_option('blockcountry_blockbackend') == 'on')
{
    add_action ( 'init', 'iqblockcountry_checkCountry', 1 );
}
elseif (get_option('blockcountry_blockfrontend') == "on")
{
    add_action ( 'wp_head', 'iqblockcountry_checkCountry', 1 );
}
else
{
    $ip_address = iqblockcountry_get_ipaddress();
    $country = iqblockcountry_check_ipaddress($ip_address);
    iqblockcountry_debug_logging($ip_address,$country,'NH');

}

add_action ( 'admin_init', 'iqblockcountry_localization');
add_action ( 'admin_menu', 'iqblockcountry_create_menu' );
add_filter ( 'update_option_blockcountry_tracking', 'iqblockcountry_schedule_tracking', 10, 2);
add_filter ( 'add_option_blockcountry_tracking', 'iqblockcountry_schedule_tracking', 10, 2);
add_filter ( 'update_option_blockcountry_apikey', 'iqblockcountry_schedule_retrieving', 10, 2);
add_filter ( 'add_option_blockcountry_apikey', 'iqblockcountry_schedule_retrieving', 10, 2);

add_filter ( 'update_option_blockcountry_debuglogging', 'iqblockcountry_blockcountry_debuglogging', 10, 2);
add_filter ( 'add_option_blockcountry_debuglogging', 'iqblockcountry_blockcountry_debuglogging', 10, 2);
add_action ( 'blockcountry_tracking', 'iqblockcountry_tracking' );
add_action ( 'blockcountry_retrievebanlist',  'iqblockcountry_tracking_retrieve_xml');
if (get_option('blockcountry_buffer') == "on")
{
    add_action ( 'init', 'iqblockcountry_buffer',1);
    add_action ( 'wp_footer', 'iqblockcountry_buffer_flush');
}


?>
