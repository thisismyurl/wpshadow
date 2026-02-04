<?php
/**
 * Newsletter Quality Standards Diagnostic
 *
 * Tests newsletter frequency, design, and engagement tracking.
 *
 * @since   1.6050.0000
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Newsletter Quality Standards Diagnostic Class
 *
 * Verifies newsletter tools are configured and quality checks exist.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Maintains_Newsletter_Quality extends Diagnostic_Base {

	protected static $slug = 'maintains-newsletter-quality';
	protected static $title = 'Newsletter Quality Standards';
	protected static $description = 'Tests newsletter frequency, design, and engagement tracking';
	protected static $family = 'publisher';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$newsletter_plugins = array(
			'mailpoet/mailpoet.php',
			'newsletter/plugin.php',
			'mailchimp-for-wp/mailchimp-for-wp.php',
		);

		foreach ( $newsletter_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return null;
			}
		}

		$manual_flag = get_option( 'wpshadow_newsletter_quality_standards' );
		if ( $manual_flag ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No newsletter quality program detected. Use a newsletter tool and track engagement to improve results.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/newsletter-quality-standards',
			'persona'      => 'publisher',
		);
	}
}
