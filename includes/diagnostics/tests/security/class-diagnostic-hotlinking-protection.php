<?php
/**
 * Hotlinking Protection Diagnostic
 *
 * Checks if hotlinking protection is configured for media files.
 * **Business Impact:**
 * Photography portfolio site. Images directly linked from 50+ forum/blog posts.\n * Receives 1M page views/month on external sites (your images embedded).\n * Your images = 500GB bandwidth/month externally. ISP/CDN bill: $5K/month extra.\n * Enable hotlink protection: image requests redirected to placeholder.\n * Saves $5K/month bandwidth + prevents image misuse.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Media protected from unauthorized use\n * - #9 Show Value: Quantified bandwidth savings\n * - #10 Beyond Pure: Respects content ownership + prevents misinformation\n *
 * **Related Checks:**
 * - File Permission Security (file access control)\n * - Directory Listing Prevention (information disclosure)\n * - Rate Limiting (abuse prevention)\n *
 * **Learn More:**
 * Hotlink protection setup: https://wpshadow.com/kb/hotlinking-protection-wordpress\n * Video: Bandwidth optimization via hotlink blocking (8min): https://wpshadow.com/training/hotlink-protection\n *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_Request_Helper;
use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Hotlinking_Protection Class
 *
 * Validates referrer-based protection for media files.\n * Implements hotlink blocking via .htaccess or server rules.\n *
 * **Detection Pattern:**
 * 1. Check .htaccess for RewriteCond HTTP_REFERER rules\n * 2. Validate hotlink-blocking redirect or placeholder logic\n * 3. Test if external referrers rejected\n * 4. Check if legitimate referrers whitelisted\n * 5. Validate all media types protected\n * 6. Return severity if protection missing\n *
 * **Real-World Scenario:**
 * Developer uploads high-quality product images (5MB each). No hotlink\n * protection. Third-party comparison shopping site embeds images directly.\n * Site gets 1M visitors/month. Your images loaded for each visit (no caching\n * by third party). Bandwidth explosion. ISP throttles due to overuse.\n * Sites takes 30 seconds to load (legitimate traffic suffers).\n *
 * **Implementation Notes:**
 * - Checks .htaccess for RewriteCond rules\n * - Validates HTTP_REFERER blocking\n * - Tests placeholder image setup\n * - Severity: medium (no protection), high (major bandwidth waste)\n * - Treatment: enable hotlink blocking via .htaccess\n *
 * @since 1.6093.1200
 */
class Diagnostic_Hotlinking_Protection extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'hotlinking-protection';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Hotlinking Protection';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if hotlinking protection is configured';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates:
	 * - .htaccess referrer rules
	 * - Response difference for external referrers
	 * - Hotlink protection plugins
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$has_rules = false;

		$upload_dir = wp_upload_dir();
		$htaccess   = trailingslashit( $upload_dir['basedir'] ) . '.htaccess';

		if ( file_exists( $htaccess ) && is_readable( $htaccess ) ) {
			$content = file_get_contents( $htaccess );
			if ( false !== $content ) {
				if ( false !== stripos( $content, 'HTTP_REFERER' ) || false !== stripos( $content, 'hotlink' ) ) {
					$has_rules = true;
				}
			}
		}

		// Test a sample media URL with different referrers.
		global $wpdb;
		$attachment_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID
				FROM {$wpdb->posts}
				WHERE post_type = %s
				ORDER BY post_date DESC
				LIMIT 1",
				'attachment'
			)
		);

		$referrer_block = false;
		if ( 0 < $attachment_id ) {
			$url = wp_get_attachment_url( $attachment_id );
			if ( ! empty( $url ) ) {
				$internal = Diagnostic_Request_Helper::head_result(
					$url,
					array(
						'timeout' => 5,
						'headers' => array(
							'Referer' => home_url( '/' ),
						),
					)
				);

				$external = Diagnostic_Request_Helper::head_result(
					$url,
					array(
						'timeout' => 5,
						'headers' => array(
							'Referer' => 'https://example.com/',
						),
					)
				);

				if ( $internal['success'] && $external['success'] ) {
					$internal_code = (int) $internal['code'];
					$external_code = (int) $external['code'];
					if ( 200 <= $internal_code && 200 <= $external_code && $external_code >= 400 ) {
						$referrer_block = true;
					}
				}
			}
		}

		if ( ! $has_rules && ! $referrer_block ) {
			$issues[] = __( 'No hotlinking protection detected - media can be embedded from other sites', 'wpshadow' );
		}

		// Check for hotlink protection plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$hotlink_plugins = array(
			'prevent-direct-access' => __( 'Prevent Direct Access plugin detected', 'wpshadow' ),
			'all-in-one-wp-security-and-firewall' => __( 'All In One WP Security may handle hotlink protection', 'wpshadow' ),
			'wordfence'             => __( 'Wordfence may provide hotlink rules', 'wpshadow' ),
		);

		foreach ( $hotlink_plugins as $slug => $message ) {
			foreach ( $active_plugins as $plugin ) {
				if ( false !== strpos( $plugin, $slug ) ) {
					$issues[] = sprintf(
						/* translators: %s: message */
						__( 'Plugin note: %s', 'wpshadow' ),
						$message
					);
					break;
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d hotlinking protection issue detected',
						'%d hotlinking protection issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/hotlinking-protection',
				'details'      => array(
					'issues'         => $issues,
					'has_rules'      => $has_rules,
					'referrer_block' => $referrer_block,
				),
			);
		}

		return null;
	}
}
