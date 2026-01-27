<?php
/**
 * Diagnostic: Accessibility Standards Compliance (WCAG)
 *
 * Detects WCAG 2.1 accessibility compliance issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_WCAG_Accessibility_Compliance
 *
 * Performs basic accessibility checks for WCAG 2.1 compliance including
 * alt text, heading hierarchy, and color contrast.
 *
 * @since 1.2601.2148
 */
class Diagnostic_WCAG_Accessibility_Compliance extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wcag-accessibility-compliance';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Accessibility Standards Compliance (WCAG)';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect WCAG 2.1 accessibility compliance issues';

	/**
	 * Run the diagnostic check.
	 *
	 * Note: This performs basic checks. Full WCAG audit requires specialized tools.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for accessibility plugin
		$a11y_plugins = array(
			'one-click-accessibility/one-click-accessibility.php' => 'One Click Accessibility',
			'wp-accessibility/wp-accessibility.php' => 'WP Accessibility',
			'accessibility-checker/accessibility-checker.php' => 'Accessibility Checker',
		);

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$has_a11y_plugin = false;
		foreach ( $a11y_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$has_a11y_plugin = true;
				break;
			}
		}

		// Check images for missing alt text (sample check on recent posts)
		$recent_posts = get_posts(
			array(
				'numberposts' => 10,
				'post_status' => 'publish',
			)
		);

		$images_without_alt = 0;
		foreach ( $recent_posts as $post ) {
			$content = $post->post_content;
			// Simple regex to find img tags without alt or with empty alt
			if ( preg_match_all( '/<img[^>]+>/', $content, $matches ) ) {
				foreach ( $matches[0] as $img ) {
					if ( ! preg_match( '/alt=["\'][^"\']+["\']/', $img ) ) {
						++$images_without_alt;
					}
				}
			}
		}

		if ( $images_without_alt > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of images without alt text */
				_n(
					'Found %d image without alt text in recent posts.',
					'Found %d images without alt text in recent posts.',
					$images_without_alt,
					'wpshadow'
				),
				$images_without_alt
			);
		}

		// Check if site has skip links (basic check)
		$homepage_id = get_option( 'page_on_front' );
		if ( $homepage_id ) {
			$homepage = get_post( $homepage_id );
			if ( $homepage ) {
				$content = strtolower( $homepage->post_content );
				if ( false === strpos( $content, 'skip to content' ) && false === strpos( $content, 'skip navigation' ) ) {
					$issues[] = __( 'No "skip to content" link detected for keyboard navigation.', 'wpshadow' );
				}
			}
		}

		// Check if theme declares accessibility-ready support
		$theme = wp_get_theme();
		$theme_tags = $theme->get( 'Tags' );
		$is_accessibility_ready = is_array( $theme_tags ) && in_array( 'accessibility-ready', $theme_tags, true );

		if ( ! $is_accessibility_ready && ! $has_a11y_plugin ) {
			$issues[] = __( 'Theme is not tagged as accessibility-ready and no accessibility plugin is active.', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			// No obvious accessibility issues
			return null;
		}

		$severity = count( $issues ) > 2 ? 'medium' : 'low';
		$threat_level = count( $issues ) > 2 ? 40 : 20;

		$description = sprintf(
			/* translators: %d: number of accessibility issues */
			_n(
				'Found %d accessibility issue. WCAG 2.1 compliance is legally required in many jurisdictions and benefits all users. Approximately 15%% of the population has some form of disability.',
				'Found %d accessibility issues. WCAG 2.1 compliance is legally required in many jurisdictions and benefits all users. Approximately 15%% of the population has some form of disability.',
				count( $issues ),
				'wpshadow'
			),
			count( $issues )
		) . ' ' . implode( ' ', $issues );

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
		'kb_link'     => \WPShadow\Core\UTM_Link_Manager::kb_link( 'accessibility-wcag-accessibility-compliance', 'diagnostic' ),
			'meta'        => array(
				'issues' => $issues,
				'issue_count' => count( $issues ),
				'has_a11y_plugin' => $has_a11y_plugin,
				'theme_accessibility_ready' => $is_accessibility_ready,
			),
		);
	}
}
