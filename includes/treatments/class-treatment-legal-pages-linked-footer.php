<?php
/**
 * Treatment: Link legal pages in a footer-style menu
 *
 * This treatment ensures the privacy policy page is linked from a footer-style
 * navigation menu. It reuses the privacy-link visibility automation because
 * both diagnostics converge on the same user-visible outcome.
 *
 * Undo: delegates to the privacy-policy-links-visible treatment restore path.
 *
 * @package WPShadow
 * @since   0.7056.0200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ensures legal pages are represented in footer navigation.
 */
class Treatment_Legal_Pages_Linked_Footer extends Treatment_Base {

	/** @var string */
	protected static $slug = 'legal-pages-linked-footer';

	/** @return string */
	public static function get_risk_level(): string {
		return 'moderate';
	}

	/**
	 * Reuse the privacy-policy menu automation.
	 *
	 * @return array
	 */
	public static function apply(): array {
		$result = Treatment_Privacy_Policy_Links_Visible::apply();

		if ( ! empty( $result['success'] ) ) {
			$result['message'] = __( 'A footer-style navigation path for legal pages was created or updated using the privacy policy link.', 'wpshadow' );
		}

		return $result;
	}

	/**
	 * Undo the footer legal-link changes.
	 *
	 * @return array
	 */
	public static function undo(): array {
		return Treatment_Privacy_Policy_Links_Visible::undo();
	}
}