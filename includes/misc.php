<?php      

// Set the defult timezone
date_default_timezone_set("Africa/Lagos");

// Set the site configuration here
// Default configuration
$configuration = array('language' => 'english', 'site_name' => 'Passengine', 'site_phone' => '09031983482'
	, 'twillio_phone' => '+1092292922', 'cleanurl' => 0);
// You can pass this configuration information from a database, your database should contain the default
// configuration variables
// $configuration = configuration();

// Store the theme path and theme name into the CONF and TMPL
$PTMPL['template_path'] = $SETT['template_path'];
$PTMPL['template_name'] = $SETT['template_name'] = 'default';//$settings['template'];
$PTMPL['template_url'] = $SETT['template_url'] = $SETT['template_path'].'/'.$SETT['template_name'];


