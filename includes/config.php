<?php
//error_reporting(E_ALL);
/*
 * turn off magic-quotes support, for runtime e, as it will cause problems if enabled
 */
if (version_compare(PHP_VERSION, 5.3, '<') && function_exists('set_magic_quotes_runtime')) set_magic_quotes_runtime(0);


$SETT = $PTMPL = array();

/* 
* set currentPage in the local scope
*/
$SETT['current_page'] = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);

/* 
* The MySQL credentials
*/	
define('DB_PREFIX', '');	
$SETT['dbdriver'] = 'mysql'; 
$SETT['dbhost'] = 'localhost'; 
$SETT['dbuser'] = 'root'; 
$SETT['dbpass'] = 'idontknow1A@'; 
$SETT['dbname'] = 'passcolabs';

/* 
* The Installation URL 
* https is enforced in .HTACCESS, to use the auto protocol feature remove the .HTACCESS https enforcement
*/
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
$SETT['url'] = $protocol.'://'.$_SERVER['HTTP_HOST'];

/* 
* The Notifications e-mail
*/
$SETT['email'] = 'support@passcontest.com';  

/* 
* The templates directory
*/
$SETT['template_path'] = 'templates';

$action = array('homepage'			=> 'homepage',
				'artist'			=> 'artist',
				'album'				=> 'album',
				'track'				=> 'track',
				'playlist'			=> 'playlist',
				'project'			=> 'project',
				'listen'			=> 'listen',
				'follow'			=> 'follow',
				'view_artists'		=> 'view_artists',
				'distribution'		=> 'distribution',
				'explore'			=> 'explore'
				); 

/* 
* Define the cookies path
*/				
define('COOKIE_PATH', preg_replace('|'.$protocol.'?://[^/]+|i', '', $SETT['url']).'/');

?>
