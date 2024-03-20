<?php
/*
Plugin Name: IK Melio Payment Gateway
Plugin URI: https://inforket.com/
Description: Melio Payment Gateway
Version: 1.1.3
Author: Inforket.com / Gabriel Caroprese
Author URI: https://inforket.com/
Requires at least: 5.3
Requires PHP: 7.2
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$ik_meliopg_dir = dirname( __FILE__ );
$ik_meliopg_public_dir = plugin_dir_url(__FILE__ );
define( 'IK_MELIOPG_PLUGIN_DIR', $ik_meliopg_dir);
define( 'IK_MELIOPG_PLUGIN_DIR_PUBLIC', $ik_meliopg_public_dir);

require_once($ik_meliopg_dir . '/include/class.php');
require_once($ik_meliopg_dir . '/include/hooks.php');

?>