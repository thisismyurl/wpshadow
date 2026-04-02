<?php
/**
 * Support Ticket Trend Analysis Diagnostic
 *
 * Analyzes common issues across support tickets for agencies.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Support Ticket Trend Analysis Diagnostic Class
 *
 * Identifies patterns in client support tickets to highlight
 * common issues that should be proactively addressed across
 * the agency's client portfolio.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Support_Ticket_Trend_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'support-ticket-trend-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Support Ticket Trend Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies common issues across portfolio';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the support ticket analysis check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if critical trends detected, null otherwise.
	 */
	public static function check() {
		$stats = array();
		$issues = array();

		// Get support ticket data (if available via custom post type).
		$ticket_args = array(
			'post_type'   => 'support_ticket',
			'numberposts' => 100,
		);

		$tickets = get_posts( $ticket_args );
		$stats['total_tickets'] = count( $tickets );

		if ( empty( $tickets ) ) {
			return null; // No support tracking system active.
		}

		// Analyze ticket patterns.
		$issue_categories = array();
		$unresolved_tickets = 0;
		$avg_resolution_time = 0;
		$resolution_times = array();

		foreach ( $tickets as $ticket ) {
			// Get ticket category/type.
			$categories = get_the_terms( $ticket->ID, 'ticket_category' );
			if ( ! empty( $categories ) ) {
				foreach ( $categories as $cat ) {
					$key = $cat->slug;
					$issue_categories[ $key ] = ( $issue_categories[ $key ] ?? 0 ) + 1;
				}
			}

			// Get ticket status.
			$status = get_post_meta( $ticket->ID, '_ticket_status', true );
			if ( 'unresolved' === $status || 'open' === $status ) {
				$unresolved_tickets++;
			}

			// Calculate resolution time.
			$opened = strtotime( $ticket->post_date );
			$closed = get_post_meta( $ticket->ID, '_ticket_resolved_date', true );
			if ( ! empty( $closed ) ) {
				$closed_time = strtotime( $closed );
				$resolution_time = ( $closed_time - $opened ) / ( 60 * 60 ); // Hours.
				$resolution_times[] = $resolution_time;
			}
		}

		// Calculate stats.
		if ( ! empty( $resolution_times ) ) {
			$avg_resolution_time = array_sum( $resolution_times ) / count( $resolution_times );
		}

		$stats['unresolved_tickets'] = $unresolved_tickets;
		$stats['avg_resolution_hours'] = round( $avg_resolution_time, 1 );
		$stats['issue_categories'] = $issue_categories;

		// Get top issues.
		if ( ! empty( $issue_categories ) ) {
			arsort( $issue_categories );
			$top_issues = array_slice( $issue_categories, 0, 3, true );
			$stats['top_issues'] = $top_issues;

			// Generate insights.
			$top_issue = key( $top_issues );
			if ( $issue_categories[ $top_issue ] > count( $tickets ) * 0.3 ) {
				$issues[] = sprintf(
					/* translators: %s: issue type */
					__( '%s represents 30%% of tickets - address proactively', 'wpshadow' ),
					ucfirst( str_replace( '-', ' ', $top_issue ) )
				);
			}
		}

		// Check resolution time SLA.
		if ( $avg_resolution_time > 24 ) {
			$issues[] = sprintf(
				/* translators: %d: hours */
				__( 'Average resolution time is %d hours - consider process improvements', 'wpshadow' ),
				round( $avg_resolution_time )
			);
		}

		// Check unresolved ticket backlog.
		if ( $unresolved_tickets > count( $tickets ) * 0.2 ) {
			$issues[] = sprintf(
				/* translators: %d: number of tickets */
				__( '%d unresolved tickets in backlog - needs attention', 'wpshadow' ),
				$unresolved_tickets
			);
		}

		// Check ticket trending.
		$tickets_this_week = $this->count_tickets_in_date_range( '-7 days' );
		$tickets_last_week = $this->count_tickets_in_date_range( '-14 days', '-7 days' );

		$stats['tickets_this_week'] = $tickets_this_week;
		$stats['tickets_last_week'] = $tickets_last_week;

		if ( $tickets_this_week > $tickets_last_week *1.0 ) {
			$issues[] = sprintf(
				/* translators: %d: percentage */
				__( 'Support tickets up %d%% - may indicate system issues', 'wpshadow' ),
				round( ( ( $tickets_this_week - $tickets_last_week ) / $tickets_last_week ) * 100 )
			);
		}

		// If critical trends detected.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Support ticket trends detected: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/portfolio-support-analysis',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null; // Support ticket patterns normal.
	}

	/**
	 * Count tickets in a date range.
	 *
	 * @since 1.6093.1200
	 * @param  string $from From date (relative).
	 * @param  string $to   To date (relative, optional).
	 * @return int Ticket count.
	 */
	private function count_tickets_in_date_range( $from, $to = null ) {
		$from_time = strtotime( $from );
		$to_time = ! empty( $to ) ? strtotime( $to ) : current_time( 'timestamp' );

		$args = array(
			'post_type'   => 'support_ticket',
			'numberposts' => -1,
			'date_query'  => array(
				array(
					'after'  => gmdate( 'Y-m-d H:i:s', $from_time ),
					'before' => gmdate( 'Y-m-d H:i:s', $to_time ),
				),
			),
		);

		return count( get_posts( $args ) );
	}
}
