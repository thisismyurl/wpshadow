# Auto-Merge Workflow for Copilot PRs

This workflow automatically merges pull requests from the Copilot coding agent when they meet safety criteria, reducing manual review overhead for low-risk changes.

## Workflow File

`.github/workflows/auto-merge-copilot-prs.yml`

## Auto-Merge Criteria

All of the following must be true for a PR to be auto-merged:

1. **Author Check**: PR created by `copilot-swe-agent[bot]` or `Copilot` bot
2. **Conflict Check**: No merge conflicts (`mergeable: true`)
3. **CI Status**: All required checks passing OR PR only touches non-critical files
4. **File Safety**: PR doesn't modify security-critical files

## Safety Categories

### Always Safe (Auto-merge even without CI)

- Documentation files: `*.md`, `docs/**/*`
- DevContainer files: `.devcontainer/**/*`
- Test files: `tests/**/*`, `*Test.php`, `*-test.php`
- Configuration samples: `*.example`, `*.sample`
- GitHub templates: `.github/CODEOWNERS`, `.github/PULL_REQUEST_TEMPLATE`

### Safe with Passing CI

- GitHub workflows: `.github/workflows/**/*`
- Assets: `assets/**/*` (CSS, JS, images)
- Non-executable scripts: `bin/**/*.sh` (if not in critical list)
- Views: `includes/views/**/*`

### Never Auto-Merge (Require Human Review)

Security-critical files that always require manual review:

- `wpshadow.php` (main plugin file)
- `includes/core/class-plugin-bootstrap.php`
- `includes/core/class-ajax-router.php`
- `includes/core/class-database-migrator.php`
- Any file with `ajax` in path
- Any file with `auth` in name
- Database migrations
- SQL query files

## Labels

The workflow automatically adds these labels:

- **`auto-merge-ready`** - PR is safe to auto-merge
- **`docs-only`** - PR only touches documentation/tests
- **`needs-review`** - PR requires human review (critical files)

## Behavior Examples

### Example 1: Documentation PR

```
Files: README.md, docs/CONTRIBUTING.md
Result: ✅ Auto-merged immediately (safe-docs)
Label: auto-merge-ready, docs-only
```

### Example 2: New Feature with Tests

```
Files: includes/features/new-feature.php, tests/test-new-feature.php
Result: ⏳ Waits for CI, then ✅ auto-merges if passing
Label: auto-merge-ready
```

### Example 3: Core Security File

```
Files: includes/core/class-ajax-router.php
Result: ❌ Blocked, needs human review
Label: needs-review
Comment: "This PR requires human review due to security-critical files"
```

### Example 4: Merge Conflict

```
Files: Any
Mergeable: false
Result: ❌ Blocked, needs conflict resolution
Label: needs-review
Comment: "This PR requires human review due to merge conflicts"
```

## Workflow Triggers

The workflow activates on:

1. **New PRs from Copilot** - When a new PR is opened
2. **Updates to existing Copilot PRs** - When commits are pushed
3. **CI check completions** - When CI status changes

## Testing

You can test the workflow by:

1. Creating a documentation-only PR and watching it auto-merge
2. Creating a PR that modifies test files
3. Creating a PR that touches critical files and verifying it's blocked

## Disabling Auto-Merge

To temporarily disable auto-merge for a specific PR, add the `no-auto-merge` label.

## Success Criteria

- ✅ Workflow file created and validated
- ✅ Proper permissions set (`contents: write`, `pull-requests: write`)
- ✅ Labels defined and automatically applied
- ✅ Safety checks implemented (critical files, merge conflicts, draft status)
- ✅ Comments added for transparency
- ✅ Error handling included

## Security

This workflow uses `pull_request_target` which runs in the context of the base repository with write permissions. It's restricted to only run for PRs created by specific bot accounts to prevent unauthorized access.

## Permissions

The workflow requires:

- `contents: write` - To merge PRs
- `pull-requests: write` - To add labels and comments
