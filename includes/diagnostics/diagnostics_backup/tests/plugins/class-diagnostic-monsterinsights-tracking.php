<?php
/**
 * MonsterInsights Tracking Configuration
 *
 * Validates GA tracking setup.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1815
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

class Diagnostic_MonsterInsights_Tracking extends Diagnostic_Base {
protected static $slug        = 'monsterinsights-tracking';
protected static $title       = 'MonsterInsights Tracking';
protected static $description = 'Validates GA configuration';
protected static $family      = 'plugins';

public static function check() {
if ( ! function_exists( 'MonsterInsights' ) && ! class_exists( 'MonsterInsights_Lite' ) ) {
return null;
}

$cache_key = 'wpshadow_mi_tracking';
$cached    = get_transient( $cache_key );
if ( false !== $cached ) {
return $cached;
}

$issues = array();
$ua_code = get_option( 'monsterinsights_ua', '' );

if ( empty( $ua_code ) ) {
$issues[] = 'No Google Analytics tracking ID configured';
} elseif ( ! preg_match( '/^(UA-|G-)/', $ua_code ) ) {
$issues[] = 'Invalid tracking ID format';
}

$tracking_mode = get_option( 'monsterinsights_tracking_mode', 'analytics' );
if ( 'gtag' === $tracking_mode && strpos( $ua_code, 'UA-' ) === 0 ) {
$issues[] = 'Using gtag.js with Universal Analytics ID';
}

$events_mode = get_option( 'monsterinsights_events_mode', 'js' );
if ( 'js' !== $events_mode ) {
$issues[] = 'Event tracking not optimal';
}

if ( ! empty( $issues ) ) {
$result = array(
'id'           => self::$slug,
'title'        => self::$title,
'description'  => sprintf( __( '%d tracking configuration issues.', 'wpshadow' ), count( $issues ) ),
'severity'     => 'medium',
'threat_level' => 50,
'auto_fixable' => false,
'kb_link'      => 'https://wpshadow.com/kb/monsterinsights-tracking',
'data'         => array( 'tracking_issues' => $issues ),
);
set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
return $result;
}

set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
return null;
}
}
