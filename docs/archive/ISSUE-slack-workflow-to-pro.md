# Issue: Add Slack workflow action to WPShadow Pro (migrate from free)

**Summary**
- Slack workflow action was removed from the free/core plugin. Move it into WPShadow Pro.

**Scope / Requirements**
1. Add Slack action definition in Pro action registry (align with existing Pro patterns).
2. Add wizard option + form fields in Pro for Slack (webhook URL, message fields).
3. Add executor mapping and handler in Pro to POST to Slack webhook.
4. Capability check (`manage_options`) and sanitize/validate webhook URL before use.
5. UX: If a core workflow references Slack but Pro isn’t active, show a notice: “Slack action requires WPShadow Pro” (no fatal errors).
6. Update Pro UI copy/examples to mention Slack appropriately.

**References (where removed in core)**
- Removed from free registry: `includes/workflow/class-block-registry.php` (Slack block).
- Removed from wizard options/fields/mapping: `includes/workflow/class-workflow-wizard.php`.
- Slack mention removed from examples: `includes/views/workflow-list.php`.

**Acceptance Criteria**
- Pro users can configure Slack action (webhook + message) and workflows post to Slack successfully with valid webhook.
- Core remains Slack-free and does not break workflows; missing Slack action in core shows the Pro-required notice.

**Notes**
- Keep text domain `wpshadow`.
- Ensure handler sanitizes and escapes; validate webhook URL format.
- Add unit/e2e checks if coverage exists for workflow actions.
