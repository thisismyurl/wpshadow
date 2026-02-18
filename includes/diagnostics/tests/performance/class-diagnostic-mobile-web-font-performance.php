<?php
/**
 * Mobile Web Font Performance
 *
 * Optimizes web font loading strategy for mobile.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.602.1600
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Web Font Performance
 *
 * Validates font-display strategy and preload hints for optimal
 * web font loading on mobile.
 *
 * @since 1.602.1600
 */
class Diagnostic_Mobile_Web_Font_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-web-font-performance';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Web Font Performance';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Optimizes web font loading strategy for mobile';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.602.1600
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = self::find_font_issues();

		if ( empty( $issues['all'] ) ) {
			return null; // Font strategy optimized
		}

		$threat = 60;
		if ( count( $issues['all'] ) > 3 ) {
			$threat = 75;
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %d: count of font issues */
				__( 'Found %d web font performance issues', 'wpshadow' ),
				count( $issues['all'] )
			),
			'severity'        => 'medium',
			'threat_level'    => $threat,
			'issues'          => array_slice( $issues['all'], 0, 5 ),
			'total_issues'    => count( $issues['all'] ),
			'recommendations' => $issues['recommendations'] ?? array(),
			'user_impact'     => __( 'Fonts block rendering by 100-400ms', 'wpshadow' ),
			'auto_fixable'    => true,
			'kb_link'         => 'https://wpshadow.com/kb/web-font-performance',
		);
	}

	/**
	 * Find font performance issues.
	 *
	 * @since  1.602.1600
	 * @return array Issues found.
	 */
	private static function find_font_issues(): array {
		$html = self::get_page_html();
		if ( ! $html ) {
			return array( 'all' => array() );
		}

		$issues = array(
			'all'              => array(),
			'recommendations'  => array(),
		);

		// Check font-face declarations
		preg_match_all( '/@font-face\s*{([^}]+)}/', $html, $font_faces );
		foreach ( $font_faces[1] ?? array() as $declaration ) {
			// Check for font-display
			if ( false === strpos( $declaration, 'font-display' ) ) {
				$issues['all'][] = array(
					'issue'        => 'Missing font-display property',
					'recommendation' => 'Add font-display: swap to prevent FOIT',
				);
			} elseif ( preg_match( '/font-display\s*:\s*(?:auto|block)/', $declaration ) ) {
				$issues['all'][] = array(
					'issue'        => 'Suboptimal font-display: auto or block',
					'recommendation' => 'Change to swap for faster text visibility',
				);
			}
		}

		// Check for font preload hints
		$preload_count = preg_match_all( '/rel=["\']preload["\'][^>]*font/', $html );
		if ( $preload_count === 0 ) {
			$font_count = preg_match_all( '/@font-face|\.woff2?|\.ttf/', $html );
			if ( $font_count > 0 ) {
				$issues['all'][] = array(
					'issue'        => 'No font preload hints',
					'recommendation' => 'Add <link rel="preload" as="font"> for critical fonts',
				);
			}
		}

		// Check for system font fallback
		$has_fallback = preg_match( '/font-family:[^;]*system-ui|-apple-system|sans-serif/', $html );
		if ( ! $has_fallback ) {
			$issues['all'][] = array(
				'issue'        => 'No system font fallback',
				'recommendation' => 'Add system fonts in fallback chain',
			);
		}

		$issues['recommendations'] = array(
			'Use font-display: swap for immediate fallback',
			'Preload critical font files',
			'Limit font weights (regular, bold only)',
			'Use WOFF2 format (30% smaller than WOFF)',
		);

		return $issues;
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since  1.602.1600
	 * @return string|null HTML content.
	 */
	private static function get_page_html(): ?string {
		return Diagnostic_HTML_Helper::fetch_homepage_html(
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
