<?php
/**
 * Plugin Name: Yivic REST API - User Authentication & Access Control
 * Plugin URI:  https://yivic.com/wp-plugin-yivic-rest-api/
 * Description: This WordPress plugin provides a secure and efficient way to handle REST API authentication and user access control
 * Author:      dev@yivic.com, manhphucofficial@yahoo.com
 * Author URI:  https://yivic.com/yivic-team/
 * Version:     0.0.1
 * License:     MIT
 * License URI: https://mit-license.org/
 * Text Domain: yivic-rest-api
 */

// We want to split all the bootstrapping code to a separate file
//  for putting into composer autoload and
//  for easier including on other section e.g. unit test
require_once __DIR__ . DIRECTORY_SEPARATOR . 'yivic-rest-api-bootstrap.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'yivic-rest-api-init.php';