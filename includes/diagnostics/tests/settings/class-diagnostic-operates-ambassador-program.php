<?php
/**
 * Ambassador Program Diagnostic
 *
 * Tests whether the site formally recognizes and supports community advocates through an ambassador program.
 *
 * @since   1.6034.0510
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ambassador Program Diagnostic Class
 *
 * Ambassador programs amplify reach by 500% and reduce acquisition costs by 70%.
 * Formal advocate programs turn enthusiasts into brand champions.
 *
 * @since 1.6034.0510
 */
class Diagnostic_Operates_Ambassador_Program extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'operates-ambassador-program';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Ambassador Program';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site formally recognizes and supports community advocates through an ambassador program';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'community-building';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6034.0510
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$ambassador_score = 0;
		$max_score = 5;

		// Check for ambassador program.
		$ambassador_program = self::check_ambassador_program();
		if ( $ambassador_program ) {
			$ambassador_score++;
		} else {
			$issues[] = __( 'No ambassador or advocate program documented', 'wpshadow' );
		}

		// Check for ambassador benefits.
		$ambassador_benefits = self::check_ambassador_benefits();
		if ( $ambassador_benefits ) {
			$ambassador_score++;
		} else {
			$issues[] = __( 'No clear benefits or perks for ambassadors', 'wpshadow' );
		}

		// Check for ambassador application.
		$ambassador_application = self::check_ambassador_application();
		if ( $ambassador_application ) {
			$ambassador_score++;
		} else {
			$issues[] = __( 'No process for applying to become an ambassador', 'wpshadow' );
		}

		// Check for ambassador content.
		$ambassador_content = self::check_ambassador_content();
		if ( $ambassador_content ) {
			$ambassador_score++;
		} else {
			$issues[] = __( 'Not showcasing ambassador contributions', 'wpshadow' );
		}

		// Check for ambassador support.
		$ambassador_support = self::check_ambassador_support();
		if ( $ambassador_support ) {
			$ambassador_score++;
		} else {
			$issues[] = __( 'No dedicated support or resources for ambassadors', 'wpshadow' );
		}

		// Determine severity based on ambassador program.
		$ambassador_percentage = ( $ambassador_score / $max_score ) * 100;

		if ( $ambassador_percentage < 40 ) {
			$severity = 'low';
			$threat_level = 20;
		} elseif ( $ambassador_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 10;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Ambassador program strength percentage */
				__( 'Ambassador program strength at %d%%. ', 'wpshadow' ),
				(int) $ambassador_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Ambassador programs amplify reach by 500%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/operates-ambassador-program',
			);
		}

		return null;
	}

	/**
	 * Check ambassador program.
	 *
	 * @since  1.6034.0510
	 * @return bool True if exists, false otherwise.
	 */
	private static function check_ambassador_program() {
		// Check for ambassador program content.
		$keywords = array( 'ambassador', 'advocate', 'champion', 'evangelist' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword . ' program',
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
	 * Check ambassador benefits.
	 *
	 * @since  1.6034.0510
	 * @return bool True if documented, false otherwise.
	 */
	private static function check_ambassador_benefits() {
		// Check for benefits documentation.
		$keywords = array( 'benefits', 'perks', 'rewards', 'exclusive access' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword . ' ambassador',
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
	 * Check ambassador application.
	 *
	 * @since  1.6034.0510
	 * @return bool True if exists, false otherwise.
	 */
	private static function check_ambassador_application() {
		// Check for application process.
		$keywords = array( 'apply', 'join', 'become an ambassador', 'sign up' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword . ' ambassador',
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
	 * Check ambassador content.
	 *
	 * @since  1.6034.0510
	 * @return bool True if showcased, false otherwise.
	 */
	private static function check_ambassador_content() {
		// Check for ambassador showcases.
		$keywords = array( 'meet our ambassadors', 'featured ambassador', 'ambassador spotlight' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
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
	 * Check ambassador support.
	 *
	 * @since  1.6034.0510
	 * @return bool True if provided, false otherwise.
	 */
	private static function check_ambassador_support() {
		// Check for support resources.
		$keywords = array( 'ambassador kit', 'resources', 'toolkit', 'guidelines' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword . ' ambassador',
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
}
