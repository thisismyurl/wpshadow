<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_MonsterInsights_Performance extends Diagnostic_Base {
protected static $slug = 'monsterinsights-performance';
protected static $title = 'MonsterInsights Performance';
protected static $description = 'Checks performance impact';
protected static $family = 'plugins';

public static function check() {
function_exists( 'MonsterInsights' ) && ! class_exists( 'MonsterInsights_Lite' ) ) {
 null;
'wpshadow_mi_perf';
sient( $cache_key );
!== $cached ) { return $cached; }
();
g = get_option( 'monsterinsights_tracking_mode', 'analytics' );
!== $local_tracking ) {
ot using JavaScript tracking (slower)';
empty( $issues ) ) {
(
=> self::$title,
' => sprintf( __( '%d performance issues.', 'wpshadow' ), count( $issues ) ),
'medium',
=> false,
k' => 'https://wpshadow.com/kb/monsterinsights-performance',
( 'issues' => $issues ),
sient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
 $result;
sient( $cache_key, null, 24 * HOUR_IN_SECONDS );
 null;
}
}
