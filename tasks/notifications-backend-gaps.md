# Notifications — backend gaps for the frontend integration

The notifications page (`resources/js/pages/notifications/index.tsx`) now renders
the real `threads` prop from `NotificationController::index`
(`NotificationService::getMyNotifications`). Mock data is removed. The shape we
consume matches `storage/app/private/notifications-index-data.json`.

The items below are **not available from the backend yet**. Until they land, the
listed interactions run as **client-side optimistic state only** (they do not
persist across a reload).

## 1. No write endpoints

Every interactive action lacks a route/controller action. The `NotificationService`
already has some of the domain logic, but nothing is wired to HTTP:

| Action (frontend) | Service method that exists | What's missing |
|---|---|---|
| Reply to a thread | `replyToGenericNotification()` | route + controller action + FormRequest + policy |
| Mark thread read (on open) | `markAsRead(Notification, userId)` | route + action; ideally a per-thread variant |
| Mark thread **unread** | — | service method + route + action |
| Mark **all** as read | — | service method + route + action |
| Approve / reject ownership/invitation | response is created on send only | route + action to **update** `NotificationThreadResponse` |

Suggested: `POST notifications/{thread}/reply`, `PATCH notifications/{thread}/read`,
`PATCH notifications/{thread}/unread`, `PATCH notifications/read-all`,
`PATCH notifications/{thread}/respond` (accepted|rejected). All authorized via a
`NotificationThreadPolicy`.

## 2. Response status — ✅ RESOLVED

`project_ownership` / `project_invitation` threads now include a
`"response": "accepted" | "rejected" | "unreplied"` field (sourced from
`notification_thread_responses`). The frontend uses it to decide the
Approve/Reject footer state: `unreplied` → active buttons; `accepted`/`rejected`
→ both buttons disabled with the chosen one highlighted.

Note: this only reflects the *current* status on load. Persisting a new decision
the user makes in the UI still requires the write endpoint described in item #1.

## 3. `sender_role` is the user's *global* role, not the project-contextual one

Each notification serializes `sender_role` from the sender's **global** role
(`annotator`, `annotation-manager`, `admin`). The design needs the
**project-contextual** role badge (Annotator / Manager / Owner) — and the same
user shows different badges in different threads in the mockups (e.g. `robertDowny`
is "Owner" on one thread and "Manager" on another).

The frontend currently best-effort maps `annotation-manager → manager` and renders
**no badge** for unmapped roles (e.g. `admin`). Please send the contextual role per
notification (one of `annotator` / `manager` / `owner`).

## 4. System-message bodies are literal English strings

Notice/announcement/action bodies (info, warning, announcement, ownership,
invitation) are stored/sent as literal English text, so they cannot be localized.

Translation templates with `:placeholders` have been added to
`lang/{en,el}/notifications.php` under the `messages.*` key:

- `messages.profile_edited.{title,body}` — `:editor`
- `messages.added_to_project.{title,body}` — `:editor`
- `messages.overdue_approaching.{title,body}` — `:subproject`, `:days`
- `messages.subproject_overdue.{title,body}` — `:subproject`
- `messages.announcement` — `:username`
- `messages.project_ownership` / `messages.project_ownership_question` — `:project`
- `messages.project_invitation` / `messages.project_invitation_question` — `:project`

Please render these server-side via `trans('notifications.messages.…', [...])`
when generating the notifications (the `create*Notification` service methods),
**or** send a message key + params so the frontend can localize. Confirm which
approach you prefer.

## 5. Minor

- No `recipient_user_id` per message — dropped frontend-side (ownership of a
  message is derived from `sender_user_id === auth.user.id`). Flag if you need it.
- `datetime` is raw `Y-m-d H:i:s`; the frontend formats it for display. Fine as-is.
