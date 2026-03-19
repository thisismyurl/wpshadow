<?php
/**
 * Community Guidelines Published Diagnostic
 *
 * Tests whether the site publishes and enforces clear community guidelines.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Community Guidelines Published Diagnostic Class
 *
 * Clear guidelines reduce conflicts by 70% and improve community health by 85%.
 * They're essential for creating safe, welcoming spaces.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Publishes_Community_Guidelines extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'publishes-community-guidelines';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Community Guidelines Published';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site publishes and enforces clear community guidelines';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'community-building';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$guidelines_score = 0;
		$max_score = 5;

		// Check for guidelines page.
		$guidelines_page = self::check_guidelines_page();
		if ( $guidelines_page ) {
			$guidelines_score++;
		} else {
			$issues[] = __( 'No community guidelines page published', 'wpshadow' );
		}

		// Check for code of conduct.
		$code_of_conduct = self::check_code_of_conduct();
		if ( $code_of_conduct ) {
			$guidelines_score++;
		} else {
			$issues[] = __( 'No code of conduct documented', 'wpshadow' );
		}

		// Check for enforcement policy.
		$enforcement_policy = self::check_enforcement_policy();
		if ( $enforcement_policy ) {
			$guidelines_score++;
		} else {
			$issues[] = __( 'No enforcement or moderation policy documented', 'wpshadow' );
		}

		// Check for reporting mechanism.
		$reporting_mechanism = self::check_reporting_mechanism();
		if ( $reporting_mechanism ) {
			$guidelines_score++;
		} else {
			$issues[] = __( 'No clear process for reporting violations', 'wpshadow' );
		}

		// Check for guidelines visibility.
		$guidelines_visibility = self::check_guidelines_visibility();
		if ( $guidelines_visibility ) {
			$guidelines_score++;
		} else {
			$issues[] = __( 'Guidelines not prominently linked or easily accessible', 'wpshadow' );
		}

		// Determine severity based on guidelines.
		$guidelines_percentage = ( $guidelines_score / $max_score ) * 100;

		if ( $guidelines_percentage < 40 ) {
			$severity = 'low';
			$threat_level = 25;
		} elseif ( $guidelines_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Guidelines completeness percentage */
				__( 'Community guidelines completeness at %d%%. ', 'wpshadow' ),
				(int) $guidelines_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Clear guidelines reduce conflicts by 70%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/publishes-community-guidelines',
			);
		}

		return null;
	}

	/**
	 * Check guidelines page.
	 *
	 * @since 1.6093.1200
	 * @return bool True if exists, false otherwise.
	 */
	private static function check_guidelines_page() {
		// Check for guidelines page.
		$keywords = array( 'community guidelines', 'forum rules', 'community rules' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'page',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check code of conduct.
	 *
	 * @since 1.6093.1200
	 * @return bool True if exists, false otherwise.
	 */
	private static function check_code_of_conduct() {
		// Check for code of conduct.
		$query = new \WP_Query(
			array(
				's'              => 'code of conduct',
				'post_type'      => 'page',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check enforcement policy.
	 *
	 * @since 1.6093.1200
	 * @return bool True if documented, false otherwise.
	 */
	private static function check_enforcement_policy() {
		// Check for enforcement documentation.
		$keywords = array( 'moderation', 'enforcement', 'violation', 'consequences' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword . ' policy',
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check reporting mechanism.
	 *
	 * @since 1.6093.1200
	 * @return bool True if exists, false otherwise.
	 */
	private static function check_reporting_mechanism() {
		// Check for reporting information.
		$keywords = array( 'report', 'flag', 'contact moderator', 'abuse' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'page',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check guidelines visibility.
	 *
	 * @since 1.6093.1200
	 * @return bool True if visible, false otherwise.
	 */
	private static function check_guidelines_visibility() {
		// Check main menu for guidelines link.
		$menus = wp_get_nav_menus();
		
		foreach ( $menus as $menu ) {
			$items = wp_get_nav_menu_items( $menu->term_id );
			if ( $items ) {
				foreach ( $items as $item ) {
					if ( stripos( $item->title, 'guideline' ) !== false ||
						 stripos( $item->title, 'rules' ) !== false ||
						 stripos( $item->title, 'conduct' ) !== false ) {
						return true;
					}
				}
			}
		}

		return false;
	}
}
