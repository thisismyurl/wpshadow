<?php
/**
 * Comment Cookies Consent Treatment
 *
 * Verifies comment cookie consent is properly configured for GDPR compliance.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1755
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Cookies Consent Treatment Class
 *
 * Checks comment cookie consent configuration for privacy compliance.
 *
 * @since 1.6032.1755
 */
class Treatment_Comment_Cookies_Consent extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-cookies-consent';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Cookies Consent';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comment cookie consent for GDPR';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1755
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if comment cookies opt-in is enabled (WP 4.9.6+).
		$show_comments_cookies_opt_in = get_option( 'show_comments_cookies_opt_in', true );

		if ( ! $show_comments_cookies_opt_in ) {
			$issues[] = __( 'Comment cookies consent checkbox is disabled - GDPR compliance concern', 'wpshadow' );
		}

		// Check if comments are enabled.
		$default_comment_status = get_option( 'default_comment_status', 'open' );
		if ( $default_comment_status !== 'open' ) {
			return null; // Comments disabled, cookie consent is irrelevant.
		}

		// Check theme support for comment form customization.
		if ( ! current_theme_supports( 'html5', 'comment-form' ) ) {
			$issues[] = __( 'Theme lacks HTML5 comment form support - consent checkbox may not display properly', 'wpshadow' );
		}

		// Check for GDPR/privacy plugins.
		$gdpr_plugins = array(
			'gdpr-cookie-consent/gdpr-cookie-consent.php',
			'cookie-law-info/cookie-law-info.php',
			'complianz-gdpr/complianz-gdpr.php',
		);

		$has_gdpr_plugin = false;
		foreach ( $gdpr_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_gdpr_plugin = true;
				break;
			}
		}

		if ( ! $has_gdpr_plugin ) {
			$issues[] = __( 'No GDPR/cookie consent plugin detected for comprehensive compliance', 'wpshadow' );
		}

		// Check privacy policy page.
		$privacy_policy_page_id = get_option( 'wp_page_for_privacy_policy', 0 );
		if ( ! $privacy_policy_page_id ) {
			$issues[] = __( 'No privacy policy page configured', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-cookies-consent',
			);
		}

		return null;
	}
}
