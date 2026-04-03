<?php
/**
 * Auto-Draft Accumulation Diagnostic
 *
 * Every time the WordPress post editor is opened, WordPress creates an
 * auto-draft row in wp_posts. These rows are never automatically purged.
 * On active sites they accumulate into the thousands, bloating the posts
 * table, slowing revision-related queries, and inflating backup sizes without
 * providing any ongoing value.
 *
 * @package WPShadow
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
 * Diagnostic_Auto_Draft_Accumulation Class
 *
 * Counts post rows with post_status = 'auto-draft'. Returns null when
 * the count is below the threshold. Returns a finding scaled by volume.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Auto_Draft_Accumulation extends Diagnostic_Base {

    /**
     * Diagnostic slug.
     *
     * @var string
     */
    protected static $slug = 'auto-draft-accumulation';

    /**
     * Diagnostic title.
     *
     * @var string
     */
    protected static $title = 'Auto-Draft Accumulation';

    /**
     * Diagnostic description.
     *
     * @var string
     */
    protected static $description = 'Checks whether auto-draft rows have accumulated in wp_posts. WordPress creates an auto-draft every time the post editor is opened and never automatically removes them.';

    /**
     * Gauge family/category.
     *
     * @var string
     */
    protected static $family = 'database';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'low';

    /**
     * Auto-drafts above this count trigger a medium-severity finding.
     *
     * @var int
     */
    private const THRESHOLD_MEDIUM = 100;

    /**
     * Auto-drafts above this count escalate to high severity.
     *
     * @var int
     */
    private const THRESHOLD_HIGH = 500;

    /**
     * Run the diagnostic check.
     *
     * Counts all post rows with post_status = 'auto-draft'. Returns null
     * when the count is below THRESHOLD_MEDIUM. Returns medium severity
     * between the two thresholds and high severity above THRESHOLD_HIGH.
     *
     * @since  0.6093.1200
     * @return array|null Finding array when auto-drafts are excessive, null when healthy.
     */
    public static function check() {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $count = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'auto-draft'"
        );

        if ( $count < self::THRESHOLD_MEDIUM ) {
            return null;
        }

        $severity     = $count >= self::THRESHOLD_HIGH ? 'high' : 'medium';
        $threat_level = $count >= self::THRESHOLD_HIGH ? 55 : 35;

        return array(
            'id'           => self::$slug,
            'title'        => self::$title,
            'description'  => sprintf(
                /* translators: %d: number of auto-draft rows found */
                _n(
                    '%d auto-draft row was found in the posts table. Auto-drafts are created each time the editor is opened and are never automatically deleted. They add unnecessary weight to the database, slow revision queries, and inflate backup sizes.',
                    '%d auto-draft rows were found in the posts table. Auto-drafts are created each time the editor is opened and are never automatically deleted. They add unnecessary weight to the database, slow revision queries, and inflate backup sizes.',
                    $count,
                    'wpshadow'
                ),
                $count
            ),
            'severity'     => $severity,
            'threat_level' => $threat_level,
            'kb_link'      => 'https://wpshadow.com/kb/auto-draft-accumulation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
            'details'      => array(
                'auto_draft_count' => $count,
                'fix'              => __( 'Run via WP-CLI: wp post delete $(wp post list --post_status=auto-draft --format=ids) — or use a database optimisation plugin (WP-Optimize, Advanced Database Cleaner) to purge auto-drafts with one click. WordPress does not auto-purge these during scheduled cleanup.', 'wpshadow' ),
            ),
        );
    }
}
