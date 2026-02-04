<?php
/**
 * PHP Version Plugin Compatibility Diagnostic
 *
 * Checks if installed plugins are compatible with the current PHP version.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2205
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * PHP Version Plugin Compatibility Diagnostic Class
 *
 * Identifies plugins that may not be compatible with the current PHP version.
 *
 * @since 1.6030.2205
 */
class Diagnostic_PHP_Version_Plugin_Compatibility extends Diagnostic_Base {

/**
 * The diagnostic slug
 *
 * @var string
 */
protected static $slug = 'php-version-plugin-compatibility';

/**
 * The diagnostic title
 *
 * @var string
 */
protected static $title = 'PHP Version Plugin Compatibility';

/**
 * The diagnostic description
 *
 * @var string
 */
protected static $description = 'Checks if installed plugins are compatible with the current PHP version';

/**
 * The family this diagnostic belongs to
 *
 * @var string
 */
protected static $family = 'plugins';

/**
 * Run the diagnostic check.
 *
 * @since  1.6030.2205
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
 = PHP_VERSION;
     = array();
ings    = array();

Get all installed plugins.
( ! function_exists( 'get_plugins' ) ) {
uire_once ABSPATH . 'wp-admin/includes/plugin.php';
s    = get_plugins();
s = get_option( 'active_plugins', array() );

compatible_active   = array();
compatible_inactive = array();

( $all_plugins as $plugin_file => $plugin_data ) {
= in_array( $plugin_file, $active_plugins, true );

Check Requires PHP header.
uires_php = isset( $plugin_data['RequiresPHP'] ) ? $plugin_data['RequiresPHP'] : '';

( ! empty( $requires_php ) && version_compare( $php_version, $requires_php, '<' ) ) {
_info = array(
ame'         => $plugin_data['Name'],
uires_php' => $requires_php,
t_php'  => $php_version,
( $is_active ) {
compatible_active[] = $plugin_info;
else {
compatible_inactive[] = $plugin_info;
( ! empty( $incompatible_active ) ) {
= sprintf(
translators: %d: number of incompatible plugins */
'%d active plugins incompatible with PHP %s', 'wpshadow' ),
t( $incompatible_active ),


( ! empty( $incompatible_inactive ) ) {
ings[] = sprintf(
translators: %d: number of incompatible inactive plugins */
'%d inactive plugins incompatible with PHP %s', 'wpshadow' ),
t( $incompatible_inactive ),


Check for old PHP versions.
( version_compare( $php_version, '7.4', '<' ) ) {
= sprintf(
translators: %s: PHP version */
'PHP %s is deprecated - many modern plugins require PHP 7.4+', 'wpshadow' ),


Check for deprecated PHP features still in use.
= array();

( version_compare( $php_version, '8.0', '>=' ) ) {
Check for plugins that might use deprecated features in PHP 8.0+.
( $active_plugins as $plugin_file ) {
_path = WP_PLUGIN_DIR . '/' . $plugin_file;
( file_exists( $plugin_path ) ) {
tent = file_get_contents( $plugin_path );
Check for common PHP 8.0 incompatibilities.
( strpos( $content, 'create_function' ) !== false ) {
= sprintf(
translators: %s: plugin name */
'%s uses deprecated create_function()', 'wpshadow' ),
ame( $plugin_file )
( ! empty( $deprecated_checks ) ) {
ings[] = sprintf(
translators: %d: number of plugins */
'%d plugins use deprecated PHP features', 'wpshadow' ),
t( $deprecated_checks )

Report findings.
( ! empty( $incompatible_active ) || ! empty( $issues ) ) {
     = 'medium';
= 60;

( ! empty( $incompatible_active ) ) {
     = 'high';
= 85;
 = __( 'PHP version compatibility issues detected with installed plugins', 'wpshadow' );

= array(
' => $php_version,
( ! empty( $incompatible_active ) ) {
compatible_active_plugins'] = $incompatible_active;
( ! empty( $incompatible_inactive ) ) {
compatible_inactive_plugins'] = array_slice( $incompatible_inactive, 0, 5 );
( ! empty( $issues ) ) {
= $issues;
( ! empty( $warnings ) ) {
ings'] = $warnings;
 array(
          => self::$slug,
       => self::$title,
'  => $description,
'     => $severity,
=> $threat_level,
=> false,
k'      => 'https://wpshadow.com/kb/php-version-plugin-compatibility',
     => $details,

 null;
}
}
