<?php
/**
 * No Content Update Strategy Diagnostic
 *
 * Detects when old content is not being updated,
 * missing SEO freshness signals and accuracy.
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
 * Diagnostic: No Content Update Strategy
 *
 * Checks whether old content is being
 * regularly updated and refreshed.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Content_Update_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-content-update-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Update Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether content is regularly updated';

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
		// Check for posts with no modifications
		$old_posts = get_posts( array(
			'post_type'      => 'post',
			'posts_per_page' => 10,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		$outdated_count = 0;
		foreach ( $old_posts as $post ) {
			$modified = strtotime( $post->post_modified );
			$created = strtotime( $post->post_date );

			// Check if never updated and older than 1 year
			if ( $modified === $created && ( time() - $created ) > YEAR_IN_SECONDS ) {
				$outdated_count++;
			}
		}

		if ( $outdated_count >= 5 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__(
						'You have %d posts that haven\'t been updated in over a year. Google favors fresh content, and old information becomes outdated. Strategy: quarterly content audits, update top-performing posts, add new sections to existing content, refresh statistics/examples. Updated content often ranks better than brand new content (existing authority + freshness). Use "last updated" dates. Updating 10 old posts often outperforms creating 10 new ones.',
						'wpshadow'
					),
					$outdated_count
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'outdated_count' => $outdated_count,
				'business_impact' => array(
					'metric'         => 'Content Freshness & SEO',
					'potential_gain' => 'Updated content outperforms new content (existing authority + freshness)',
					'roi_explanation' => 'Updating old posts preserves SEO authority while gaining freshness signals. Often better ROI than new content.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/content-update-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
