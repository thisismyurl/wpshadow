<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Pages_Per_Session extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-pages-per-session', 'title' => __('Pages Per Session Trend', 'wpshadow'), 'description' => __('Tracks depth of user engagement. Drop indicates users not finding related content or poor navigation.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/internal-linking/', 'training_link' => 'https://wpshadow.com/training/navigation-design/', 'auto_fixable' => false, 'threat_level' => 5]; } }
