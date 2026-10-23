---
name: system-audit
description: Comprehensive auditing procedure for security, performance, architecture, database integrity, business logic, configuration, dependency risks, code quality, and testing in Laravel/PHP and full-stack web applications.
---

# Comprehensive System Audit Skill

This skill guides the rigorous, non-destructive audit of a web application codebase, specifically tailored for Laravel, Livewire, and modern web architectures.

## Audit Principles
1. **Read-Only / Non-Destructive**: Do not modify application source code, configuration, or database records.
2. **Evidence-Based**: Every finding must cite exact file paths, line numbers, and code snippets.
3. **Severity Categorization**: Classify findings using standard CVSS/OWASP risk ratings:
   - **CRITICAL**: Immediate risk of remote code execution, authentication bypass, data loss, or secret exposure.
   - **HIGH**: Serious security vulnerability (SQLi, CSRF, IDOR, Privilege Escalation, Business logic financial errors).
   - **MEDIUM**: Configuration weakness, missing rate limits, potential XSS, N+1 performance bottlenecks, missing validation.
   - **LOW / INFORMATIONAL**: Code smell, outdated dependency without known active exploit, test gap, documentation drift.

## 9 Pillars of Audit

### 1. Application Security & OWASP Top 10
- Check authentication mechanisms, password hashing algorithms, session hijacking safeguards.
- Verify authorization checks on all controller and Livewire actions (gates, policies, `can` middleware).
- Inspect SQL injection vectors (`DB::raw()`, unescaped raw queries vs Eloquent parameterized queries).
- Check Cross-Site Scripting (XSS) in Blade (`{!! $var !!}` vs `{{ $var }}`) and Livewire properties.
- Check CSRF token verification across POST/PUT/DELETE routes.
- Examine Mass Assignment vulnerabilities (`$guarded = []` or unconstrained `$fillable`).

### 2. Business Logic & Tariff Calculation Integrity
- Inspect calculation engines (e.g. `PostalCalculator.php`, dimensional weight formulas, weight brackets).
- Verify edge cases: negative weight, 0g weight, extreme values, division by zero, floating point rounding errors (`bcmath` vs native floats).
- Validate currency calculations and currency exchange handling.
- Review history logging (`CalculatorHistory.php`) and audit trails.

### 3. Database Architecture & Query Performance
- Review schema definitions (`database/migrations/`, SQL dumps) for foreign keys, constraints, and data types (`DECIMAL` vs `FLOAT`).
- Identify missing indexes on frequently queried/filtered columns (e.g., query histories, dates, user lookups).
- Detect N+1 query problems in loops, controllers, and Livewire components.
- Check database transaction usage around multi-step state changes.

### 4. API, Routing & Middleware
- Audit route definitions in `routes/web.php` and `routes/api.php`.
- Check middleware application: `auth`, `verified`, `throttle:api`, `sanctum`.
- Verify FormRequest validation logic: ensure all inputs have strict validation rules (types, regex, bounds).
- Review API error responses to ensure no stack traces or internal implementation details leak in production.

### 5. Frontend, Blade & Livewire Audit
- Audit Livewire component public properties (ensure client cannot tamper with unauthorized state or recalculate prices arbitrarily).
- Check authorization inside Livewire methods (`mount()`, action handlers).
- Inspect file uploads or export handlers (e.g. PDF generation, DomPDF security settings).
- Review frontend bundle configurations (`vite.config.js`, Tailwind, PostCSS).

### 6. Environment, Configuration & Secrets
- Verify `.env.example` vs `.env`: ensure no real credentials, API keys, or production passwords exist in VCS.
- Review `APP_DEBUG`, `APP_ENV`, `APP_KEY`.
- Inspect cookie security settings: `SESSION_SECURE_COOKIE`, `SESSION_SAME_SITE`, `SESSION_HTTP_ONLY`.
- Inspect logging configurations: avoid logging PII, passwords, or credit card/payment data.

### 7. Infrastructure, Docker & Deployment
- Review `Dockerfile` and `docker-compose.yml`: check base image versions, root user execution, exposed ports.
- Review directory permissions for `storage/` and `bootstrap/cache/`.
- Review entrypoint scripts (`docker-entrypoint.sh`) for insecure commands or unhandled errors.

### 8. Dependencies & Supply Chain Security
- Analyze `composer.json` and `composer.lock` for vulnerable packages or unmaintained libraries.
- Analyze `package.json` and `package-lock.json` for npm package vulnerabilities.
- Review license compatibility (e.g., MIT, GPL, commercial restrictions).

### 9. QA, Test Coverage & Observability
- Evaluate PHPUnit test suites (`tests/Feature`, `tests/Unit`).
- Check test database isolation (`calcupost_test` vs production).
- Assess test coverage across critical calculation and authentication paths.
- Review application monitoring, telemetry, and error reporting tools (e.g. Laravel Pulse, Log Viewer).
