<?php
/**
 * Bitbucket Updater Branches Diagnostic
 *
 * Bitbucket Updater Branches issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1082.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bitbucket Updater Branches Diagnostic Class
 *
 * @since 1.1082.0000
 */
class Diagnostic_BitbucketUpdaterBranches extends Diagnostic_Base {

	protected static $slug = 'bitbucket-updater-branches';
	protected static $title = 'Bitbucket Updater Branches';
	protected static $description = 'Bitbucket Updater Branches issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Bitbucket updater plugins
		$has_updater = get_option( 'bitbucket_updater_configured', false ) ||
		               defined( 'BITBUCKET_UPDATER_VERSION' ) ||
		               get_option( 'gu_bitbucket_token', '' );
		
		if ( ! $has_updater ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Branch configuration
		$branch = get_option( 'bitbucket_updater_branch', 'master' );
		if ( 'master' === $branch || 'main' === $branch ) {
			$issues[] = __( 'Tracking production branch (receive unstable updates)', 'wpshadow' );
		}
		
		// Check 2: Access token security
		$token_location = get_option( 'bitbucket_token_location', 'database' );
		if ( 'database' === $token_location ) {
			$issues[] = __( 'API token in database (should be in wp-config.php)', 'wpshadow' );
		}
		
		// Check 3: Update check frequency
		$check_frequency = get_option( 'bitbucket_update_check_frequency', 12 );
		if ( $check_frequency < 6 ) {
			$issues[] = sprintf( __( 'Update checks every %d hours (excessive API calls)', 'wpshadow' ), $check_frequency );
		}
		
		// Check 4: Version tagging
		$use_tags = get_option( 'bitbucket_use_version_tags', false );
		if ( ! $use_tags ) {
			$issues[] = __( 'Not using version tags (unpredictable updates)', 'wpshadow' );
		}
		
		// Check 5: Automatic updates
		$auto_update = get_option( 'bitbucket_auto_update', false );
		if ( $auto_update ) {
			$issues[] = __( 'Automatic updates enabled (untested code risk)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of updater issues */
				__( 'Bitbucket updater has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/bitbucket-updater-branches',
		);
	}
}
