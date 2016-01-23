<?php


function iqblockcountry_check_ipaddress($ip_address)
{
    if (!class_exists('GeoIP'))
    {
	include_once("geoip.inc");
    }
    
    if ((is_file ( IPV4DBFILE )) && function_exists('geoip_open')) {

	$ipv4 = FALSE;
	$ipv6 = FALSE;
	if (iqblockcountry_is_valid_ipv4($ip_address)) { $ipv4 = TRUE; }
	if (iqblockcountry_is_valid_ipv6($ip_address)) { $ipv6 = TRUE; }
	
	if ($ipv4) 
	{ 	
		$gi = geoip_open ( IPV4DBFILE, GEOIP_STANDARD );
		$country = geoip_country_code_by_addr ( $gi, $ip_address );
		geoip_close ( $gi );
	}
	elseif ($ipv6)
	{
		if (is_file ( IPV6DBFILE )) {				
			$gi = geoip_open(IPV6DBFILE,GEOIP_STANDARD);
			$country = geoip_country_code_by_addr_v6 ( $gi, $ip_address );
 			geoip_close($gi);
		}
		else {
			$country = 'ipv6';				
		}
	}
        else { $country = "Unknown"; }
        }
        elseif (get_option('blockcountry_geoapikey'))
        {
            $country = iqblockcountry_retrieve_geoipapi($ip_address);
        }
        else { $country = "Unknown"; }
        
        
    return $country;
}

/*
 * iQ Block Retrieve XML file for API blocking
 */
function iqblockcountry_retrieve_geoipapi($ipaddress)
{
    $url = GEOIPAPIURL;
    if (get_option('blockcountry_geoapilocation') == "US")
    {
        $url = GEOIPAPIURLUS;
    }

    $result = wp_remote_post(
            $url,
            array(
                'body' => array(
                    'api-key' => get_option('blockcountry_geoapikey'),
                    'ipaddress' => $ipaddress
                 
                )
            )
        );    
    if ( 200 == $result['response']['code'] ) {
	$body = $result['body'];
        $xml = new SimpleXmlElement($body);
        return (string) $xml->country;
    }
    elseif ( 403 == $result['response']['code'] ) {
        update_option('blockcountry_geoapikey','');
    }
    else return "Unknown";
}



/*
 *  Check country against bad countries, whitelist and blacklist
 */
function iqblockcountry_check($country,$badcountries,$ip_address)
{
    /* Set default blocked status and get all options */
    $blocked = FALSE; 
    $blockedpage = get_option('blockcountry_blockpages');
    $blockedcategory = get_option('blockcountry_blockcategories');
    $blockedposttypes = get_option('blockcountry_blockposttypes');

    $frontendblacklistip = array();   $frontendblacklist = get_option ( 'blockcountry_frontendblacklist' );
    $frontendwhitelistip = array();   $frontendwhitelist = get_option ( 'blockcountry_frontendwhitelist' );
    $backendblacklistip = array();    $backendblacklist = get_option ( 'blockcountry_backendblacklist' );
    $backendwhitelistip = array();    $backendwhitelist = get_option ( 'blockcountry_backendwhitelist' );
    
    $backendbanlistip = unserialize(get_option('blockcountry_backendbanlistip'));
    $blockredirect = get_option ( 'blockcountry_redirect');
    
   
    if (preg_match('/;/',$frontendblacklist))
    {
        $frontendblacklistip = explode(";", $frontendblacklist);
    }
    if (preg_match('/;/',$frontendwhitelist))
    {
            $frontendwhitelistip = explode(";", $frontendwhitelist);
    }
    if (preg_match('/;/',$backendblacklist))
    {
        $backendblacklistip = explode(";", $backendblacklist);
    }
    if (preg_match('/;/',$backendwhitelist))
    {
            $backendwhitelistip = explode(";", $backendwhitelist);
    }
    
    /* Block if user is in a bad country from frontend or backend. Unblock may happen later */
    if (is_array ( $badcountries ) && in_array ( $country, $badcountries )) {
        $blocked = TRUE;
        global $backendblacklistcheck;
        $backendblacklistcheck = TRUE;
    }

    global $blockcountry_is_login_page;

    /* Check if requested url is not login page. Else check against frontend whitelist/blacklist. */
    if (!$blockcountry_is_login_page )
    {    
        if (is_array ( $frontendblacklistip ) && in_array ( $ip_address, $frontendblacklistip)) {
             $blocked = TRUE;
            }
        if (is_array ( $frontendwhitelistip ) && in_array ( $ip_address, $frontendwhitelistip)) {
                $blocked = FALSE;
            }
    }
    
    
    if ($blockcountry_is_login_page || is_admin())
    {    
        if (is_array($backendbanlistip) &&  in_array($ip_address,$backendbanlistip))
        {
            $blocked = TRUE;
            global $apiblacklist;
            $apiblacklist = TRUE;
        }
        if (is_array ( $backendblacklistip ) && in_array ( $ip_address, $backendblacklistip)) {
             $blocked = TRUE;
            }
        if (is_array ( $backendwhitelistip ) && in_array ( $ip_address, $backendwhitelistip)) {
                $blocked = FALSE;
            }
    }

    global $post;

    if ($blockedposttypes == "on")
    {
        $blockedposttypes = get_option('blockcountry_posttypes');
        if (is_array($blockedposttypes) && in_array(get_post_type( $post->ID ), $blockedposttypes) && ((is_array ( $badcountries ) && in_array ( $country, $badcountries ) || (is_array ( $frontendblacklistip ) && in_array ( $ip_address, $frontendblacklistip)))))
        {
            $blocked = TRUE;
            if (is_array ( $frontendwhitelistip ) && in_array ( $ip_address, $frontendwhitelistip)) {
                $blocked = FALSE;
            }
        }
    }
    
    if (is_page() && $blockedpage == "on")
    {
        $blockedpages = get_option('blockcountry_pages');
        if (is_page($blockedpages) && !empty($blockedpages) && ((is_array ( $badcountries ) && in_array ( $country, $badcountries ) || (is_array ( $frontendblacklistip ) && in_array ( $ip_address, $frontendblacklistip)))))
        {
            $blocked = TRUE;
            if (is_array ( $frontendwhitelistip ) && in_array ( $ip_address, $frontendwhitelistip)) {
                $blocked = FALSE;
            }
        }
        else
        {
            $blocked = FALSE;
        }
    }
    if (is_single() && $blockedcategory == "on")
    {
        global $post;
        $blockedcategories = get_option('blockcountry_categories');
        if (!is_array($blockedcategories)) { $blockedcategories = array(); }
        $post_categories = wp_get_post_categories( $post->ID );
        $flagged = FALSE;
        foreach ($post_categories as $key => $value)
        {
            if (in_array($value,$blockedcategories))
            {
                if (is_single() && ((is_array ( $badcountries ) && in_array ( $country, $badcountries ) || (is_array ( $frontendblacklistip ) && in_array ( $ip_address, $frontendblacklistip)))))
                {
                    $flagged = TRUE;
                    if (is_array ( $frontendwhitelistip ) && in_array ( $ip_address, $frontendwhitelistip)) {
                        $flagged = FALSE;
                    }
                }
            }            
        }
        if ($flagged) { $blocked = TRUE; } else { $blocked = FALSE; }
    }

    if (is_category() && $blockedcategory == "on")
    {
        $flagged = FALSE;
        $blockedcategories = get_option('blockcountry_categories');
        if (is_category($blockedcategories) && ((is_array ( $badcountries ) && in_array ( $country, $badcountries ) || (is_array ( $frontendblacklistip ) && in_array ( $ip_address, $frontendblacklistip)))))
        {
            $flagged = TRUE;
        }
        if (is_array ( $frontendwhitelistip ) && in_array ( $ip_address, $frontendwhitelistip)) {
                        $flagged = FALSE;
        }
       if ($flagged) { $blocked = TRUE; } else { $blocked = FALSE; }
    }
    
    
    if (is_home() && (get_option('blockcountry_blockhome')) == FALSE && $blockedcategory == "on")
    {
        $blocked = FALSE;
    }
    if (is_page($blockredirect) && ($blockredirect != 0) && !(empty($blockredirect)))
    {
        $blocked = FALSE;
    }
    
    $allowse = get_option('blockcountry_allowse');
    if (!$blockcountry_is_login_page && isset ($_SERVER['HTTP_USER_AGENT']) && iqblockcountry_check_searchengine($_SERVER['HTTP_USER_AGENT'], $allowse))
    {
        $blocked = FALSE;
    }
    
    if (is_search() && (get_option('blockcountry_blocksearch')) == FALSE)
    {
        $blocked = FALSE;
    }
    
    return $blocked;
}


/*
 * 
 * Does the real check of visitor IP against MaxMind database or the GeoAPI
 * 
 */
function iqblockcountry_CheckCountry() {
    
    $ip_address = iqblockcountry_get_ipaddress();
    $country = iqblockcountry_check_ipaddress($ip_address);
    global $blockcountry_is_login_page;
    if (($blockcountry_is_login_page || is_admin()) && get_option('blockcountry_blockbackend'))
    { 
        $banlist = get_option( 'blockcountry_backendbanlist' );
        if (!is_array($banlist)) { $banlist = array(); }
        if (get_option( 'blockcountry_backendbanlist_inverse' ) == 'on') {
            $all_countries = array_keys(iqblockcountry_get_countries());
            $badcountries = array_diff($all_countries, $banlist);
        } else {
            $badcountries = $banlist;
        }
    }
    else
    {    
        $banlist = get_option( 'blockcountry_banlist' );
        if (!is_array($banlist)) { $banlist = array(); }
        if (get_option( 'blockcountry_banlist_inverse' ) == 'on') {
            $all_countries = array_keys(iqblockcountry_get_countries());
            $badcountries = array_diff($all_countries, $banlist);
        } else {
            $badcountries = $banlist;
        }

    }
    $blocklogin = get_option ( 'blockcountry_blocklogin' );
    if ( ((is_user_logged_in()) && ($blocklogin != "on")) || (!(is_user_logged_in())) )  {			
	
        /* Check ip address against banlist, whitelist and blacklist */
        if (iqblockcountry_check($country,$badcountries,$ip_address))
        {        
                if (($blockcountry_is_login_page || is_admin()) && get_option('blockcountry_blockbackend'))
                {
                    $blocked = get_option('blockcountry_backendnrblocks');
                    if (empty($blocked)) { $blocked = 0; }
                    $blocked++;
                    update_option('blockcountry_backendnrblocks', $blocked);
                    global $apiblacklist,$backendblacklistcheck,$debughandled;
                    if (!get_option('blockcountry_logging'))
                    {
                        if (!$apiblacklist)
                        {    
                            iqblockcountry_logging($ip_address, $country, "B");
                            iqblockcountry_debug_logging($ip_address,$country,'BB');
                        }
                        elseif ($backendblacklistcheck && $apiblacklist)
                        {
                            iqblockcountry_logging($ip_address, $country, "T");
                            iqblockcountry_debug_logging($ip_address,$country,'TB');
                        }
                        else
                        {
                            iqblockcountry_logging($ip_address, $country, "A");   
                            iqblockcountry_debug_logging($ip_address,$country,'AB');
                        }
                    }
                }
                else
                {
                    $blocked = get_option('blockcountry_frontendnrblocks');
                    if (empty($blocked)) { $blocked = 0; }
                    $blocked++;
                    update_option('blockcountry_frontendnrblocks', $blocked);
                    if (!get_option('blockcountry_logging'))
                    {
                        iqblockcountry_logging($ip_address, $country, "F");
                        iqblockcountry_debug_logging($ip_address,$country,'FB');
                    }
                }
            
                
        	$blockmessage = get_option ( 'blockcountry_blockmessage' );
                $blockredirect = get_option ( 'blockcountry_redirect');
                $blockredirect_url = get_option ( 'blockcountry_redirect_url');
                $header = get_option('blockcountry_header');
                if (!empty($header) && ($header))
                {
                    // Prevent as much as possible that this error message is cached:
                    header("Cache-Control: no-store, no-cache, must-revalidate");
                    header("Cache-Control: post-check=0, pre-check=0", false);
                    header("Pragma: no-cache");
                    header("Expires: Sat, 26 Jul 2012 05:00:00 GMT"); 
                                
                    header ( 'HTTP/1.1 403 Forbidden' );
                }
                if (!empty($blockredirect_url))
                {
                    header("Location: $blockredirect_url");
                }
                elseif (!empty($blockredirect) && $blockredirect != 0)
                {
                    $redirecturl = get_permalink($blockredirect);
                    header("Location: $redirecturl");
                }
                // Display block message
                print "$blockmessage";


		exit ();
	}	
        else
        {
            iqblockcountry_debug_logging($ip_address,$country,'NB');
        }
    }
    else
    {
            iqblockcountry_debug_logging($ip_address,$country,'NB');
    }
}


/*
 * Check if page is the login page
 */
function iqblockcountry_is_login_page() {
   $found = FALSE;
   $pos2 = FALSE;
   
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
    if ( is_plugin_active( 'all-in-one-wp-security-and-firewall/wp-security.php' ) ) {
        $aio = get_option('aio_wp_security_configs');
        if (!empty($aio)) {
        $pos2 = strpos( $_SERVER['REQUEST_URI'], $aio['aiowps_login_page_slug'] ); }
    } 
 
    if ( is_plugin_active( 'lockdown-wp-admin/lockdown-wp-admin.php' ) ) {
        $ld = get_option('ld_login_base');
        if (!empty($ld)) {
        $pos2 = strpos( $_SERVER['REQUEST_URI'], $ld); }
    } 
    
    if ( is_plugin_active( 'wp-simple-firewall/icwp-wpsf.php' ) ) {
        $wpsf = get_option('icwp_wpsf_loginprotect_options');
        if (!empty($wpsf['rename_wplogin_path'])) {
        $pos2 = strpos( $_SERVER['REQUEST_URI'], $wpsf['rename_wplogin_path'] ); }
    } 
 
    
    $pos = strpos( $_SERVER['REQUEST_URI'], 'wp-login' );
    if ($pos !== false) { $found = TRUE; }
    elseif ($pos2 !== false) { $found = TRUE; }
    
    return $found;
}


/*
 * Check if page is within wp-admin page
 */
function iqblockcountry_is_admin() {
   $found = FALSE;
   
 
    $pos = strpos( $_SERVER['REQUEST_URI'], '/wp-admin/' );
    if ($pos !== false) { $found = TRUE; }
    
    return $found;
}