<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Post_Publication_Delay extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-pub-delay', 'title' => __('Post Publication Delay Monitoring', 'wpshadow'), 'description' => __('Tracks time from publishing to first Google index. Delay indicates crawl/indexation issues.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/indexation-speed/', 'training_link' => 'https://wpshadow.com/training/publish-strategy/', 'auto_fixable' => false, 'threat_level' => 5]; } }
