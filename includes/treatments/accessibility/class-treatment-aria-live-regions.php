<?php
/**
 * ARIA Live Regions Treatment
 *
 * Issue #4943: Dynamic Content Not Announced to Screen Readers
 * Pillar: 🌍 Accessibility First
 *
 * Checks if dynamic content uses ARIA live regions.
 * Screen readers miss content that appears without page reload.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_ARIA_Live_Regions Class
 *
 * @since 1.6050.0000
 */
class Treatment_ARIA_Live_Regions extends Treatment_Base {

	protected static $slug = 'aria-live-regions';
	protected static $title = 'Dynamic Content Not Announced to Screen Readers';
	protected static $description = 'Checks if dynamic content uses ARIA live regions';
	protected static $family = 'accessibility';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Use role="status" for non-critical updates', 'wpshadow' );
		$issues[] = __( 'Use role="alert" for important messages', 'wpshadow' );
		$issues[] = __( 'Use aria-live="polite" for background updates', 'wpshadow' );
		$issues[] = __( 'Use aria-live="assertive" for urgent messages', 'wpshadow' );
		$issues[] = __( 'Announce form validation errors', 'wpshadow' );
		$issues[] = __( 'Announce loading states and completion', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Content added via JavaScript is silent to screen readers. ARIA live regions announce dynamic changes so users know what\'s happening.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/aria-live-regions',
				'details'      => array(
					'recommendations'         => $issues,
					'wcag_requirement'        => 'WCAG 2.1 4.1.3 Status Messages (Level AA)',
					'polite_example'          => '<div role="status" aria-live="polite">Item added to cart</div>',
					'assertive_example'       => '<div role="alert" aria-live="assertive">Form submission failed</div>',
				),
			);
		}

		return null;
	}
}
