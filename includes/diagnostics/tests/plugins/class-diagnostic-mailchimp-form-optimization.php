<?php
/**
 * Mailchimp Form Optimization Diagnostic
 *
 * Mailchimp forms loading too many assets.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.225.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mailchimp Form Optimization Diagnostic Class
 *
 * @since 1.225.0000
 */
class Diagnostic_MailchimpFormOptimization extends Diagnostic_Base {

	protected static $slug = 'mailchimp-form-optimization';
	protected static $title = 'Mailchimp Form Optimization';
	protected static $description = 'Mailchimp forms loading too many assets';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'mc4wp' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Lazy loading enabled
		$lazy = get_option( 'mc4wp_lazy_loading_enabled', 0 );
		if ( ! $lazy ) {
			$issues[] = 'Lazy loading not enabled';
		}

		// Check 2: CSS optimization
		$css_opt = get_option( 'mc4wp_css_optimization_enabled', 0 );
		if ( ! $css_opt ) {
			$issues[] = 'CSS optimization not enabled';
		}

		// Check 3: Script minification
		$minify = get_option( 'mc4wp_script_minification_enabled', 0 );
		if ( ! $minify ) {
			$issues[] = 'Script minification not enabled';
		}

		// Check 4: Async form loading
		$async = get_option( 'mc4wp_async_form_loading_enabled', 0 );
		if ( ! $async ) {
			$issues[] = 'Async form loading not enabled';
		}

		// Check 5: Form caching
		$cache = get_option( 'mc4wp_form_caching_enabled', 0 );
		if ( ! $cache ) {
			$issues[] = 'Form caching not enabled';
		}

		// Check 6: Asset compression
		$compress = get_option( 'mc4wp_asset_compression_enabled', 0 );
		if ( ! $compress ) {
			$issues[] = 'Asset compression not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 30;
			$threat_multiplier = 6;
			$max_threat = 60;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d form optimization issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/mailchimp-form-optimization',
			);
		}

		return null;
	}
}
