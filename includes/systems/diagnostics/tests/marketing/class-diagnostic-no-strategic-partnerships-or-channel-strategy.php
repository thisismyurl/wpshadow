<?php
/**
 * No Strategic Partnerships or Channel Strategy Diagnostic
 *
 * Checks if strategic partnerships are being leveraged.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Strategic Partnerships Diagnostic
 *
 * Strategic partnerships can accelerate growth by 2-10x by leveraging
 * complementary audiences and capabilities.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Strategic_Partnerships_Or_Channel_Strategy extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-strategic-partnerships-channel-strategy';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Strategic Partnerships/Channel Strategy';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if strategic partnerships are being leveraged';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'business-performance';

	/**
	 * Run diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_partnership_strategy() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No strategic partnership strategy detected. Growing alone is slow. Strategic partnerships accelerate growth 2-10x by accessing complementary audiences. Types: 1) Reseller partners (who sell your product), 2) Technology partners (integrate your product with theirs), 3) Content partners (create content together), 4) Distribution partners (they distribute to their audience), 5) Affiliate partners (commission-based growth). Ideal partner: Similar target customer, different solution, non-competing. Partnership structure: What do they get? What do we get? How do we measure success? How long? How do we support them? Success: Partner brings customers/revenue, we bring visibility/credibility.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/strategic-partnerships-channel-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'issue'               => __( 'No strategic partnership strategy detected', 'wpshadow' ),
					'recommendation'      => __( 'Identify and develop strategic partnerships', 'wpshadow' ),
					'business_impact'     => __( 'Missing 2-10x growth acceleration from partnerships', 'wpshadow' ),
					'partnership_types'   => self::get_partnership_types(),
					'partner_criteria'    => self::get_partner_criteria(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if partnership strategy exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if strategy detected, false otherwise.
	 */
	private static function has_partnership_strategy() {
		// Check for partnership content
		$partnership_posts = self::count_posts_by_keywords(
			array(
				'partnership',
				'partner',
				'integration',
				'reseller',
				'channel',
			)
		);

		if ( $partnership_posts > 0 ) {
			return true;
		}

		// Check for partnership/integrations
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$partner_keywords = array(
			'integration',
			'partner',
			'zapier',
			'api',
		);

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $partner_keywords as $keyword ) {
				if ( false !== strpos( $plugin_name, $keyword ) ) {
					if ( is_plugin_active( $plugin_file ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since 0.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return int Number of matching posts.
	 */
	private static function count_posts_by_keywords( $keywords ) {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'posts_per_page' => 1,
					'post_type'      => array( 'post', 'page' ),
					'post_status'    => 'publish',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				++$total;
			}
		}

		return $total;
	}

	/**
	 * Get partnership types.
	 *
	 * @since 0.6093.1200
	 * @return array Partnership types with descriptions.
	 */
	private static function get_partnership_types() {
		return array(
			'reseller'    => array(
				'type'       => __( 'Reseller Partners', 'wpshadow' ),
				'what_they_do' => __( 'They sell your product to their customers', 'wpshadow' ),
				'what_they_get' => __( 'Commission per sale (typically 20-40%)', 'wpshadow' ),
				'best_for'   => __( 'B2B products, products with sales cycles', 'wpshadow' ),
			),
			'technology'  => array(
				'type'       => __( 'Technology Partners', 'wpshadow' ),
				'what_they_do' => __( 'Integrate with their product, enhance both', 'wpshadow' ),
				'what_they_get' => __( 'Access to each other\'s customers', 'wpshadow' ),
				'best_for'   => __( 'Complementary products (CRM + email, for example)', 'wpshadow' ),
			),
			'content'     => array(
				'type'       => __( 'Content Partners', 'wpshadow' ),
				'what_they_do' => __( 'Create content together (webinars, guides, courses)', 'wpshadow' ),
				'what_they_get' => __( 'Access to each other\'s audience', 'wpshadow' ),
				'best_for'   => __( 'Thought leadership, audience building', 'wpshadow' ),
			),
			'distribution' => array(
				'type'       => __( 'Distribution Partners', 'wpshadow' ),
				'what_they_do' => __( 'They distribute your product to their audience', 'wpshadow' ),
				'what_they_get' => __( 'Commission or revenue share', 'wpshadow' ),
				'best_for'   => __( 'Products with complementary audiences', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get ideal partner criteria.
	 *
	 * @since 0.6093.1200
	 * @return array Ideal partner criteria.
	 */
	private static function get_partner_criteria() {
		return array(
			'target_customer' => __( '✅ Same target customer (right audience)', 'wpshadow' ),
			'non_competing'   => __( '✅ Different solution (don\'t directly compete)', 'wpshadow' ),
			'complementary'   => __( '✅ Complementary solution (work well together)', 'wpshadow' ),
			'quality'         => __( '✅ High quality (reflect well on both)', 'wpshadow' ),
			'audience_size'   => __( '✅ Significant audience (brings real volume)', 'wpshadow' ),
			'values_aligned'  => __( '✅ Values aligned (similar brand, culture)', 'wpshadow' ),
			'success_focus'   => __( '✅ Success-focused (will invest in partnership)', 'wpshadow' ),
		);
	}
}
