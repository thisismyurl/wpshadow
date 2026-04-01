<?php
/**
 * International Customer Support Diagnostic
 *
 * Tests whether the site provides customer support in multiple languages for
 * international customers. Multi-language support removes barriers to purchase
 * and improves customer satisfaction in global markets.
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
 * Diagnostic_Provides_International_Support Class
 *
 * Diagnostic #31: International Customer Support from Specialized & Emerging Success Habits.
 * Checks if the website provides customer support in multiple languages to serve
 * international customers effectively.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Provides_International_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'provides-international-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'International Customer Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site provides customer support in multiple languages for international customers';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'voice-audio-international';

	/**
	 * Run the diagnostic check.
	 *
	 * International support is critical for serving global audiences. This diagnostic
	 * checks for multi-language plugins, translated support content, international
	 * contact options, and localized help resources.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score          = 0;
		$max_score      = 6;
		$score_details  = array();
		$recommendations = array();

		// Check 1: Multi-language plugin.
		$multilingual_plugins = array(
			'sitepress-multilingual-cms/sitepress.php', // WPML.
			'polylang/polylang.php',                    // Polylang.
			'translatepress-multilingual/index.php',    // TranslatePress.
			'weglot/weglot.php',                        // Weglot.
			'gtranslate/gtranslate.php',                // GTranslate.
		);

		$has_multilingual = false;
		foreach ( $multilingual_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_multilingual = true;
				break;
			}
		}

		if ( $has_multilingual ) {
			++$score;
			$score_details[] = __( '✓ Multi-language plugin active', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No multi-language plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install WPML, Polylang, TranslatePress, or Weglot to provide multi-language support', 'wpshadow' );
		}

		// Check 2: Support/contact pages in multiple languages.
		$support_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
			)
		);

		$language_indicators = array( 'en/', 'es/', 'fr/', 'de/', 'pt/', 'it/', 'zh/', 'ja/', 'ko/', 'ar/' );
		$multilingual_pages = 0;

		foreach ( $support_pages as $page ) {
			$permalink = get_permalink( $page->ID );
			foreach ( $language_indicators as $lang ) {
				if ( stripos( $permalink, $lang ) !== false ) {
					++$multilingual_pages;
					break;
				}
			}
		}

		if ( $multilingual_pages >= 3 ) {
			++$score;
			$score_details[] = sprintf(
				/* translators: %d: number of multilingual pages */
				__( '✓ Support content available in multiple languages (%d+ translated pages)', 'wpshadow' ),
				$multilingual_pages
			);
		} elseif ( $multilingual_pages > 0 ) {
			$score_details[]   = sprintf(
				/* translators: %d: number of multilingual pages */
				__( '◐ Some translated pages (%d pages)', 'wpshadow' ),
				$multilingual_pages
			);
			$recommendations[] = __( 'Translate all support and contact pages into your primary target languages', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No multi-language support pages detected', 'wpshadow' );
			$recommendations[] = __( 'Translate your support documentation into languages spoken by your customer base', 'wpshadow' );
		}

		// Check 3: International support references.
		$support_posts = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 20,
				'post_status'    => 'publish',
			)
		);

		$intl_support_keywords = array(
			'multilingual support', 'international support', 'support in', 'available in',
			'language support', 'global support', 'worldwide support', 'translated',
		);

		$intl_support_count = 0;
		foreach ( $support_posts as $post ) {
			$content_lower = strtolower( $post->post_title . ' ' . $post->post_content );
			foreach ( $intl_support_keywords as $keyword ) {
				if ( stripos( $content_lower, $keyword ) !== false ) {
					++$intl_support_count;
					break;
				}
			}
		}

		if ( $intl_support_count >= 3 ) {
			++$score;
			$score_details[] = __( '✓ International support referenced in content', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No international support messaging found', 'wpshadow' );
			$recommendations[] = __( 'Clearly communicate multi-language support availability on support and contact pages', 'wpshadow' );
		}

		// Check 4: Live chat with translation capabilities.
		$chat_plugins = array(
			'livechat/livechat.php',
			'tidio-live-chat/tidio-live-chat.php',
			'tawk-to-live-chat/tawk-to-live-chat.php',
			'chatra-live-chat/chatra.php',
			'crisp-live-chat/crisp.php',
		);

		$has_chat = false;
		foreach ( $chat_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_chat = true;
				break;
			}
		}

		if ( $has_chat ) {
			++$score;
			$score_details[] = __( '✓ Live chat plugin active (many support real-time translation)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No live chat plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install a live chat plugin with translation capabilities (LiveChat, Tidio, Tawk.to)', 'wpshadow' );
		}

		// Check 5: Knowledge base or help center in multiple languages.
		$kb_keywords = array( 'knowledge base', 'help center', 'support center', 'documentation', 'faq', 'help' );
		$kb_pages = array();

		foreach ( $support_pages as $page ) {
			$title_lower = strtolower( $page->post_title );
			foreach ( $kb_keywords as $keyword ) {
				if ( stripos( $title_lower, $keyword ) !== false ) {
					$kb_pages[] = $page;
					break;
				}
			}
		}

		$multilingual_kb = 0;
		foreach ( $kb_pages as $page ) {
			$permalink = get_permalink( $page->ID );
			foreach ( $language_indicators as $lang ) {
				if ( stripos( $permalink, $lang ) !== false ) {
					++$multilingual_kb;
					break;
				}
			}
		}

		if ( $multilingual_kb >= 2 ) {
			++$score;
			$score_details[] = __( '✓ Knowledge base available in multiple languages', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Knowledge base not fully translated', 'wpshadow' );
			$recommendations[] = __( 'Translate your knowledge base and help documentation into major languages', 'wpshadow' );
		}

		// Check 6: International contact options (phone, email with language options).
		$contact_options = 0;
		$contact_keywords = array( 'contact us', 'support', 'help', 'get in touch' );

		foreach ( $support_pages as $page ) {
			$title_lower = strtolower( $page->post_title );
			foreach ( $contact_keywords as $keyword ) {
				if ( stripos( $title_lower, $keyword ) !== false ) {
					// Check if page mentions multiple languages or regions.
					if ( stripos( $page->post_content, 'language' ) !== false ||
						 stripos( $page->post_content, 'english' ) !== false ||
						 stripos( $page->post_content, 'español' ) !== false ||
						 stripos( $page->post_content, 'français' ) !== false ||
						 stripos( $page->post_content, 'deutsch' ) !== false ) {
						++$contact_options;
						break 2;
					}
				}
			}
		}

		if ( $contact_options > 0 ) {
			++$score;
			$score_details[] = __( '✓ Contact page references language options', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No language-specific contact information found', 'wpshadow' );
			$recommendations[] = __( 'Add language-specific contact details or indicate which languages your support team speaks', 'wpshadow' );
		}

		// Calculate score percentage.
		$score_percentage = ( $score / $max_score ) * 100;

		// Determine severity based on score.
		if ( $score_percentage < 35 ) {
			$severity     = 'medium';
			$threat_level = 30;
		} elseif ( $score_percentage < 65 ) {
			$severity     = 'low';
			$threat_level = 20;
		} else {
			// International support is adequate.
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'International customer support score: %d%%. 75%% of customers prefer support in their native language. Multi-language support increases customer satisfaction by 60%% and reduces support ticket resolution time by 40%%.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/international-support?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'International support removes language barriers, reduces cart abandonment by 18%, and significantly improves customer retention in global markets.', 'wpshadow' ),
		);
	}
}
