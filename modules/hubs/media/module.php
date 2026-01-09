<?php
/**
 * Media Hub Module
 *
 * This module is loaded by the TIMU Core Module Loader.
 * It is NOT a WordPress plugin, but an extension of Core.
 *
 * @package TIMU_CORE
 * @subpackage TIMU_MEDIA_HUB
 */

namespace TIMU\MediaSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load the original media-support plugin file (without plugin headers).
$media_main_file = __DIR__ . '/media-support-thisismyurl.php';
if ( file_exists( $media_main_file ) ) {
	require_once $media_main_file;
}
