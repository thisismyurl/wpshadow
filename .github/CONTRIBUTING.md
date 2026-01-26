# Contributing to WPShadow

Thanks for your interest in contributing! This guide ensures contributions align with WPShadow's philosophy and quality standards.

## 🎯 Before You Start

**Read These First:**
1. [PRODUCT_PHILOSOPHY.md](../docs/PRODUCT_PHILOSOPHY.md) - Our 11 core commandments
2. [ROADMAP.md](../docs/ROADMAP.md) - Current phases and timeline
3. [GITHUB_WORKFLOW.md](../docs/GITHUB_WORKFLOW.md) - Label and issue strategy
4. [CODING_STANDARDS.md](../docs/CODING_STANDARDS.md) - Code style requirements

## 📝 Creating Issues

### For Bugs
- Use **Bug Report** template
- Add `bug/*` label (critical/security/ui/compatibility/integration)
- Add priority label (P0-Critical through P3-Low)
- If security-related, use P0-Critical + bug/security

### For Features
- Use **Feature Request** template
- Specify if it's a diagnostic, treatment, or workflow feature
- Link to related diagnostics (if applicable)
- Indicate effort estimate (effort/1h through effort/epic)
- Call out philosophy alignment

### For Questions
- Use **Question** template
- Reference what docs you've already checked
- Discussions are welcome!

## 🚀 Getting Started with Code

```bash
# Clone and setup
git clone https://github.com/thisismyurl/wpshadow.git
cd wpshadow

# See README.md for Codespaces development environment setup
cat README.md
```

## ✅ Code Standards

**All contributions must:**
- Pass `composer phpcs` (WordPress Coding Standards)
- Pass `composer phpstan` (static analysis - level 5)
- Include `declare(strict_types=1);` at file top
- Use `Treatment_Base` or `AJAX_Handler_Base` where applicable
- Have proper nonce/capability checks

**Run before committing:**
```bash
composer phpcs      # Check coding standards
composer phpstan    # Check type safety
php -l file.php     # Check syntax
```

## 📊 Adding Diagnostics

1. Create stub in `includes/core/Diagnostics/`
2. Implement `run()` and `get_result()` methods
3. Add KB article link in tooltip
4. Reference training video if available
5. Include KPI tracking placeholder
6. Mark persona applicability in comment

See [EXAMPLE_DIAGNOSTIC_IMPLEMENTATION.md](../docs/EXAMPLE_DIAGNOSTIC_IMPLEMENTATION.md)

## 🔧 Adding Treatments

1. Extend `Treatment_Base` class
2. Implement required abstract methods
3. Create backup before modifications
4. Include undo functionality
5. Test with `wp shell` before submitting

See [FEATURE_MATRIX_TREATMENTS.md](../docs/FEATURE_MATRIX_TREATMENTS.md)

## 🧪 Testing

```bash
# In Codespaces:
# Open WordPress and activate wpshadow
# Run diagnostics manually
# Test treatments and rollback
# Check activity logs
```

## 📋 Pull Request Process

1. **Link to issue** - Reference issue number in PR description
2. **Add labels** - Match issue labels (status/review, effort/*, etc)
3. **Test locally** - Run phpcs, phpstan, manual tests
4. **Update docs** - If adding diagnostics/treatments, update matrices
5. **Philosophy check** - Does it follow our commandments?

## 🏷️ Label Your PR

- Add **status/review** when creating PR
- Add effort estimate: effort/1h through effort/epic
- Add category: feature/diagnostic, quality/*, etc
- If closes an issue: mention "Closes #123"

## 💡 Philosophy First

Every contribution should demonstrate:
- ✅ **Helpful Neighbor** - Genuinely helps users, doesn't push sales
- ✅ **Free First** - Free features that don't need servers
- ✅ **Educate** - Links to KB articles and training
- ✅ **Show Value** - Tracks impact/KPIs
- ✅ **Privacy** - Respects user data and consent

## 🤔 Not Sure Where to Start?

- Look for issues tagged `good first issue`
- Check `help wanted` for things needing community input
- Review [TOP_100_DIAGNOSTICS_FOR_TESTING.md](../docs/TOP_100_DIAGNOSTICS_FOR_TESTING.md) for diagnostic ideas
- Ask in GitHub Discussions!

## ❓ Questions?

- Check [docs/](../docs/) folder for detailed guides
- Read existing PRs and issues for patterns
- Start a Discussion in GitHub

---

**Thanks for helping make WordPress plugin development better!** 🙌
