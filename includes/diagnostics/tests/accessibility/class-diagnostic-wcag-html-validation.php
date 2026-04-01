<?php
/**
 * WCAG 4.1.1 HTML Validation Diagnostic
 *
 * Validates that HTML is well-formed for assistive technology compatibility.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCAG HTML Validation Diagnostic Class
 *
 * Checks for valid HTML structure (WCAG 4.1.1 Level A).
 *
 * @since 0.6093.1200
 */
class Diagnostic_WCAG_HTML_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wcag-html-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTML Validation (WCAG 4.1.1)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates HTML structure for assistive technology compatibility';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check theme header for DOCTYPE.
		$theme_header = get_template_directory() . '/header.php';
		if ( file_exists( $theme_header ) ) {
			$content = file_get_contents( $theme_header );

			// Check for DOCTYPE declaration.
			if ( ! preg_match( '/<!DOCTYPE\s+html>/i', $content ) ) {
				$issues[] = __( 'Missing or incorrect DOCTYPE declaration in theme header. Should be <!DOCTYPE html>', 'wpshadow' );
			}

			// Check for duplicate IDs (basic check).
			if ( preg_match_all( '/id=["\']([^"\'>]+)["\']/', $content, $matches ) ) {
				$ids        = $matches[1];
				$duplicates = array_diff_assoc( $ids, array_unique( $ids ) );
				if ( ! empty( $duplicates ) ) {
					$issues[] = sprintf(
						/* translators: %s: comma-separated list of duplicate IDs */
						__( 'Found duplicate IDs in theme header: %s. Each ID must be unique', 'wpshadow' ),
						implode( ', ', array_unique( $duplicates ) )
					);
				}
			}

			// Check for proper HTML structure.
			if ( ! preg_match( '/<html[^>]*>/', $content ) ) {
				$issues[] = __( 'Missing <html> opening tag in theme header', 'wpshadow' );
			}

			if ( ! preg_match( '/<head[^>]*>/', $content ) ) {
				$issues[] = __( 'Missing <head> opening tag in theme header', 'wpshadow' );
			}

			if ( ! preg_match( '/<body[^>]*>/', $content ) ) {
				$issues[] = __( 'Missing <body> opening tag in theme header', 'wpshadow' );
			}

			// Check for unclosed tags (basic validation).
			$tag_pattern = '/<([a-z]+)[^>]*(?<!\/?)>/i';
			if ( preg_match_all( $tag_pattern, $content, $opening_tags ) ) {
				$close_pattern = '/<\/([a-z]+)>/i';
				if ( preg_match_all( $close_pattern, $content, $closing_tags ) ) {
					$void_elements = array( 'img', 'br', 'hr', 'input', 'meta', 'link', 'col', 'area', 'base', 'embed', 'param', 'source', 'track' );

					$opens  = array_diff( $opening_tags[1], $void_elements );
					$closes = $closing_tags[1];

					$opens_count  = array_count_values( $opens );
					$closes_count = array_count_values( $closes );

					foreach ( $opens_count as $tag => $count ) {
						$close_count = isset( $closes_count[ $tag ] ) ? $closes_count[ $tag ] : 0;
						if ( $count > $close_count ) {
							$issues[] = sprintf(
								/* translators: %s: HTML tag name */
								__( 'Potentially unclosed <%s> tag in theme header', 'wpshadow' ),
								$tag
							);
						}
					}
				}
			}
		}

		// Check footer for closing tags.
		$theme_footer = get_template_directory() . '/footer.php';
		if ( file_exists( $theme_footer ) ) {
			$content = file_get_contents( $theme_footer );

			if ( ! preg_match( '/<\/body\s*>/', $content ) ) {
				$issues[] = __( 'Missing </body> closing tag in theme footer', 'wpshadow' );
			}

			if ( ! preg_match( '/<\/html\s*>/', $content ) ) {
				$issues[] = __( 'Missing </html> closing tag in theme footer', 'wpshadow' );
			}
		}

		// Check recent posts for common HTML errors.
		$posts = get_posts(
			array(
				'numberposts' => 5,
				'post_status' => 'publish',
				'post_type'   => 'any',
			)
		);

		$malformed_count = 0;
		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Check for duplicate IDs in content.
			if ( preg_match_all( '/id=["\']([^"\'>]+)["\']/', $content, $matches ) ) {
				$ids        = $matches[1];
				$duplicates = array_diff_assoc( $ids, array_unique( $ids ) );
				if ( ! empty( $duplicates ) ) {
					++$malformed_count;
				}
			}

			// Check for improperly nested tags.
			if ( preg_match( '/<strong[^>]*><em[^>]*>[^<]*<\/strong[^>]*><\/em[^>]*>|<em[^>]*><strong[^>]*>[^<]*<\/em[^>]*><\/strong[^>]*>/i', $content ) ) {
				++$malformed_count;
			}
		}

		if ( $malformed_count > 2 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with HTML errors */
				__( 'Found %d posts with potential HTML errors (duplicate IDs or improperly nested tags)', 'wpshadow' ),
				$malformed_count
			);
		}

		// Recommend validation plugins.
		$validation_plugins = array(
			'accessibility-checker/accessibility-checker.php',
			'wp-accessibility/wp-accessibility.php',
		);

		$has_validation_plugin = false;
		$active_plugins        = get_option( 'active_plugins', array() );

		foreach ( $validation_plugins as $plugin ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$has_validation_plugin = true;
				break;
			}
		}

		if ( ! $has_validation_plugin && ! empty( $issues ) ) {
			$issues[] = __( 'Consider installing an accessibility checker plugin to catch HTML validation errors automatically', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Valid HTML is like speaking grammatically correct sentences. While humans can understand broken grammar, assistive technologies like screen readers are more strict. Missing closing tags, duplicate IDs, or improperly nested elements can confuse these tools, causing them to skip content or misinterpret your page structure. It\'s like trying to read a book where random pages are glued together—technically readable but frustrating and error-prone.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wcag-html-validation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
