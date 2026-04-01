<?php
/**
 * No Influencer or Brand Ambassador Program Diagnostic
 *
 * Checks whether an influencer or ambassador program is visible.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Influencer Program Diagnostic
 *
 * Detects when there is no clear influencer or ambassador program. Authentic
 * recommendations build trust quickly and can expand reach.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Influencer_Or_Brand_Ambassador_Program extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-influencer-or-ambassador-program';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Influencer or Brand Ambassador Program Available';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether an influencer or ambassador program is documented';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$has_program = self::has_influencer_program();

		if ( ! $has_program ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'An influencer or ambassador program is not visible yet. Partners can introduce your brand to new audiences in a trusted way. Even a small program (5–10 ambassadors) can build social proof and grow word-of-mouth referrals.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/influencer-program?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'program_detected' => false,
					'recommendation'   => __( 'Create a simple ambassador page with benefits, requirements, and a signup form.', 'wpshadow' ),
					'program_elements' => self::get_program_elements(),
				),
			);
		}

		return null;
	}

	/**
	 * Determine whether an influencer program is visible.
	 *
	 * @since 0.6093.1200
	 * @return bool True when program indicators exist.
	 */
	private static function has_influencer_program(): bool {
		$keywords = array(
			'influencer',
			'ambassador',
			'brand ambassador',
			'creator program',
			'partner program',
		);

		if ( self::count_posts_by_keywords( $keywords ) > 0 ) {
			return true;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();
		$affiliate_plugins = array(
			'affiliate-wp/affiliate-wp.php',
			'affiliates-manager/affiliates-manager.php',
			'wp-affiliate-platform/wp-affiliate-platform.php',
		);

		foreach ( $affiliate_plugins as $plugin_file ) {
			if ( isset( $plugins[ $plugin_file ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Count posts/pages containing any keyword.
	 *
	 * @since 0.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return int Count of matching posts/pages.
	 */
	private static function count_posts_by_keywords( array $keywords ): int {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$matches = get_posts( array(
				'post_type'   => array( 'page', 'post' ),
				'numberposts' => 5,
				's'           => $keyword,
			) );

			$total += count( $matches );
		}

		return $total;
	}

	/**
	 * Provide ambassador program elements.
	 *
	 * @since 0.6093.1200
	 * @return array Program elements.
	 */
	private static function get_program_elements(): array {
		return array(
			__( 'Who the program is for and expected fit', 'wpshadow' ),
			__( 'Benefits (discounts, commissions, early access)', 'wpshadow' ),
			__( 'Clear guidelines and brand expectations', 'wpshadow' ),
			__( 'Application or signup form', 'wpshadow' ),
			__( 'Contact point for questions', 'wpshadow' ),
		);
	}
}
