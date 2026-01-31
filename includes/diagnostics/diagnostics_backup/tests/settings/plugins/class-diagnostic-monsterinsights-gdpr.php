<?php
/**
 * MonsterInsights GDPR Compliance Diagnostic
 *
 * Validates GDPR settings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1820
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
class-diagnostic-exit;
}

class Diagnostic_MonsterInsights_GDPR extends Diagnostic_Base {
class-diagnostic-protected static $slug        = 'monsterinsights-gdpr';
class-diagnostic-protected static $title       = 'MonsterInsights GDPR Compliance';
class-diagnostic-protected static $description = 'Validates GDPR settings';
class-diagnostic-protected static $family      = 'plugins';

class-diagnostic-public static function check() {
class-diagnostic-$cache_key = 'wpshadow_' . self::$slug;
class-diagnostic-$cached    = get_transient( $cache_key );
class-diagnostic-if ( false !== $cached ) {
class-diagnostic- $cached;
class-diagnostic-}


class-diagnostic-if ( ! function_exists( 'MonsterInsights' ) && ! class_exists( 'MonsterInsights_Lite' ) ) {
class-diagnostic- null;
class-diagnostic-}
class-diagnostic-
class-diagnostic-$issues = array();
class-diagnostic-$anonymize = get_option( 'monsterinsights_anonymize_ips', false );
class-diagnostic-if ( ! $anonymize ) {
class-diagnostic-'IP anonymization not enabled';
class-diagnostic-}
class-diagnostic-
class-diagnostic-$demographics = get_option( 'monsterinsights_demographics', false );
class-diagnostic-if ( $demographics ) {
class-diagnostic-'Demographics tracking enabled (GDPR concern)';
class-diagnostic-}
class-diagnostic-
class-diagnostic-if ( ! empty( $issues ) ) {
class-diagnostic- array(
class-diagnostic-self::$slug,
class-diagnostic-self::$title,
class-diagnostic-' => sprintf( __( '%d GDPR compliance issues.', 'wpshadow' ), count( $issues ) ),
class-diagnostic-=> 'high',
class-diagnostic-70,
class-diagnostic-false,
class-diagnostic-k' => 'https://wpshadow.com/kb/monsterinsights-gdpr',
class-diagnostic-array( 'gdpr_issues' => $issues ),
class-diagnostic- null;
class-diagnostic-
class-diagnostic-set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
class-diagnostic-return null;
class-diagnostic-}
}
