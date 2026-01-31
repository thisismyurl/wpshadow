<?php
/**
 * Salient Theme Page Header Styles Diagnostic
 *
 * Salient Theme Page Header Styles needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1326.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Salient Theme Page Header Styles Diagnostic Class
 *
 * @since 1.1326.0000
 */
class Diagnostic_SalientThemePageHeaderStyles extends Diagnostic_Base {

	protected static $slug = 'salient-theme-page-header-styles';
	protected static $title = 'Salient Theme Page Header Styles';
	protected static $description = 'Salient Theme Page Header Styles needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'wp_get_theme' ) ) {
			return null;
		}

		$current_theme = wp_get_theme();
		if ( 'Salient' !== $current_theme->get( 'Name' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify header styles enabled
		$header_styles = get_option( 'salient_header_styles_enabled', false );
		if ( ! $header_styles ) {
			$issues[] = __( 'Salient header styles not enabled', 'wpshadow' );
		}

		// Check 2: Check page header configuration
		$page_header = get_option( 'salient_page_header_config', '' );
		if ( empty( $page_header ) ) {
			$issues[] = __( 'Page header configuration not set', 'wpshadow' );
		}

		// Check 3: Verify header caching
		$header_cache = get_transient( 'salient_header_cache' );
		if ( false === $header_cache ) {
			$issues[] = __( 'Header styles caching not active', 'wpshadow' );
		}

		// Check 4: Check CSS optimization
		$css_optimization = get_option( 'salient_header_css_optimization', false );
		if ( ! $css_optimization ) {
			$issues[] = __( 'Header CSS optimization not enabled', 'wpshadow' );
		}

		// Check 5: Verify responsive header
		$responsive_header = get_option( 'salient_responsive_header', false );
		if ( ! $responsive_header ) {
			$issues[] = __( 'Responsive header styles not enabled', 'wpshadow' );
		}

		// Check 6: Check header mobile optimization
		$mobile_opt = get_option( 'salient_header_mobile_optimization', false );
		if ( ! $mobile_opt ) {
			$issues[] = __( 'Mobile header optimization not configured', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Salient page header styles issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/salient-theme-page-header-styles',
			);
		}

		return null;
	}
}
