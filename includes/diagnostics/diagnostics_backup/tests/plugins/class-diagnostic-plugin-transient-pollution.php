<?php
/**
 * Plugin Transient Pollution Diagnostic
 *
 * Detects plugins creating excessive or expired transients.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1700
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Plugin Transient Pollution Class
 *
 * Monitors transients for pollution - expired or orphaned entries.
 * Excessive transients impact database performance.
 *
 * @since 1.5029.1700
 */
class Diagnostic_Plugin_Transient_Pollution extends Diagnostic_Base {

protected static $slug        = 'plugin-transient-pollution';
protected static $title       = 'Plugin Transient Pollution';
protected static $description = 'Detects excessive or expired transients';
protected static $family      = 'plugins';

public static function check() {
'wpshadow_transient_pollution';
 = get_transient( $cache_key );

!== $cached ) {
 $cached;
Get transient counts.
sient_count = $wpdb->get_var(
T(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'"
ore WordPress.DB.DirectDatabaseQuery

sients = $wpdb->get_var(
T(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_%'"
ore WordPress.DB.DirectDatabaseQuery

transients.
uery = $wpdb->prepare(
T(*) FROM {$wpdb->options} 
_name LIKE %s 
D option_value < %d",
sient_timeout_%',
t = $wpdb->get_var( $expired_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared

sient_ratio = $transient_count / 1000;

transients OR 100+ expired.
sient_count > 1000 || $expired_count > 100 ) {
( $expired_count > 500 ) {
= array(
        => self::$slug,
     => self::$title,
'  => sprintf(
slators: 1: transient count, 2: expired count */
sients found (%2$s expired). Clean up to improve database performance.', 'wpshadow' ),
umber_format_i18n( $transient_count ),
umber_format_i18n( $expired_count )
   => 'medium',
=> true,
k'      => 'https://wpshadow.com/kb/performance-transient-cleanup',
      => array(
sients' => $transient_count,
sients' => $expired_count,
up_recommended' => $expired_count > 100,
sient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
 $result;
sient( $cache_key, null, 24 * HOUR_IN_SECONDS );
 null;
}
}
