<?php
/**
 * No Table of Contents for Long-Form Content Diagnostic
 *
 * Detects when long-form content lacks table of contents,
 * reducing user experience and internal link opportunities.
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
 * Diagnostic: No Table of Contents for Long-Form Content
 *
 * Checks whether long-form articles (2000+ words) include
 * a table of contents for better navigation.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Table_Of_Contents_For_Long_Form_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-table-of-contents-long-form';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Table of Contents for Long Content';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether long-form content includes a table of contents';

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
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for TOC plugins
		$has_toc_plugin = is_plugin_active( 'easy-table-of-contents/easy-table-of-contents.php' ) ||
			is_plugin_active( 'table-of-contents-plus/table-of-contents.php' );

		// Check for long-form articles without TOC
		$posts = get_posts( array(
			'post_type'      => 'post',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		) );

		$long_form_without_toc = 0;

		foreach ( $posts as $post ) {
			$word_count = str_word_count( strip_tags( $post->post_content ) );
			
			// If post is 2000+ words but has no TOC
			if ( $word_count >= 2000 ) {
				$has_toc = strpos( $post->post_content, '<div class="ez-toc-container"' ) !== false ||
					strpos( $post->post_content, '[ez-toc]' ) !== false ||
					strpos( $post->post_content, '<!-- wp:toc' ) !== false;

				if ( ! $has_toc ) {
					$long_form_without_toc++;
				}
			}
		}

		if ( $long_form_without_toc > 0 && ! $has_toc_plugin ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You have ' . $long_form_without_toc . ' long-form articles (2000+ words) without a table of contents. Long articles are like books without chapter headings—readers get lost. A table of contents helps users jump to sections they care about and reduces bounce rate. It also creates more internal links (which help SEO) and helps Google understand your page structure.',
					'wpshadow'
				),
				'severity'      => 'low',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'long_form_count' => $long_form_without_toc,
				'business_impact' => array(
					'metric'         => 'User Experience & Engagement',
					'potential_gain' => 'Reduced bounce rate on long content',
					'roi_explanation' => 'Table of contents helps users navigate long content, reducing bounce rate and creating more internal link opportunities.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/table-of-contents-long-form',
			);
		}

		return null;
	}
}
