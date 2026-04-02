<?php
/**
 * No Short-Form Content Diagnostic
 *
 * Detects lack of quick-read content, missing opportunities for
 * social sharing and quick engagement.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Short-Form Content Diagnostic Class
 *
 * Analyzes content mix to ensure inclusion of short-form posts
 * that serve quick-consumption audiences.
 *
 * **Why This Matters:**
 * - 55% of visitors spend < 15 seconds on pages
 * - Short content drives social sharing
 * - Better for mobile consumption
 * - Increases publishing frequency
 * - Complements long-form pillar content
 *
 * **Short-Form Content Types:**
 * - Quick tips (300-500 words)
 * - News updates
 * - List posts
 * - Infographic summaries
 * - Product announcements
 *
 * **Ideal Content Mix:**
 * - 30% short-form (< 600 words)
 * - 50% medium (600-1500 words)
 * - 20% long-form (1500+ words)
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Short_Form_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-short-form-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Short-Form Content';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'All content is long-form; missing quick-read posts for social sharing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if no short-form content, null otherwise.
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

		$short_form_percentage = ( $short_form_count / count( $posts ) ) * 100;

		// Issue if < 15% short-form content
		if ( $short_form_percentage >= 15 ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: percentage of short-form content */
				__( 'Only %s%% of content is short-form (< 600 words). Add quick-read posts to improve content mix and social sharing.', 'wpshadow' ),
				number_format_i18n( $short_form_percentage, 1 )
			),
			'severity'     => 'low',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-mix',
			'details'      => array(
				'short_form_count'      => $short_form_count,
				'short_form_percentage' => round( $short_form_percentage, 1 ),
				'medium_count'          => $medium_count,
				'long_form_count'       => $long_form_count,
				'total_posts'           => count( $posts ),
				'recommendation'        => 'Aim for 30% short-form content',
			),
		);
	}
}
