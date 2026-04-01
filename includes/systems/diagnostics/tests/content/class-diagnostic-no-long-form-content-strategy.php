<?php
/**
 * No Long-Form Content Strategy Diagnostic
 *
 * Detects when content is too short,
 * missing ranking power and depth opportunities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Long-Form Content Strategy
 *
 * Checks whether content is sufficiently
 * detailed for SEO and reader value.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Long_Form_Content_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-long-form-content-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Long-Form Content Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether content is sufficiently detailed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check average post length
		$posts = get_posts( array(
			'post_type'      => 'post',
			'posts_per_page' => 10,
			'post_status'    => 'publish',
		) );

		if ( empty( $posts ) ) {
			return null;
		}

		$total_words = 0;
		foreach ( $posts as $post ) {
			$word_count = str_word_count( strip_tags( $post->post_content ) );
			$total_words += $word_count;
		}

		$average_words = $total_words / count( $posts );

		if ( $average_words < 1000 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__(
						'Your average post is only %d words, which limits ranking power. Long-form content (1500-3000 words) ranks better because: more comprehensive coverage of topic, naturally includes more keywords, gives readers more value. Studies show: 1500+ word content ranks 1st page 50-70%% better than short content. Long-form also enables more internal linking, featured snippets, deeper insights. Doesn\'t mean all content must be long, but mix of long-form improves overall performance.',
						'wpshadow'
					),
					round( $average_words )
				),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'average_words' => round( $average_words ),
				'business_impact' => array(
					'metric'         => 'SEO Ranking Power',
					'potential_gain' => '+50-70% better ranking for 1500+ word content',
					'roi_explanation' => 'Long-form content (1500-3000 words) ranks 50-70% better than short content for competitive keywords.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/long-form-content-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
