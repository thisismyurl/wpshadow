<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Search_Console_Errors extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-sc-errors', 'title' => __('Search Console Error Rate', 'wpshadow'), 'description' => __('Tracks crawl errors, indexation issues in Search Console. Errors prevent indexing and ranking.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/search-console/', 'training_link' => 'https://wpshadow.com/training/sc-monitoring/', 'auto_fixable' => false, 'threat_level' => 8]; } }
