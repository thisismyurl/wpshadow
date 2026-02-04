<?php
/**
 * Tap Target Size Validation
 *
 * Validates that interactive elements are at least 44×44px for accurate tapping.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since      1.602.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tap Target Size Validation
 *
 * Checks that buttons, links, and form controls are at least 44×44px (Apple HIG)
 * or 48×48px (Material Design) to support accurate tapping on mobile devices.
 * WCAG 2.5.5 Level AAA requirement.
 *
 * @since 1.602.1430
 */
class Diagnostic_Tap_Target_Size extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'tap-targets-too-small';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Tap Target Size Validation';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Ensures mobile tap targets meet minimum 44×44px size';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Minimum tap target size in pixels (WCAG AAA).
	 *
	 * @var int
	 */
	const MIN_TAP_TARGET_SIZE = 44;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.602.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$small_targets = self::find_small_tap_targets();

		if ( empty( $small_targets ) ) {
			return null; // No issues found
		}

		$violation_count = count( $small_targets );

		// Determine severity
		if ( $violation_count > 20 ) {
			$severity = 'critical';
			$threat   = 70;
		} elseif ( $violation_count > 10 ) {
			$severity = 'high';
			$threat   = 65;
		} else {
			$severity = 'medium';
			$threat   = 50;
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %d: number of small tap targets */
				__( 'Found %d interactive elements below 44×44px minimum', 'wpshadow' ),
				$violation_count
			),
			'severity'        => $severity,
			'threat_level'    => $threat,
			'small_targets'   => array_slice( $small_targets, 0, 10 ), // Show first 10
			'total_violations' => $violation_count,
			'wcag_violation'  => '2.5.5 Target Size (Level AAA)',
			'user_impact'     => __( 'Difficult to tap correctly, frustration, mis-taps', 'wpshadow' ),
			'auto_fixable'    => true,
			'kb_link'         => 'https://wpshadow.com/kb/tap-target-size',
		);
	}

	/**
	 * Find all tap targets below minimum size.
	 *
	 * @since  1.602.1430
	 * @return array List of small tap targets.
	 */
	private static function find_small_tap_targets(): array {
		// Get page HTML
		$html = self::get_page_html();
		if ( ! $html ) {
			return array();
		}

		$small_targets = array();
		$min_size = self::MIN_TAP_TARGET_SIZE;

		// Parse HTML and check buttons
		$dom = self::parse_html( $html );
		if ( ! $dom ) {
			return array();
		}

		// Check buttons
		$buttons = $dom->getElementsByTagName( 'button' );
		foreach ( $buttons as $button ) {
			$size = self::get_element_size( $button );
			if ( $size['width'] < $min_size || $size['height'] < $min_size ) {
				$small_targets[] = array(
					'element'  => 'button',
					'size'     => sprintf( '%d×%d', $size['width'], $size['height'] ),
					'location' => self::get_element_context( $button ),
				);
			}
		}

		// Check links
		$links = $dom->getElementsByTagName( 'a' );
		foreach ( $links as $link ) {
			// Skip if link is inline in text
			if ( self::is_inline_text_link( $link ) ) {
				continue;
			}

			$size = self::get_element_size( $link );
			if ( $size['width'] < $min_size || $size['height'] < $min_size ) {
				$small_targets[] = array(
					'element'  => 'link',
					'size'     => sprintf( '%d×%d', $size['width'], $size['height'] ),
					'location' => self::get_element_context( $link ),
				);
			}
		}

		// Check form inputs
		$inputs = $dom->getElementsByTagName( 'input' );
		foreach ( $inputs as $input ) {
			$type = $input->getAttribute( 'type' );
			if ( in_array( $type, array( 'submit', 'button', 'checkbox', 'radio' ), true ) ) {
				$size = self::get_element_size( $input );
				if ( $size['width'] < $min_size || $size['height'] < $min_size ) {
					$small_targets[] = array(
						'element'  => 'input[' . $type . ']',
						'size'     => sprintf( '%d×%d', $size['width'], $size['height'] ),
						'location' => self::get_element_context( $input ),
					);
				}
			}
		}

		return $small_targets;
	}

	/**
	 * Get approximate element dimensions.
	 *
	 * @since  1.602.1430
	 * @param  \DOMElement $element Element to measure.
	 * @return array { Width and height in pixels. }
	 */
	private static function get_element_size( $element ): array {
		$style = $element->getAttribute( 'style' ) ?? '';
		
		// Extract width from style
		$width = 40;
		if ( preg_match( '/width\s*:\s*(\d+)px/i', $style, $matches ) ) {
			$width = (int) $matches[1];
		}

		// Extract height from style
		$height = 40;
		if ( preg_match( '/height\s*:\s*(\d+)px/i', $style, $matches ) ) {
			$height = (int) $matches[1];
		}

		// Check for min-width/min-height
		if ( preg_match( '/min-width\s*:\s*(\d+)px/i', $style, $matches ) ) {
			$width = max( $width, (int) $matches[1] );
		}
		if ( preg_match( '/min-height\s*:\s*(\d+)px/i', $style, $matches ) ) {
			$height = max( $height, (int) $matches[1] );
		}

		return array(
			'width'  => $width,
			'height' => $height,
		);
	}

	/**
	 * Check if link is inline text (exception to size rule).
	 *
	 * @since  1.602.1430
	 * @param  \DOMElement $link Link element.
	 * @return bool True if inline text link.
	 */
	private static function is_inline_text_link( $link ): bool {
		$parent = $link->parentNode;
		if ( ! $parent ) {
			return false;
		}

		// Check if parent is paragraph, span, or other text container
		$text_containers = array( 'p', 'span', 'div', 'li' );
		return in_array( strtolower( $parent->nodeName ), $text_containers, true );
	}

	/**
	 * Get context for element (location description).
	 *
	 * @since  1.602.1430
	 * @param  \DOMElement $element Element.
	 * @return string Context description.
	 */
	private static function get_element_context( $element ): string {
		$class = $element->getAttribute( 'class' ) ?? '';
		$id    = $element->getAttribute( 'id' ) ?? '';

		if ( $id ) {
			return "#{$id}";
		} elseif ( $class ) {
			$classes = explode( ' ', $class );
			return '.' . implode( '.', $classes );
		} else {
			return $element->nodeName;
		}
	}

	/**
	 * Parse HTML string.
	 *
	 * @since  1.602.1430
	 * @param  string $html HTML content.
	 * @return \DOMDocument|null Parsed DOM or null.
	 */
	private static function parse_html( string $html ): ?\DOMDocument {
		return Diagnostic_HTML_Helper::parse_html( $html );
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since  1.602.1430
	 * @return string|null Page HTML.
	 */
	private static function get_page_html(): ?string {
		return Diagnostic_HTML_Helper::fetch_homepage_html_cached(
			'wpshadow_page_html_analysis',
			HOUR_IN_SECONDS,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
