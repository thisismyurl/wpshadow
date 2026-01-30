<?php
/**
 * Restrict Content Pro Content Restrictions Diagnostic
 *
 * RCP content restrictions bypassable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.329.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restrict Content Pro Content Restrictions Diagnostic Class
 *
 * @since 1.329.0000
 */
class Diagnostic_RestrictContentProContentRestrictions extends Diagnostic_Base {

	protected static $slug = 'restrict-content-pro-content-restrictions';
	protected static $title = 'Restrict Content Pro Content Restrictions';
	protected static $description = 'RCP content restrictions bypassable';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'RCP_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: REST API protection
		$protect_rest = get_option( 'rcp_protect_rest_api', false );
		if ( ! $protect_rest ) {
			$issues[] = __( 'REST API not protected (content exposed via API)', 'wpshadow' );
		}
		
		// Check 2: Feed protection
		$protect_feeds = get_option( 'rcp_protect_feeds', false );
		if ( ! $protect_feeds ) {
			$issues[] = __( 'RSS feeds not protected (full content in feeds)', 'wpshadow' );
		}
		
		// Check 3: Search results protection
		$hide_from_search = get_option( 'rcp_hide_restricted_from_search', false );
		if ( ! $hide_from_search ) {
			$issues[] = __( 'Restricted content appears in search results', 'wpshadow' );
		}
		
		// Check 4: Excerpt protection
		$protect_excerpts = get_option( 'rcp_protect_excerpts', true );
		if ( ! $protect_excerpts ) {
			$issues[] = __( 'Excerpts not protected (content preview leaked)', 'wpshadow' );
		}
		
		// Check 5: Direct URL access
		$check_restricted = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != ''",
				'rcp_subscription_level'
			)
		);
		
		if ( $check_restricted > 0 ) {
			$redirect_enabled = get_option( 'rcp_redirect_from_premium', false );
			if ( ! $redirect_enabled ) {
				$issues[] = __( 'No redirect for restricted content (direct access possible)', 'wpshadow' );
			}
		}
		
		// Check 6: Attachment protection
		$protect_attachments = get_option( 'rcp_protect_attachments', false );
		if ( ! $protect_attachments ) {
			$issues[] = __( 'Media files not protected (direct file access)', 'wpshadow' );
		}
		
		// Check 7: Access logging
		$log_access = get_option( 'rcp_log_restricted_access', false );
		if ( ! $log_access ) {
			$issues[] = __( 'Access attempts not logged (no audit trail)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 70;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 84;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 77;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of content restriction bypass risks */
				__( 'Restrict Content Pro has %d bypass risks: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/restrict-content-pro-content-restrictions',
		);
	}
}
