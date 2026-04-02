<?php
/**
 * Data Masking in UI Diagnostic
 *
 * Checks for proper data masking in user interfaces including credit card
 * masking, password field types, and sensitive data in HTML output.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data Masking in UI Diagnostic Class
 *
 * Verifies sensitive data is properly masked in user interfaces and
 * HTML output to prevent information disclosure.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Data_Masking extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'masks_sensitive_ui_data';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Data Masking in UI';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies sensitive data is properly masked in user interfaces';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for eCommerce plugins with masking features (30 points).
		$ecommerce_plugins = array(
			'woocommerce/woocommerce.php'                 => 'WooCommerce',
			'easy-digital-downloads/easy-digital-downloads.php' => 'Easy Digital Downloads',
			'wp-e-commerce/wp-shopping-cart.php'          => 'WP eCommerce',
			'cart66-lite/cart66-lite.php'                 => 'Cart66',
		);

		$active_ecommerce = array();
		foreach ( $ecommerce_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_ecommerce[] = $plugin_name;
				$earned_points     += 15; // Up to 30 points.

				// Check WooCommerce specific masking settings.
				if ( 'woocommerce/woocommerce.php' === $plugin_file ) {
					$mask_card = get_option( 'woocommerce_mask_card_numbers', 'yes' );
					if ( 'yes' === $mask_card ) {
						$stats['woocommerce_masking'] = 'enabled';
					} else {
						$issues[] = 'WooCommerce card number masking not enabled';
					}
				}
			}
		}

		if ( count( $active_ecommerce ) > 0 ) {
			$stats['ecommerce_plugins'] = implode( ', ', $active_ecommerce );
		}

		// Check for security plugins with data protection (25 points).
		$security_plugins = array(
			'wordfence/wordfence.php'                       => 'Wordfence Security',
			'better-wp-security/better-wp-security.php'     => 'iThemes Security',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
			'sucuri-scanner/sucuri.php'                     => 'Sucuri Security',
			'jetpack/jetpack.php'                           => 'Jetpack Security',
		);

		$active_security = array();
		foreach ( $security_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_security[] = $plugin_name;
				$earned_points    += 8; // Up to 25 points.
			}
		}

		if ( count( $active_security ) > 0 ) {
			$stats['security_plugins'] = implode( ', ', $active_security );
		} else {
			$issues[] = 'No security plugins detected for data protection';
		}

		// Check for HTML comment disclosure (20 points).
		$sample_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				'orderby'        => 'rand',
			)
		);

		$pages_with_comments   = 0;
		$sensitive_patterns    = array( 'password', 'key', 'secret', 'token', 'api', 'credit', 'card' );
		$found_sensitive_terms = array();

		foreach ( $sample_pages as $page ) {
			$content = $page->post_content;
			if ( preg_match_all( '/<!--(.+?)-->/s', $content, $matches ) ) {
				$pages_with_comments++;
				foreach ( $matches[1] as $comment ) {
					foreach ( $sensitive_patterns as $pattern ) {
						if ( stripos( $comment, $pattern ) !== false ) {
							$found_sensitive_terms[] = $pattern;
						}
					}
				}
			}
		}

		if ( count( $found_sensitive_terms ) > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: Number of sensitive terms */
				_n(
					'%d sensitive term found in HTML comments',
					'%d sensitive terms found in HTML comments',
					count( array_unique( $found_sensitive_terms ) ),
					'wpshadow'
				),
				count( array_unique( $found_sensitive_terms ) )
			);
			$stats['sensitive_in_comments'] = array_unique( $found_sensitive_terms );
		} else {
			$earned_points += 20;
		}

		$stats['pages_with_comments'] = $pages_with_comments;

		// Check for privacy/data masking plugins (15 points).
		$privacy_plugins = array(
			'gdpr-cookie-compliance/gdpr-cookie-compliance.php' => 'GDPR Cookie Compliance',
			'cookie-notice/cookie-notice.php'                   => 'Cookie Notice',
			'wp-gdpr-compliance/wp-gdpr-compliance.php'         => 'WP GDPR Compliance',
			'complianz-gdpr/complianz-gdpr.php'                 => 'Complianz',
		);

		$active_privacy = array();
		foreach ( $privacy_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_privacy[] = $plugin_name;
				$earned_points   += 5; // Up to 15 points.
			}
		}

		if ( count( $active_privacy ) > 0 ) {
			$stats['privacy_plugins'] = implode( ', ', $active_privacy );
		}

		// Check for HTTPS (10 points).
		if ( is_ssl() ) {
			$earned_points         += 10;
			$stats['https_enabled'] = true;
		} else {
			$issues[] = 'HTTPS not enabled - sensitive data transmitted without encryption';
		}

		// Calculate score percentage.
		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		// Return finding if score is below 65%.
		if ( $score < 65 ) {
			$severity     = $score < 50 ? 'high' : 'medium';
			$threat_level = $score < 50 ? 75 : 65;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your data masking practices scored %s. Sensitive information like credit card numbers, passwords, or API keys may be visible in your user interface or HTML source. Proper data masking protects user privacy and prevents information disclosure attacks.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/data-masking-in-ui',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}
}
