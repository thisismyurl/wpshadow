<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Business Hours Visible?
 *
 * Target Persona: Local Business Owner (Bakery/Plumber/Insurance)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Business_Hours_Display extends Diagnostic_Base {
	protected static $slug        = 'business-hours-display';
	protected static $title       = 'Business Hours Visible?';
	protected static $description = 'Checks if operating hours are prominently displayed.';

	public static function check(): ?array {
		$pages = get_posts(
			array(
				'post_type'      => array( 'page', 'post' ),
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);

		$hours_keywords = array( 'hours', 'open', 'closed', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'am', 'pm' );
		$has_hours      = false;

		foreach ( $pages as $page ) {
			$content_lower = strtolower( $page->post_content );
			$matches       = 0;
			foreach ( $hours_keywords as $keyword ) {
				if ( strpos( $content_lower, $keyword ) !== false ) {
					++$matches;
				}
			}
			if ( $matches >= 3 ) {
				$has_hours = true;
				break;
			}
		}

		if ( $has_hours ) {
			return null;
		}

		return array(
			'id'            => static::$slug,
			'title'         => __( 'Business hours not found', 'wpshadow' ),
			'description'   => __( 'Customers need to know when you\'re open. Add your hours to your contact page.', 'wpshadow' ),
			'severity'      => 'low',
			'category'      => 'general',
			'kb_link'       => 'https://wpshadow.com/kb/business-hours-display/',
			'training_link' => 'https://wpshadow.com/training/business-hours-display/',
			'auto_fixable'  => false,
			'threat_level'  => 35,
		);
	}

}
