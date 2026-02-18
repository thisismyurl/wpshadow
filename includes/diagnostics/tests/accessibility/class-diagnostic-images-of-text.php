<?php
/**
 * Images of Text Diagnostic
 *
 * Issue #4756: Images of Text Used Instead of Real Text
 * Pillar: 🌍 Accessibility First
 *
 * Checks if site uses images of text instead of real text.
 * Screen readers can't read text in images, and it doesn't scale/translate.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6036.1440
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Images_Of_Text Class
 *
 * Checks for use of images of text instead of real HTML text.
 *
 * @since 1.6036.1440
 */
class Diagnostic_Images_Of_Text extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'images-of-text';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Images of Text Used Instead of Real Text';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if site uses images of text that should be real HTML text';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6036.1440
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$issues[] = __( 'Replace image-based headings with HTML <h1>, <h2>, etc.', 'wpshadow' );
		$issues[] = __( 'Use CSS to style real text instead of image text', 'wpshadow' );
		$issues[] = __( 'Web fonts let you use custom typography without images', 'wpshadow' );
		$issues[] = __( 'Images of text don\'t scale, translate, or work with screen readers', 'wpshadow' );
		$issues[] = __( 'Exception: logos and essential brand imagery', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site might use images of text (like PNG headings or JPEG quotes) instead of real HTML text. This causes several problems: 1) Screen readers can\'t read the text (unless alt text duplicates it exactly), 2) Text doesn\'t scale when users zoom (it pixelates), 3) Can\'t select or copy text, 4) Doesn\'t translate with browser translation tools, 5) Poor SEO (search engines read text, not images). Modern CSS can recreate almost any text design without images. The only exceptions are logos and essential brand imagery where text is part of the graphic design.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/images-of-text',
				'details'      => array(
					'recommendations'     => $issues,
					'wcag_requirement'    => 'WCAG 2.1 1.4.5 Images of Text (Level AA)',
					'affected_users'      => 'Blind/low vision users (10%), translation users, zoom users',
					'css_alternative'     => 'Use @font-face for custom fonts + CSS styling',
					'common_culprits'     => 'Fancy headings, quotes, buttons with special fonts, decorative text',
					'legitimate_use'      => 'Logos, essential graphics where text is part of brand identity',
				),
			);
		}

		return null;
	}
}
