<?php
/**
 * Content Uses English Idioms Diagnostic
 *
 * Checks if content uses culture-specific idioms or expressions that don't translate.
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
 * Content Idioms Diagnostic
 *
 * Detects English idioms and culture-specific expressions in content that don't
 * translate well and confuse non-native speakers. Clear, literal language helps
 * everyone—especially international audiences and translation tools.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Content_Uses_English_Idioms extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-uses-english-idioms';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Uses Clear, Universal Language';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if content avoids English idioms and culture-specific expressions that confuse international users';

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
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$idioms_found = self::scan_content_for_idioms();

		if ( ! empty( $idioms_found ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of idioms found */
					__( 'Found %d English idioms or culture-specific expressions that confuse non-native speakers and translation tools. Example: "break a leg" doesn\'t translate, "piece of cake" is confusing. Use clear, literal language instead: "easy to use" instead of "piece of cake".', 'wpshadow' ),
					count( $idioms_found )
				),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/global-language-idioms',
				'details'     => array(
					'found_count'     => count( $idioms_found ),
					'examples'        => array_slice( $idioms_found, 0, 5 ),
					'recommendation'  => __( 'Replace idioms with clear, literal language that translates well globally.', 'wpshadow' ),
				),
			);
		}

		return null; // No issue found
	}

	/**
	 * Scan content for common English idioms
	 *
	 * @since 1.6093.1200
	 * @return array Array of found idioms with context
	 */
	private static function scan_content_for_idioms(): array {
		$found = array();

		// Common English idioms that don't translate
		$idioms = array(
			'break a leg'           => 'luck (theater term)',
			'piece of cake'         => 'easy',
			'hit the nail on head'  => 'correct/accurate',
			'ballpark figure'       => 'approximate number',
			'out of the park'       => 'excellent',
			'knock it out of the park' => 'succeed greatly',
			'slam dunk'             => 'certain success',
			'home run'              => 'great success',
			'touchdown'             => 'significant achievement',
			'raining cats and dogs' => 'raining heavily',
			'break the ice'         => 'start conversation',
			'under the weather'     => 'feeling sick',
			'spill the beans'       => 'reveal secret',
			'let the cat out of the bag' => 'reveal secret',
			'caught red-handed'     => 'caught in act',
			'bite the bullet'       => 'endure something difficult',
			'take the bait'         => 'accept offer',
			'pull someone\'s leg'   => 'tease/joke',
			'jump the gun'          => 'start early/prematurely',
			'miss the boat'         => 'miss opportunity',
			'push the envelope'     => 'extend limits',
			'low-hanging fruit'     => 'easy target',
			'game-changer'          => 'transformative',
		);

		// Get main content areas
		$content = self::get_scannable_content();

		foreach ( $idioms as $idiom => $meaning ) {
			if ( stripos( $content, $idiom ) !== false ) {
				$found[] = array(
					'idiom'  => $idiom,
					'meaning' => $meaning,
					'replacement' => __( 'Use literal alternative', 'wpshadow' ),
				);
			}
		}

		return $found;
	}

	/**
	 * Get main content to scan (homepage, about page, main services)
	 *
	 * @since 1.6093.1200
	 * @return string Combined scannable content
	 */
	private static function get_scannable_content(): string {
		$content = '';

		// Get homepage content
		$content .= wp_remote_retrieve_body( wp_remote_get( home_url( '/' ) ) ) . ' ';

		// Get about page if exists
		$query = new \WP_Query( array(
			'post_type'              => 'page',
			'title'                  => 'About',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		) );
		$about = ! empty( $query->posts ) ? $query->posts[0] : null;
		if ( $about ) {
			$content .= $about->post_content . ' ';
		}

		// Get site description
		$content .= get_bloginfo( 'description' ) . ' ';

		return strtolower( wp_strip_all_tags( $content ) );
	}
}
