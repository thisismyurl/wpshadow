<?php
/**
 * No Long-Form Content Treatment
 *
 * Detects lack of comprehensive pillar content, missing SEO and
 * authority-building opportunities.
 *
 * @package    WPShadow
 * @subpackage Treatments\Content
 * @since      1.6034.2209
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Long-Form Content Treatment Class
 *
 * Analyzes content strategy to ensure inclusion of comprehensive
 * long-form content that establishes authority and drives SEO.
 *
 * **Why This Matters:**
 * - Long-form content ranks 77% better in Google
 * - Average #1 result is 1,890 words
 * - Builds topic authority and trust
 * - Generates more backlinks (3.5x more)
 * - Keeps visitors engaged longer
 *
 * **Long-Form Benefits:**
 * - Ranks for more keywords
 * - Higher social shares
 * - Better conversion rates
 * - Establishes expertise
 * - Comprehensive answers to queries
 *
 * **Ideal Content Mix:**
 * - 30% short-form (< 600 words)
 * - 50% medium (600-1500 words)
 * - 20% long-form (1500+ words)
 *
 * @since 1.6034.2209
 */
class Treatment_No_Long_Form_Content extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-long-form-content';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No Long-Form Content';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Missing comprehensive pillar content that drives SEO and authority';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the treatment check
	 *
	 * @since  1.6034.2209
	 * @return array|null Finding array if no long-form content, null otherwise.
	 */
	public static function check() {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
			)
		);

		if ( count( $posts ) < 10 ) {
			return null; // Need sufficient sample size
		}

		$short_form_count = 0;
		$medium_count = 0;
		$long_form_count = 0;

		foreach ( $posts as $post ) {
			$word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );

			if ( $word_count < 600 ) {
				$short_form_count++;
			} elseif ( $word_count < 1500 ) {
				$medium_count++;
			} else {
				$long_form_count++;
			}
		}

		$long_form_percentage = ( $long_form_count / count( $posts ) ) * 100;

		// Issue if < 10% long-form content
		if ( $long_form_percentage >= 10 ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: percentage of long-form content */
				__( 'Only %s%% of content is long-form (1500+ words). Create comprehensive pillar posts to improve SEO and authority.', 'wpshadow' ),
				number_format_i18n( $long_form_percentage, 1 )
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-long-form',
			'details'      => array(
				'long_form_count'      => $long_form_count,
				'long_form_percentage' => round( $long_form_percentage, 1 ),
				'medium_count'         => $medium_count,
				'short_form_count'     => $short_form_count,
				'total_posts'          => count( $posts ),
				'recommendation'       => 'Aim for 20% long-form content (1500+ words)',
			),
		);
	}
}
