# User Story: Annotator Password Policy

## Summary

As an **admin**, I want to configure a password policy that applies specifically when creating or updating **annotator** accounts, so that I can enforce password requirements that match the security or operational needs of my annotation project (e.g. simpler passwords for non-technical users, or stricter passwords for sensitive data projects).

---

## Background

Currently, all user types (admin, annotation manager, annotator) share the same global password policy hardcoded in `AppServiceProvider`:

```php
Password::defaults(fn () => Password::min(8)->letters()->numbers());
```

There is no way to configure this without editing code. This story introduces a UI for admins to configure the policy that applies specifically and only to annotator accounts.

---

## Acceptance Criteria

### Rule: Minimum length
- [ ] Admin can set a minimum password length (e.g. 4–64 characters)
- [ ] Default: 8

### Rule: Character composition
Admin selects one of the following composition modes (mutually exclusive):

| Option | Description | Laravel equivalent |
|--------|-------------|-------------------|
| Letters only | Only alphabetic characters required | `Password::min(n)->letters()` |
| Letters and numbers | Alphanumeric, no special characters | `Password::min(n)->letters()->numbers()` |
| Letters, numbers, and symbols | Full complexity with at least one symbol | `Password::min(n)->letters()->numbers()->symbols()` |
| No restriction | Any characters accepted, only length enforced | `Password::min(n)` |

### Rule: Mixed case
- [ ] Admin can optionally require at least one uppercase and one lowercase letter (maps to `->mixedCase()`)
- [ ] Default: off

### Enforcement scope
- [ ] The configured policy applies when **creating** a new annotator account
- [ ] The configured policy applies when **updating** an annotator account's password
- [ ] The policy does **not** affect admin or annotation manager accounts (they retain the system-wide default)

### Settings UI
- [ ] Settings are accessible from a dedicated "Annotator Creation Password Policy" section within app settings (or user management settings)
- [ ] Only admins can view and edit these settings
- [ ] The form shows the current active policy
- [ ] A "Save" action persists the policy
- [ ] A live preview or summary of the resulting rule is shown to the admin (e.g. _"Passwords must be at least 8 characters and contain letters and numbers"_)
- [ ] Changes take effect immediately for new creations/updates (no restart required)

### Validation feedback
- [ ] When a password fails the policy during annotator creation/update, the error message is human-readable and reflects the active rules
- [ ] Error messages are translatable

### Persistence
- [ ] The policy is stored in the database (not in `.env` or `config/` files), so it can be changed at runtime without a deploy
- [ ] Sensible defaults are seeded on first run (minimum 8, letters + numbers)

### Testing
- [ ] Unit tests cover the password policy service logic
- [ ] Feature tests cover the settings UI and enforcement during annotator creation/update

---

## Out of Scope

- Per-project password policies (all annotators share one policy)
- Password expiry / rotation enforcement
- Password history (prevent reuse)
- Policies for admin or annotation manager roles
- Two-factor authentication

---

## Open Questions

- [ ] Should the policy UI live inside the existing "Settings" area, or as a subsection of "User Management"?
- [ ] Should policy changes trigger a notification or force a re-set for existing annotators whose passwords no longer meet the new rules?
- [ ] Is there a maximum length cap to enforce (to prevent e.g. bcrypt DoS via very long inputs)?

---

## Technical Notes

- Current global default: `app/Providers/AppServiceProvider.php` — `Password::defaults()`
- Annotator store validation: `app/Http/Requests/User/UserStoreAnnotatorRequest.php`
- Annotator update validation: `app/Http/Requests/User/UserUpdateAnnotatorRequest.php`
- Annotator creation form UI: `resources/js/pages/users/components/create-annotator/create-annotator-form.tsx`
- The implementation will likely introduce a `PasswordPolicy` model (or a settings table entry), a service to resolve the active annotator policy, and a settings controller + Inertia page.
