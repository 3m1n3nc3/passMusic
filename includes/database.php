<?php
	// basic options for PDO 
	$dboptions = array(
	    PDO::ATTR_PERSISTENT => FALSE,
	    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
	);

	//connect with the server
	try {
	    $DB = new PDO($SETT['dbdriver'] . ':host=' . $SETT['dbhost'] . ';dbname=' . $SETT['dbname'], $SETT['dbuser'], $SETT['dbpass'], $dboptions);
	} catch (Exception $ex) {
	    echo $ex->getMessage();
	    die;
	}
?>
