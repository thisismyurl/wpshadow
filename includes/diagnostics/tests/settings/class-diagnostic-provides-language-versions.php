<?php
/**
 * Language Versions Available Diagnostic
 *
 * Tests whether the site provides professionally translated content in 3+ languages
 * for key markets. Multiple language support expands market reach and demonstrates
 * commitment to international audiences.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5003.1025
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Diagnostic_Provides_Language_Versions Class
 *
 * Diagnostic #25: Language Versions Available from Specialized & Emerging Success Habits.
 * Checks if the website provides professionally translated content in 3+ languages.
 *
 * @since 1.5003.1025
 */
class Diagnostic_Provides_Language_Versions extends Diagnostic_Base {

protected static $slug = 'provides-language-versions';
protected static $title = 'Language Versions Available';
protected static $description = 'Tests whether the site provides professionally translated content in 3+ languages for key markets';
protected static $family = 'international-ecommerce';

public static function check() {
$score          = 0;
$max_score      = 5;
$score_details  = array();
$recommendations = array();

// Check multi-language plugin.
$multilingual_plugins = array(
'sitepress-multilingual-cms/sitepress.php',
'polylang/polylang.php',
'translatepress-multilingual/index.php',
'weglot/weglot.php',
);

$has_multilingual = false;
foreach ( $multilingual_plugins as $plugin ) {
if ( is_plugin_active( $plugin ) ) {
$has_multilingual = true;
++$score;
$score_details[] = __( '✓ Multi-language plugin active', 'wpshadow' );
break;
}
}

if ( ! $has_multilingual ) {
$score_details[]   = __( '✗ No multi-language plugin detected', 'wpshadow' );
$recommendations[] = __( 'Install WPML, Polylang, or TranslatePress to support multiple languages', 'wpshadow' );
}

// Check language count (estimate via URL patterns or pages).
$language_count = 0;
$language_codes = array( 'en', 'es', 'fr', 'de', 'it', 'pt', 'zh', 'ja', 'ko', 'ar', 'ru', 'nl', 'pl', 'tr' );

$all_pages = get_posts(
array(
'post_type'      => 'page',
'posts_per_page' => 100,
'post_status'    => 'publish',
)
);

foreach ( $language_codes as $lang_code ) {
foreach ( $all_pages as $page ) {
if ( stripos( get_permalink( $page->ID ), "/{$lang_code}/" ) !== false ) {
++$language_count;
break;
}
}
}

if ( $language_count >= 3 ) {
$score += 2;
$score_details[] = sprintf(
/* translators: %d: number of languages */
__( '✓ Content available in %d+ languages', 'wpshadow' ),
$language_count
);
} elseif ( $language_count > 0 ) {
++$score;
$score_details[]   = sprintf( __( '◐ Content in %d language(s)', 'wpshadow' ), $language_count );
$recommendations[] = __( 'Expand to 3+ languages to serve major markets', 'wpshadow' );
} else {
$score_details[]   = __( '✗ Single language site', 'wpshadow' );
$recommendations[] = __( 'Translate content into at least 3 key market languages (e.g., English, Spanish, French)', 'wpshadow' );
}

// Check language switcher.
global $wp_scripts;
$has_switcher = false;
if ( isset( $wp_scripts->registered ) ) {
foreach ( $wp_scripts->registered as $handle => $script ) {
if ( stripos( $handle, 'language' ) !== false || stripos( $handle, 'translate' ) !== false ) {
$has_switcher = true;
break;
}
}
}

if ( $has_switcher || $has_multilingual ) {
++$score;
$score_details[] = __( '✓ Language switcher available', 'wpshadow' );
} else {
$score_details[]   = __( '✗ No language switcher detected', 'wpshadow' );
$recommendations[] = __( 'Add a language switcher menu or widget for easy language selection', 'wpshadow' );
}

// Check professional translation quality indicators.
$has_quality_indicator = get_posts(
array(
'post_type'      => 'any',
'posts_per_page' => 3,
'post_status'    => 'publish',
's'              => 'professional translation',
)
);

if ( ! empty( $has_quality_indicator ) ) {
++$score;
$score_details[] = __( '✓ Professional translation services mentioned', 'wpshadow' );
} else {
$score_details[]   = __( '✗ No translation quality documentation', 'wpshadow' );
$recommendations[] = __( 'Use professional translators, not machine translation, for quality content', 'wpshadow' );
}

$score_percentage = ( $score / $max_score ) * 100;

if ( $score_percentage < 30 ) {
$severity     = 'medium';
$threat_level = 25;
} elseif ( $score_percentage < 60 ) {
$severity     = 'low';
$threat_level = 15;
} else {
return null;
}

return array(
'id'               => self::$slug,
'title'            => self::$title,
'description'      => sprintf(
/* translators: %d: score percentage */
__( 'Language versions score: %d%%. 76%% of consumers prefer content in their native language, and 40%% won\'t buy from sites not in their language. Sites with 3+ languages see 47%% higher international revenue.', 'wpshadow' ),
$score_percentage
),
'severity'         => $severity,
'threat_level'     => $threat_level,
'auto_fixable'     => false,
'kb_link'          => 'https://wpshadow.com/kb/language-versions',
'details'          => $score_details,
'recommendations'  => $recommendations,
'impact'           => __( 'Multi-language support increases addressable market size by 300-500% and demonstrates commitment to international customers.', 'wpshadow' ),
);
}
}
