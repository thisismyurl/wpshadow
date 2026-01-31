<?php
/**
 * MemberPress Content Protection Diagnostic
 *
 * MemberPress content protection bypassed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.530.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MemberPress Content Protection Diagnostic Class
 *
 * @since 1.530.0000
 */
class Diagnostic_MemberpressContentProtection extends Diagnostic_Base {

	protected static $slug = 'memberpress-content-protection';
	protected static $title = 'MemberPress Content Protection';
	protected static $description = 'MemberPress content protection bypassed';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'MEPR_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Protection rules exist
		$rules = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s",
				'memberpressrule',
				'publish'
			)
		);
		
		if ( $rules === 0 ) {
			return null;
		}
		
		// Check 2: Unprotected content detection
		$unprotected = get_option( 'mepr_unprotected_content_count', 0 );
		if ( $unprotected > 10 ) {
			$issues[] = sprintf( __( '%d posts/pages without protection rules', 'wpshadow' ), $unprotected );
		}
		
		// Check 3: REST API exposure
		$protect_api = get_option( 'mepr_protect_rest_api', true );
		if ( ! $protect_api ) {
			$issues[] = __( 'REST API not protected (content accessible via JSON endpoints)', 'wpshadow' );
		}
		
		// Check 4: RSS/feed protection
		$protect_feeds = get_option( 'mepr_protect_feeds', false );
		if ( ! $protect_feeds ) {
			$issues[] = __( 'RSS feeds not protected (content leak)', 'wpshadow' );
		}
		
		// Check 5: Unauthorized access logging
		$log_access = get_option( 'mepr_log_unauthorized_access', false );
		if ( ! $log_access ) {
			$issues[] = __( 'Unauthorized access attempts not logged', 'wpshadow' );
		}
		
		// Check 6: Excerpt protection
		$protect_excerpt = get_option( 'mepr_protect_excerpts', true );
		if ( ! $protect_excerpt ) {
			$issues[] = __( 'Excerpts not protected (content preview available)', 'wpshadow' );
		}
		
		// Check 7: Search results protection
		$hide_from_search = get_option( 'mepr_hide_protected_from_search', false );
		if ( ! $hide_from_search ) {
			$issues[] = __( 'Protected content appears in search results', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 75;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 88;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 82;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of protection bypass issues */
				__( 'MemberPress content protection has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/memberpress-content-protection',
		);
	}
}
