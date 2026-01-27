# Card Usage Strategy

**Purpose:** establish consistent, accessible card patterns for WPShadow admin experiences (dashboard, reports, settings, tools) so users learn the layout once and reuse it everywhere.

## Card Types
- **CTA Card (action-first):** Prompts an action (e.g., run scan, enable feature). Includes icon, short impact text, primary/secondary buttons. Classes: `wps-cta-card`, `wps-cta-info|warning|success`.
- **Data Card (status/readout):** Presents metrics, lists, or tables. Classes: `wps-card`, optional `wps-card--highlight` for KPIs. Contains a heading and summary line before detailed content.
- **Form Card (inputs/settings):** Groups related inputs. Classes: `wps-card wps-form-card`. Each input uses `label` + `for` and inline help text.

## Layout Rules
- **Grid defaults:** Use `wps-grid wps-gap-4 wps-grid-auto-260` for multi-card layouts; single column on narrow viewports.
- **Spacing:** Use `wps-mb-4` between stacked cards. Inside cards use `wps-p-20` (or `wps-p-15` for compact tables).
- **Actions:** Place primary action on the left, secondary on the right (RTL mirrors automatically). Use `wps-btn-icon-left` when showing an icon.

## Accessibility Baseline
- Each card with interactive content gets `role="region"` and `aria-labelledby` pointing to its heading id.
- Headings are always present and unique (e.g., `<h2 id="card-mobile-heading">…</h2>`).
- Status/readout areas use `role="status"` + `aria-live="polite"` when content updates dynamically.
- Icon-only controls include `aria-label` or `title`; avoid relying on color alone for state.

## Implementation Checklist
- [ ] Choose card type per purpose (CTA/Data/Form).
- [ ] Wrap content in the appropriate card class; add heading with an id.
- [ ] Set `role="region" aria-labelledby="..."` when the card contains interactive elements or unique content.
- [ ] Ensure primary actions have clear text; add `aria-label` where more context helps screen readers.
- [ ] Add live regions for status updates (progress, results, errors) near the relevant card.
- [ ] Keep card width within the grid system; avoid full-width cards unless showing tables.

## Example (Form Card)
```php
<div class="wps-card wps-form-card" role="region" aria-labelledby="card-memory-heading">
    <h2 id="card-memory-heading">Update Memory Limit</h2>
    <p class="description">Adjust the PHP memory limit and log the change for rollback.</p>
    <label for="memory-limit" class="wps-form-label">Memory Limit</label>
    <input id="memory-limit" name="memory_limit" type="text" class="regular-text" value="256M" />
    <div class="wps-flex-gap-8 wps-mt-3">
        <button class="wps-btn wps-btn-primary">Save</button>
        <button class="wps-btn wps-btn-secondary" aria-label="Cancel without saving memory limit changes">Cancel</button>
    </div>
    <div id="memory-status" role="status" aria-live="polite" class="wps-mt-2"></div>
</div>
```

## Rollout Plan
1. **Tools/Reports:** Convert tool panels to `wps-card`/`wps-form-card` with region semantics and aria-labelled headings.
2. **Settings:** Ensure each settings group uses Form Card pattern with live status for saves.
3. **Dashboard:** Use CTA/Data cards consistently; keep scan CTAs as CTA cards and metrics as Data cards.
4. **Shared CSS:** Reuse existing `wps-card` styles; only introduce new modifiers when necessary to avoid drift.
