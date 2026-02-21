<?php
/**
 * Mobile Tap Target Spacing
 *
 * Ensures adequate spacing (8px minimum) between interactive elements.
 *
 * @package    WPShadow
 * @subpackage Treatments\Touch
 * @since      1.602.1430
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_HTML_Helper;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Tap Target Spacing
 *
 * Validates that interactive elements have adequate spacing between them
 * to prevent accidental taps. WCAG 2.5.8 Level AA requirement.
 *
 * @since 1.602.1430
 */
class Treatment_Mobile_Tap_Target_Spacing extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-tap-target-spacing';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Tap Target Spacing Validation';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Ensures 8px+ spacing between tap targets';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'touch-interaction';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Tap_Target_Spacing' );
	}

	/**
	 * Find tap target spacing violations.
	 *
	 * @since  1.602.1430
	 * @return array Violations found.
	 */
	private static function find_spacing_violations(): array {
		$html = self::get_page_html();
		if ( ! $html ) {
			return array();
		}

		try {
			$dom = Treatment_HTML_Helper::parse_html( $html );
			if ( ! $dom ) {
				return array();
			}

			$xpath = Treatment_HTML_Helper::create_xpath( $dom );
			$elements = $xpath->query( '//button | //a | //input[@type="button"] | //input[@type="checkbox"] | //input[@type="radio"]' );

			$interactive = array();
			foreach ( $elements as $element ) {
				$pos = self::get_element_position( $element );
				if ( $pos ) {
					$interactive[] = $pos;
				}
			}

			// Find pairs with insufficient spacing
			$violations = array();
			for ( $i = 0; $i < count( $interactive ); $i++ ) {
				for ( $j = $i + 1; $j < count( $interactive ); $j++ ) {
					$distance = self::calculate_distance( $interactive[ $i ], $interactive[ $j ] );
					if ( $distance < 8 ) { // 8px minimum spacing
						$violations[] = array(
							'element1'  => $interactive[ $i ]['tag'],
							'element2'  => $interactive[ $j ]['tag'],
							'distance'  => round( $distance, 1 ),
							'required'  => 8,
							'location1' => $interactive[ $i ]['text'],
							'location2' => $interactive[ $j ]['text'],
						);
					}
				}
			}

			return $violations;
		} catch ( \Exception $e ) {
			return array();
		}
	}

	/**
	 * Get element position and size.
	 *
	 * @since  1.602.1430
	 * @param  \DOMElement $element DOM element.
	 * @return array|null Position array or null.
	 */
	private static function get_element_position( \DOMElement $element ): ?array {
		$style = $element->getAttribute( 'style' ) ?? '';
		$class = $element->getAttribute( 'class' ) ?? '';

		// Extract position from style attribute (approximate)
		$position = array(
			'tag'  => $element->tagName,
			'text' => substr( $element->textContent, 0, 30 ),
			'x'    => 0,
			'y'    => 0,
		);

		// Try to extract from inline styles
		if ( preg_match( '/left\s*:\s*([0-9]+)px/', $style, $matches ) ) {
			$position['x'] = (int) $matches[1];
		}
		if ( preg_match( '/top\s*:\s*([0-9]+)px/', $style, $matches ) ) {
			$position['y'] = (int) $matches[1];
		}

		return $position;
	}

	/**
	 * Calculate center-to-center distance between elements.
	 *
	 * @since  1.602.1430
	 * @param  array $pos1 First position.
	 * @param  array $pos2 Second position.
	 * @return float Distance in pixels.
	 */
	private static function calculate_distance( array $pos1, array $pos2 ): float {
		$dx = $pos2['x'] - $pos1['x'];
		$dy = $pos2['y'] - $pos1['y'];
		return sqrt( ( $dx * $dx ) + ( $dy * $dy ) );
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since  1.602.1430
	 * @return string|null HTML content.
	 */
	private static function get_page_html(): ?string {
		return Treatment_HTML_Helper::fetch_homepage_html(
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
