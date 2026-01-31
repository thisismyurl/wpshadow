<?php
/**
 * Gdpr Cookie Compliance Policy Links Diagnostic
 *
 * Gdpr Cookie Compliance Policy Links not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1107.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gdpr Cookie Compliance Policy Links Diagnostic Class
 *
 * @since 1.1107.0000
 */
class Diagnostic_GdprCookieCompliancePolicyLinks extends Diagnostic_Base {

	protected static $slug = 'gdpr-cookie-compliance-policy-links';
	protected static $title = 'Gdpr Cookie Compliance Policy Links';
	protected static $description = 'Gdpr Cookie Compliance Policy Links not compliant';
	protected static $family = 'security';

	public static function check() {
		// Check for GDPR cookie plugins
		$has_gdpr = defined( 'COOKIEYES_VERSION' ) || 
		            function_exists( 'gdpr_cookie_compliance' ) || 
		            class_exists( 'GDPR_Cookie_Consent' );
		
		if ( ! $has_gdpr ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Privacy policy page set
		$privacy_page = get_option( 'wp_page_for_privacy_policy', 0 );
		if ( ! $privacy_page ) {
			$issues[] = __( 'No privacy policy page configured', 'wpshadow' );
		} else {
			$page_status = get_post_status( $privacy_page );
			if ( 'publish' !== $page_status ) {
				$issues[] = __( 'Privacy policy page not published', 'wpshadow' );
			}
		}
		
		// Check 2: Cookie policy page
		$cookie_page = get_option( 'gdpr_cookie_policy_page', 0 );
		if ( ! $cookie_page ) {
			$issues[] = __( 'No cookie policy page configured', 'wpshadow' );
		}
		
		// Check 3: Policy links in cookie banner
		$banner_has_links = get_option( 'gdpr_cookie_banner_show_policy_link', false );
		if ( ! $banner_has_links ) {
			$issues[] = __( 'Cookie banner missing policy links', 'wpshadow' );
		}
		
		// Check 4: Policy last updated
		if ( $privacy_page ) {
			$page = get_post( $privacy_page );
			$last_modified = strtotime( $page->post_modified );
			
			if ( ( time() - $last_modified ) > ( 365 * DAY_IN_SECONDS ) ) {
				$issues[] = __( 'Privacy policy not updated in over a year (GDPR recommends annual review)', 'wpshadow' );
			}
		}
		
		// Check 5: Required policy sections
		if ( $privacy_page ) {
			$content = get_post_field( 'post_content', $privacy_page );
			$required_sections = array( 'cookie', 'data protection', 'contact', 'rights' );
			$missing_sections = array();
			
			foreach ( $required_sections as $section ) {
				if ( stripos( $content, $section ) === false ) {
					$missing_sections[] = $section;
				}
			}
			
			if ( count( $missing_sections ) > 0 ) {
				$issues[] = sprintf(
					/* translators: %s: list of missing sections */
					__( 'Privacy policy missing sections: %s', 'wpshadow' ),
					implode( ', ', $missing_sections )
				);
			}
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 70;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 82;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 76;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of policy compliance issues */
				__( 'GDPR cookie compliance has %d policy issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/gdpr-cookie-compliance-policy-links',
		);
	}
}
