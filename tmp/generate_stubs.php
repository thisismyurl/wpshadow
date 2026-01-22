<?php
declare(strict_types=1);

$base = '/workspaces/wpshadow/includes/diagnostics/new';
$defs = [
    'CustomerRetention' => [
        ['churn-risk-cohort','Churn_Risk_Cohort','Identifies customers in high-risk churn cohorts based on recency, frequency, and monetary trends.','Commerce',2],
        ['winback-campaign-missing','Winback_Campaign_Missing','Detects absence of win-back journeys for idle customers.','Commerce',2],
        ['subscription-renewal-failure','Subscription_Renewal_Failure','Flags subscription renewal failures and missing retry workflows.','Commerce',1],
        ['loyalty-points-expiry','Loyalty_Points_Expiry','Finds loyalty points nearing expiry without reminders.','Commerce',2],
        ['email-engagement-decay','Email_Engagement_Decay','Detects decaying email engagement and missing re-engagement paths.','Commerce',2],
        ['rfm-score-drop','RFM_Score_Drop','Flags customers whose RFM scores are dropping without intervention.','Commerce',2],
        ['ltv-drop','LTV_Drop','Detects cohorts with declining lifetime value.','Commerce',2],
        ['repeat-purchase-gap','Repeat_Purchase_Gap','Finds customers past expected repurchase window without prompts.','Commerce',2],
        ['cart-abandonment-winback','Cart_Abandonment_Winback','Checks if abandoned cart win-back flows are active and converting.','Commerce',1],
        ['onsite-behavior-drop','Onsite_Behavior_Drop','Detects drop in visits/pageviews from paying customers.','Commerce',2],
        ['churn-survey-missing','Churn_Survey_Missing','Flags missing exit/churn surveys to capture reasons.','Commerce',3],
        ['retention-dashboard-gap','Retention_Dashboard_Gap','Checks if retention KPIs (AOV, LTV, churn) are tracked and surfaced.','Commerce',3],
        ['high-value-churn-risk','High_Value_Churn_Risk','Identifies high-LTV customers showing churn signals.','Commerce',1],
        ['onboarding-dropoff','Onboarding_Dropoff','Finds drop-offs in onboarding funnels without recovery steps.','Commerce',2],
        ['dormant-subscriber','Dormant_Subscriber','Detects subscribers with no activity for 90/180+ days.','Commerce',2],
        ['sms-optout-spike','SMS_Optout_Spike','Flags spikes in SMS opt-outs indicating message fatigue.','Commerce',3],
        ['coupon-abuse-risk','Coupon_Abuse_Risk','Detects repeated coupon/discount abuse patterns.','Commerce',2],
        ['negative-feedback-surge','Negative_Feedback_Surge','Flags surges in refunds/chargebacks/negative reviews.','Commerce',1],
        ['support-ticket-churn-risk','Support_Ticket_Churn_Risk','Identifies support-heavy customers at churn risk.','Commerce',2],
        ['shipping-delay-churn-risk','Shipping_Delay_Churn_Risk','Flags repeated shipping delays for key customers.','Commerce',2],
        ['payment-failure-churn-risk','Payment_Failure_Churn_Risk','Detects repeated payment failures without dunning.','Commerce',1],
        ['upsell-journey-missing','Upsell_Journey_Missing','Checks missing upsell journeys post-purchase.','Commerce',3],
        ['cross-sell-journey-missing','Cross_Sell_Journey_Missing','Checks missing cross-sell journeys for compatible products.','Commerce',3],
        ['reactivation-offer-missing','Reactivation_Offer_Missing','Detects missing reactivation offers for dormant users.','Commerce',3],
        ['subscription-pause-risk','Subscription_Pause_Risk','Flags high pause rates and missing recovery steps.','Commerce',2],
    ],
    'AIReadiness' => [
        ['pii-leakage-risk','PII_Leakage_Risk','Scans prompts/logs for PII exposure risks.','AI',2],
        ['prompt-injection-surface','Prompt_Injection_Surface','Identifies user-controlled fields reaching LLMs without sanitization.','AI',1],
        ['model-version-drift','Model_Version_Drift','Detects unpinned or drifting model versions across environments.','AI',2],
        ['ai-usage-logging-missing','AI_Usage_Logging_Missing','Flags missing AI usage/error logging for audits.','AI',2],
        ['api-quota-monitor','API_Quota_Monitor','Checks AI API quota/limits and alerting.','AI',2],
        ['rate-limit-policy-missing','Rate_Limit_Policy_Missing','Flags missing rate-limit/abuse controls on AI endpoints.','AI',2],
        ['content-safety-filter-missing','Content_Safety_Filter_Missing','Detects missing content safety filters (toxicity, NSFW).','AI',1],
        ['bias-audit-missing','Bias_Audit_Missing','Checks if bias/fairness audits exist for AI outputs.','AI',3],
        ['red-team-checklist-missing','Red_Team_Checklist_Missing','Flags missing red-team tests for AI features.','AI',3],
        ['data-retention-ai','Data_Retention_AI','Detects undefined retention for prompts/responses.','AI',2],
        ['privacy-consent-ai','Privacy_Consent_AI','Checks consent/opt-out for AI personalization.','AI',2],
        ['jailbreak-detection-missing','Jailbreak_Detection_Missing','Flags lack of jailbreak/prompt attack detection.','AI',1],
        ['pii-masking-missing','PII_Masking_Missing','Detects missing masking/anonymization before LLM.','AI',1],
        ['llm-cache-poisoning','LLM_Cache_Poisoning','Checks for untrusted data in LLM caches.','AI',2],
        ['ai-dependency-health','AI_Dependency_Health','Monitors AI SDK/key dependency versions and CVEs.','AI',2],
        ['ai-failover-plan-missing','AI_Failover_Plan_Missing','Flags missing fallback when AI providers fail.','AI',2],
        ['ai-observability-missing','AI_Observability_Missing','Checks tracing/metrics for AI latency, errors, cost.','AI',2],
        ['prompt-template-versioning','Prompt_Template_Versioning','Ensures prompts are versioned and auditable.','AI',2],
        ['secrets-in-prompts','Secrets_In_Prompts','Detects secrets/tokens embedded in prompts.','AI',1],
        ['ai-error-budget-missing','AI_Error_Budget_Missing','Flags missing SLO/error budget for AI endpoints.','AI',3],
        ['ai-safety-tests-missing','AI_Safety_Tests_Missing','Checks automated safety evals in CI.','AI',2],
        ['user-optout-ai','User_Optout_AI','Detects missing user opt-out for AI features.','AI',2],
        ['model-card-missing','Model_Card_Missing','Flags missing model cards/disclosures.','AI',3],
        ['eval-benchmark-missing','Eval_Benchmark_Missing','Checks if benchmarks exist and are tracked over time.','AI',2],
        ['ai-abuse-detection','AI_Abuse_Detection','Detects abuse patterns (spam/scams) using/against AI features.','AI',1],
    ],
    'EnvironmentImpact' => [
        ['carbon-intensity-hosting','Carbon_Intensity_Hosting','Estimates hosting carbon intensity and greener options.','Core',3],
        ['green-hosting-cert','Green_Hosting_Cert','Checks for green/renewable hosting certifications.','Core',3],
        ['cdn-geo-efficiency','CDN_Geo_Efficiency','Analyzes CDN geo coverage vs user distribution.','Core',2],
        ['image-energy-inefficiency','Image_Energy_Inefficiency','Flags heavy images impacting energy use.','Core',2],
        ['cron-energy-spike','Cron_Energy_Spike','Detects heavy cron jobs during peak energy times.','Core',3],
        ['unused-environment-dev','Unused_Environment_Dev','Finds idle dev/stage environments consuming resources.','Core',3],
        ['log-retention-bloat','Log_Retention_Bloat','Flags excessive log retention increasing storage/energy.','Core',2],
        ['backup-frequency-energy','Backup_Frequency_Energy','Checks overly frequent backups consuming energy.','Core',2],
        ['idle-plugin-energy','Idle_Plugin_Energy','Identifies heavy plugins with low usage vs resource draw.','Core',2],
        ['cache-miss-energy','Cache_Miss_Energy','Estimates energy waste from low cache hit rates.','Core',2],
        ['database-inefficient-queries','Database_Inefficient_Queries','Flags energy-heavy queries lacking indexes.','Core',2],
        ['php-version-efficiency','PHP_Version_Efficiency','Checks PHP version efficiency vs latest stable.','Core',2],
        ['js-bundle-waste','JS_Bundle_Waste','Flags large unused JS increasing energy use.','Core',2],
        ['css-bloat-waste','CSS_Bloat_Waste','Identifies unused CSS contributing to waste.','Core',2],
        ['font-bloat-waste','Font_Bloat_Waste','Flags multiple/unused fonts increasing downloads.','Core',2],
        ['video-autoplay-impact','Video_Autoplay_Impact','Highlights autoplay videos driving bandwidth/energy.','Core',2],
        ['third-party-script-impact-energy','Third_Party_Script_Impact_Energy','Estimates energy cost of third-party scripts.','Core',2],
        ['scheduler-off-peak-missing','Scheduler_Off_Peak_Missing','Checks if heavy jobs run off-peak/low-carbon windows.','Core',3],
        ['server-idle-high','Server_Idle_High','Detects high idle load wasting energy.','Core',2],
        ['asset-expiry-short','Asset_Expiry_Short','Flags too-short cache expiry causing re-downloads.','Core',2],
        ['cdn-cache-hit-low','CDN_Cache_Hit_Low','Detects low CDN cache hit ratio increasing energy use.','Core',2],
        ['gzip-br-min','Gzip_BR_Min','Ensures compression (gzip/br) enabled to reduce transfer size.','Core',1],
        ['http2-h3-off','HTTP2_H3_Off','Flags HTTP/2 or H3 not enabled for efficiency.','Core',2],
        ['db-index-missing-energy','DB_Index_Missing_Energy','Identifies missing DB indexes causing extra CPU.','Core',2],
        ['queue-batch-size-inefficient','Queue_Batch_Size_Inefficient','Checks queue batch sizes causing excess cycles.','Core',2],
    ],
    'UsersTeam' => [
        ['admin-overprovision','Admin_Overprovision','Flags too many admins vs org size.','Guardian',1],
        ['inactive-admins','Inactive_Admins','Detects inactive admin accounts to disable.','Guardian',1],
        ['orphaned-users','Orphaned_Users','Finds users without owners/managers.','Guardian',2],
        ['shared-accounts','Shared_Accounts','Detects shared credentials via activity patterns.','Guardian',2],
        ['mfa-coverage-gap','MFA_Coverage_Gap','Checks 2FA coverage across roles.','Guardian',1],
        ['role-drift','Role_Drift','Detects roles drifting from baseline caps.','Guardian',2],
        ['permission-anomalies','Permission_Anomalies','Flags anomalous capabilities on accounts.','Guardian',2],
        ['audit-log-missing','Audit_Log_Missing','Checks for missing audit logging of user actions.','Guardian',1],
        ['superadmin-count','Superadmin_Count','Ensures superadmin count is minimal and justified.','Guardian',1],
        ['password-rotation-missing','Password_Rotation_Missing','Detects lack of password rotation/enforcement.','Guardian',2],
        ['session-revocation-missing','Session_Revocation_Missing','Flags lack of forced logout/revocation controls.','Guardian',2],
        ['sso-misconfigured','SSO_Misconfigured','Detects SSO config issues leading to bypass or lockout.','Guardian',2],
        ['offboarding-missing','Offboarding_Missing','Checks missing offboarding steps for departed staff.','Guardian',1],
        ['onboarding-missing','Onboarding_Missing','Detects missing onboarding checklists (MFA, roles, keys).','Guardian',2],
        ['least-privilege-gap','Least_Privilege_Gap','Flags over-permissioned roles/users.','Guardian',1],
        ['api-keys-owned','API_Keys_Owned','Checks ownership/accountability for API keys.','Guardian',2],
        ['webhook-ownership','Webhook_Ownership','Ensures webhooks have owners and rotation policies.','Guardian',2],
        ['support-staff-2fa','Support_Staff_2FA','Checks 2FA adoption for support/editorial staff.','Guardian',1],
        ['editor-capabilities-risk','Editor_Capabilities_Risk','Flags editors with dangerous capabilities.','Guardian',2],
        ['capability-escalation-history','Capability_Escalation_History','Detects past capability escalations without review.','Guardian',2],
        ['user-enumeration-protection','User_Enumeration_Protection','Checks protections against user enumeration.','Guardian',1],
        ['login-alerts-missing','Login_Alerts_Missing','Flags missing login anomaly alerts.','Guardian',2],
        ['device-trust-missing','Device_Trust_Missing','Checks device trust/verification gaps.','Guardian',2],
        ['geo-login-anomaly','Geo_Login_Anomaly','Detects geo-velocity or unusual login locations.','Guardian',1],
        ['recovery-contacts-missing','Recovery_Contacts_Missing','Flags missing recovery contacts/backup methods.','Guardian',2],
    ],
];

$template = static function(string $ns, string $slug, string $class, string $title, string $desc, string $module, int $priority): string {
    $kb_link = "https://wpshadow.com/kb/{$slug}/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign={$slug}";
    $training_link = "https://wpshadow.com/training/{$slug}/";

    return '<?php
declare(strict_types=1);

namespace WPShadow\\DiagnosticsFuture\\' . $ns . ';

use WPShadow\\Core\\Diagnostic_Base;

class Diagnostic_' . $class . ' extends Diagnostic_Base {
    protected static $slug = \'' . $slug . '\';
    protected static $title = \'' . $title . '\';
    protected static $description = \'' . $desc . '\';

    public static function check(): ?array {
        return array(
            \"id\"            => static::\$slug,
            \"title\"         => static::\$title . \" [STUB]\",
            \"description\"   => static::\$description . \" (Not yet implemented)\",
            \"color\"         => \"#9e9e9e\",
            \"bg_color\"      => \"#f5f5f5\",
            \"kb_link\"       => \"' . $kb_link . '\",
            \"training_link\" => \"' . $training_link . '\",
            \"auto_fixable\"  => false,
            \"threat_level\"  => 60,
            \"module\"        => \"' . $module . '\",
            \"priority\"      => ' . $priority . ',
            \"stub\"          => true,
        );
    }

    /**
     * IMPLEMENTATION PLAN
     * - Define signals and data sources.
     * - Compute risk/impact and surface KPIs (issues found, time saved).
     * - Suggest fixes and link to KB/Training.
     */
}
';
};

foreach ($defs as $ns => $items) {
    foreach ($items as $item) {
        if (!is_array($item) || count($item) < 5) {
            continue;
        }

        if (count($item) === 5) {
            [$slug, $class, $desc, $module, $priority] = $item;
            $title = str_replace('_', ' ', $class);
        } else {
            [$slug, $class, $title, $desc, $module, $priority] = $item;
        }
        $file = $base . '/class-diagnostic-' . $slug . '.php';
        file_put_contents($file, $template($ns, $slug, $class, $title, $desc, $module, $priority));
    }
}
