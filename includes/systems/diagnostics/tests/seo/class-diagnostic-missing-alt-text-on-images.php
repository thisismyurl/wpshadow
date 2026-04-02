<?php
/**
 * Missing Alt Text on Images Diagnostic
 *
 * Detects when images lack alternative text (alt tags),
 * impacting SEO, accessibility, and user experience.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SEO
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Missing Alt Text on Images
 *
 * Checks whether images have descriptive alternative text
 * for SEO and accessibility purposes.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Missing_Alt_Text_On_Images extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-alt-text-images';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Alt Text on Images';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether images have descriptive alt text';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

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
		// Get all published posts
		$posts = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		) );

		$missing_alt_count = 0;
		$total_images = 0;

		foreach ( $posts as $post ) {
			// Count images
			preg_match_all( '/<img\s+[^>]*>/i', $post->post_content, $images );
			$total_images += count( $images[0] );

			// Count images missing alt text
			foreach ( $images[0] as $img ) {
				if ( ! preg_match( '/alt\s*=\s*["\'](.+?)["\'](?!\s*alt)/i', $img ) || 
					 preg_match( '/alt\s*=\s*["\']?\s*["\']?/i', $img ) ) {
					$missing_alt_count++;
				}
			}
		}

		if ( $missing_alt_count > 0 ) {
			$percentage = ( $total_images > 0 ) ? round( ( $missing_alt_count / $total_images ) * 100 ) : 0;

			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__(
						'About %d%% of your images are missing alt text (%d of %d). Alt text serves two important purposes: Google uses it to understand images (which helps your SEO), and people using screen readers need it to know what the image shows. Good alt text is descriptive but concise: "woman holding coffee cup" instead of "image.jpg". This is a win-win: better for accessibility and for search rankings.',
						'wpshadow'
					),
					$percentage,
					$missing_alt_count,
					$total_images
				),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'missing_alt_count' => $missing_alt_count,
				'total_images'  => $total_images,
				'business_impact' => array(
					'metric'         => 'SEO & Accessibility',
					'potential_gain' => 'Better image search rankings',
					'roi_explanation' => 'Alt text helps both Google indexing and users with screen readers. Google uses images to understand page context, improving overall rankings.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/alt-text-images',
			);
		}

		return null;
	}
}
