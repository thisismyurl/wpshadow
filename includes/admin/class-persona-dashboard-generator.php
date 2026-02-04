<?php
/**
 * Persona Dashboard Generator
 *
 * Creates targeted dashboards for each user persona showing only
 * diagnostics relevant to their specific needs and priorities.
 *
 * @since   1.6030.2148
 * @package WPShadow\Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\Persona_Registry;
use WPShadow\Core\Diagnostic_Registry;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Persona_Dashboard_Generator Class
 *
 * Generates personalized dashboard views based on user persona selection.
 * Enables users to focus on diagnostics that matter most to them.
 *
 * @since 1.6030.2148
 */
class Persona_Dashboard_Generator {

    /**
     * Generate dashboard widget HTML for selected persona
     *
     * @since  1.6030.2148
     * @param  string $persona_slug Selected persona identifier.
     * @param  array  $findings     Latest diagnostic findings.
     * @return string HTML for persona dashboard.
     */
    public static function generate_dashboard( $persona_slug, $findings ) {
        $persona = Persona_Registry::get_persona( $persona_slug );

        if ( ! $persona ) {
            return '<p>' . esc_html__( 'Invalid persona selected', 'wpshadow' ) . '</p>';
        }

        $diagnostics = Persona_Registry::get_diagnostics_for_persona( $persona_slug );
        $action_plan = Persona_Registry::generate_action_plan( $persona_slug, $findings );

        ob_start();
        ?>
        <div class="wpshadow-persona-dashboard" data-persona="<?php echo esc_attr( $persona_slug ); ?>">
            <!-- Header -->
            <div class="persona-dashboard-header">
                <div class="persona-info">
                    <h1><?php echo esc_html( $persona['label'] ); ?></h1>
                    <p class="persona-description">
                        <?php echo esc_html( $persona['description'] ); ?>
                    </p>
                </div>

                <div class="persona-goals">
                    <h3><?php esc_html_e( 'Your Priorities', 'wpshadow' ); ?></h3>
                    <ul>
                        <?php foreach ( $persona['goals'] as $goal ) : ?>
                            <li>
                                <span class="goal-icon">✓</span>
                                <?php echo esc_html( $goal ); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Top Priority -->
            <div class="persona-dashboard-section critical-issues">
                <div class="section-header">
                    <h2><?php esc_html_e( '⭐ Top Priority Items', 'wpshadow' ); ?></h2>
                    <span class="issue-count badge-critical">
                        <?php echo (int) count( $action_plan['critical_issues'] ?? array() ); ?>
                    </span>
                </div>

                <?php if ( ! empty( $action_plan['critical_issues'] ) ) : ?>
                    <div class="issues-list">
                        <?php foreach ( $action_plan['critical_issues'] as $issue ) : ?>
                            <?php self::render_issue_card( $issue, 'critical', $persona_slug ); ?>
                        <?php endforeach; ?>
                    </div>
                    <p class="time-estimate">
                        <?php
                        printf(
                            /* translators: %d: hours */
                            esc_html__( 'Estimated time to fix: %d hours', 'wpshadow' ),
                            (int) array_sum( array_map( function ( $issue ) {
                                return $issue['estimated_hours'] ?? 1;
                            }, $action_plan['critical_issues'] ) )
                        );
                        ?>
                    </p>
                <?php else : ?>
                    <p class="no-issues">
                        <?php esc_html_e( '✨ Everything looks great! No urgent items need attention.', 'wpshadow' ); ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Recommended Improvements -->
            <div class="persona-dashboard-section recommended-fixes">
                <div class="section-header">
                    <h2><?php esc_html_e( '🟠 Recommended Improvements', 'wpshadow' ); ?></h2>
                    <span class="issue-count badge-medium">
                        <?php echo (int) count( $action_plan['recommended_fixes'] ?? array() ); ?>
                    </span>
                </div>

                <?php if ( ! empty( $action_plan['recommended_fixes'] ) ) : ?>
                    <div class="issues-list">
                        <?php foreach ( $action_plan['recommended_fixes'] as $issue ) : ?>
                            <?php self::render_issue_card( $issue, 'medium', $persona_slug ); ?>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="no-issues">
                        <?php esc_html_e( '✓ All recommended areas optimized!', 'wpshadow' ); ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Nice to Have -->
            <div class="persona-dashboard-section nice-to-have">
                <div class="section-header">
                    <h2><?php esc_html_e( '🟡 Nice to Have', 'wpshadow' ); ?></h2>
                    <span class="issue-count badge-low">
                        <?php echo (int) count( $action_plan['nice_to_have'] ?? array() ); ?>
                    </span>
                </div>

                <?php if ( ! empty( $action_plan['nice_to_have'] ) ) : ?>
                    <p class="section-description">
                        <?php esc_html_e( 'These optimizations would be helpful but are optional for your use case:', 'wpshadow' ); ?>
                    </p>
                    <div class="issues-list">
                        <?php foreach ( $action_plan['nice_to_have'] as $issue ) : ?>
                            <?php self::render_issue_card( $issue, 'low', $persona_slug ); ?>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="no-issues">
                        <?php esc_html_e( '✓ All optional enhancements complete!', 'wpshadow' ); ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Diagnostics This Persona Cares About -->
            <div class="persona-dashboard-section diagnostics-focus">
                <div class="section-header">
                    <h2><?php esc_html_e( 'Diagnostics We Monitor for You', 'wpshadow' ); ?></h2>
                    <span class="diagnostic-count">
                        <?php echo count( $diagnostics ); ?> diagnostics
                    </span>
                </div>

                <p class="section-description">
                    <?php esc_html_e( 'We run these checks specifically because they matter most to your use case:', 'wpshadow' ); ?>
                </p>

                <div class="diagnostics-grid">
                    <?php foreach ( array_slice( $diagnostics, 0, 8 ) as $diagnostic_slug ) : ?>
                        <div class="diagnostic-badge">
                            <span class="diagnostic-name">
                                <?php echo esc_html( self::get_diagnostic_label( $diagnostic_slug ) ); ?>
                            </span>
                            <span class="priority-indicator">
                                <?php
                                $priority = Persona_Registry::get_diagnostic_priority(
                                    $diagnostic_slug,
                                    $persona_slug
                                );
                                if ( $priority >= 80 ) {
                                    echo '<span class="priority-high">� Handle First</span>';
                                } elseif ( $priority >= 50 ) {
                                    echo '<span class="priority-medium">🟠 High Priority</span>';
                                } else {
                                    echo '<span class="priority-low">🟡 Medium Priority</span>';
                                }
                                ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                    <div class="diagnostic-badge more">
                        <?php
                        printf(
                            /* translators: %d: number of diagnostics */
                            esc_html__( '+ %d more', 'wpshadow' ),
                            max( 0, count( $diagnostics ) - 8 )
                        );
                        ?>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="persona-dashboard-section next-steps">
                <h2><?php esc_html_e( 'Recommended Next Steps', 'wpshadow' ); ?></h2>

                <ol class="action-steps">
                    <?php if ( ! empty( $action_plan['critical_issues'] ) ) : ?>
                        <li>
                            <strong><?php esc_html_e( 'Handle These First (Priority 1)', 'wpshadow' ); ?></strong>
                            <p>
                                <?php
                                printf(
                                    /* translators: %d: number of urgent items */
                                    esc_html__( 'Let\'s handle %d urgent items for your %s (like checking smoke alarms)', 'wpshadow' ),
                                    count( $action_plan['critical_issues'] ),
                                    strtolower( $persona['label'] )
                                );
                                ?>
                            </p>
                        </li>
                    <?php endif; ?>

                    <?php if ( ! empty( $action_plan['recommended_fixes'] ) ) : ?>
                        <li>
                            <strong><?php esc_html_e( 'Make These Improvements Next (Priority 2)', 'wpshadow' ); ?></strong>
                            <p>
                                <?php esc_html_e( 'After handling urgent items, these improvements will make your site even better', 'wpshadow' ); ?>
                            </p>
                        </li>
                    <?php endif; ?>

                    <li>
                        <strong><?php esc_html_e( 'Schedule Regular Scans', 'wpshadow' ); ?></strong>
                        <p>
                            <?php esc_html_e( 'Set up automated weekly scans to catch issues early', 'wpshadow' ); ?>
                        </p>
                    </li>

                    <li>
                        <strong><?php esc_html_e( 'Review Knowledge Base', 'wpshadow' ); ?></strong>
                        <p>
                            <?php esc_html_e( 'Learn best practices specific to your use case', 'wpshadow' ); ?>
                        </p>
                    </li>
                </ol>
            </div>
        </div>

        <style>
            .wpshadow-persona-dashboard {
                display: grid;
                gap: 2rem;
                margin: 2rem 0;
            }

            .persona-dashboard-header {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
                padding: 2rem;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            .persona-info h1 {
                margin: 0 0 0.5rem 0;
                font-size: 2rem;
                color: white;
            }

            .persona-description {
                margin: 0;
                opacity: 0.9;
                font-size: 0.95rem;
            }

            .persona-goals ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .persona-goals li {
                padding: 0.5rem 0;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .goal-icon {
                display: inline-flex;
                width: 24px;
                height: 24px;
                align-items: center;
                justify-content: center;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 50%;
                font-size: 0.8rem;
            }

            .persona-dashboard-section {
                padding: 1.5rem;
                background: #f8f9fa;
                border-left: 4px solid #667eea;
                border-radius: 4px;
            }

            .section-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1rem;
            }

            .section-header h2 {
                margin: 0;
                font-size: 1.25rem;
            }

            .issue-count,
            .diagnostic-count {
                background: #667eea;
                color: white;
                padding: 0.25rem 0.75rem;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: bold;
            }

            .badge-critical {
                background: #dc3232;
            }

            .badge-medium {
                background: #ff9800;
            }

            .badge-low {
                background: #ffc107;
                color: #333;
            }

            .issues-list {
                display: grid;
                gap: 1rem;
                margin-bottom: 1rem;
            }

            .diagnostics-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1rem;
            }

            .diagnostic-badge {
                padding: 1rem;
                background: white;
                border: 1px solid #ddd;
                border-radius: 4px;
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .priority-indicator span {
                font-size: 0.8rem;
                font-weight: bold;
            }

            .action-steps {
                padding-left: 1.5rem;
            }

            .action-steps li {
                margin-bottom: 1rem;
            }

            .action-steps strong {
                color: #333;
            }

            .action-steps p {
                margin: 0.25rem 0 0 0;
                font-size: 0.9rem;
                color: #666;
            }

            @media (max-width: 768px) {
                .persona-dashboard-header {
                    grid-template-columns: 1fr;
                }

                .diagnostics-grid {
                    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                }
            }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Render individual issue card
     *
     * @since  1.6030.2148
     * @param  array  $issue        Issue data.
     * @param  string $severity     Severity level ('high', 'medium', 'low').
     * @param  string $persona_slug Current persona.
     * @return void Outputs HTML.
     */
    private static function render_issue_card( $issue, $severity, $persona_slug ) {
        $icon = 'high' === $severity ? '🔴' : ( 'medium' === $severity ? '🟠' : '🟡' );
        ?>
        <div class="issue-card issue-<?php echo esc_attr( $severity ); ?>">
            <div class="issue-header">
                <span class="issue-icon"><?php echo wp_kses_post( $icon ); ?></span>
                <h3><?php echo esc_html( $issue['title'] ?? 'Issue' ); ?></h3>
            </div>
            <p class="issue-description">
                <?php echo wp_kses_post( $issue['description'] ?? '' ); ?>
            </p>
            <?php if ( ! empty( $issue['impact'] ) ) : ?>
                <p class="issue-impact">
                    <strong><?php esc_html_e( 'Impact:', 'wpshadow' ); ?></strong>
                    <?php echo wp_kses_post( $issue['impact'] ); ?>
                </p>
            <?php endif; ?>
            <div class="issue-actions">
                <?php if ( ! empty( $issue['kb_link'] ) ) : ?>
                    <a href="<?php echo esc_url( $issue['kb_link'] ); ?>" target="_blank" class="button-link">
                        <?php esc_html_e( 'Learn More', 'wpshadow' ); ?>
                    </a>
                <?php endif; ?>
                <?php if ( ! empty( $issue['auto_fixable'] ) ) : ?>
                    <button class="button button-primary" data-action="fix-issue" data-issue-id="<?php echo esc_attr( $issue['id'] ?? '' ); ?>">
                        <?php esc_html_e( 'Apply Fix', 'wpshadow' ); ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Get human-readable label for diagnostic
     *
     * @since  1.6030.2148
     * @param  string $diagnostic_slug Diagnostic identifier.
     * @return string Diagnostic label.
     */
    private static function get_diagnostic_label( $diagnostic_slug ) {
        // Convert slug to readable label
        $label = str_replace( array( '-', '_' ), ' ', $diagnostic_slug );
        return ucwords( $label );
    }
}
