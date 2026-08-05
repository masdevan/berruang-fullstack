# Project Structure

```
berruang/
├── .editorconfig
├── .env.example
├── .env
├── .github/
│   └── workflows/
│       ├── lint.yml
│       └── tests.yml
├── .gitignore
├── .npmrc
├── CODE_OF_CONDUCT.md
├── LICENSE.md
├── artisan
├── composer.json
├── composer.lock
├── package.json
├── package-lock.json
├── phpunit.xml
├── pint.json
├── rules.md
├── structure.md
├── vite.config.ts
├── app/
│   ├── Events/
│   │   ├── MessageRead.php
│   │   ├── MessageSent.php
│   │   ├── TypingEvent.php
│   │   └── UserStatusChanged.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php
│   │   │   ├── HomeController.php
│   │   │   ├── Auth/
│   │   │   │   ├── ForgotPasswordController.php
│   │   │   │   ├── LoginController.php
│   │   │   │   ├── RegisterController.php
│   │   │   │   ├── ResetPasswordController.php
│   │   │   │   ├── SetupProfileController.php
│   │   │   │   └── VerifyEmailController.php
│   │   │   ├── Chat/
│   │   │   │   ├── ChatController.php
│   │   │   │   ├── ContactController.php
│   │   │   │   ├── DraftController.php
│   │   │   │   ├── MessageController.php
│   │   │   │   ├── StatusController.php
│   │   │   │   ├── TypingController.php
│   │   │   │   └── WorkspaceController.php
│   │   │   └── Profile/
│   │   │       ├── AccountController.php
│   │   │       └── PasswordController.php
│   │   └── Middleware/
│   │       └── EnsureOnboarded.php
│   ├── Mail/
│   │   ├── PasswordResetCodeMail.php
│   │   └── VerificationCodeMail.php
│   ├── Models/
│   │   ├── EmailCode.php
│   │   ├── Message.php
│   │   ├── User.php
│   │   └── Workspace.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   └── Services/
│       ├── AuthService.php
│       ├── ChatService.php
│       ├── EmailCodeService.php
│       ├── PasswordResetService.php
│       ├── ProfileService.php
│       └── WorkspaceService.php
├── bootstrap/
│   ├── app.php
│   └── providers.php
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── broadcasting.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── reverb.php
│   ├── services.php
│   └── session.php
├── database/
│   ├── factories/
│   │   └── UserFactory.php
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_07_28_084609_add_username_to_users_table.php
│   │   ├── 2026_07_31_000001_create_email_codes_table.php
│   │   ├── 2026_07_31_000002_add_avatar_to_users_table.php
│   │   ├── 2026_07_31_000003_add_username_changed_at_to_users_table.php
│   │   ├── 2026_08_01_000001_add_bio_to_users_table.php
│   │   ├── 2026_08_01_000002_change_bio_length_on_users_table.php
│   │   ├── 2026_08_01_000003_create_contacts_table.php
│   │   ├── 2026_08_01_000004_add_names_to_contacts_table.php
│   │   ├── 2026_08_01_000005_split_name_to_first_last_on_users_table.php
│   │   ├── 2026_08_01_000006_add_onboarded_at_to_users_table.php
│   │   ├── 2026_08_01_000007_create_messages_table.php
│   │   ├── 2026_08_02_000001_add_read_at_to_messages_table.php
│   │   ├── 2026_08_02_000002_add_indexes_to_messages_table.php
│   │   ├── 2026_08_03_000001_add_type_and_file_to_messages_table.php
│   │   ├── 2026_08_03_000002_add_dimensions_to_messages_table.php
│   │   ├── 2026_08_03_000003_backfill_message_dimensions.php
│   │   ├── 2026_08_04_000001_add_preview_paths.php
│   │   ├── 2026_08_04_000002_backfill_preview_paths.php
│   │   └── 2026_08_05_000001_create_workspaces_tables.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── public/
│   ├── .htaccess
│   ├── apple-touch-icon.png
│   ├── favicon.ico
│   ├── favicon.svg
│   ├── favicon/
│   │   ├── apple-touch-icon.png
│   │   ├── favicon-96x96.png
│   │   ├── favicon.ico
│   │   ├── favicon.svg
│   │   ├── site.webmanifest
│   │   ├── web-app-manifest-192x192.png
│   │   └── web-app-manifest-512x512.png
│   ├── index.php
│   ├── logo.png
│   └── robots.txt
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   ├── auth.js
│   │   ├── avatar-picker.js
│   │   ├── chat.js
│   │   ├── chat-layout.js
│   │   ├── profile.js
│   │   ├── register.js
│   │   ├── setup-profile.js
│   │   ├── verify-email.js
│   │   ├── icons/
│   │   │   ├── check-done.js
│   │   │   ├── check-sent.js
│   │   │   ├── check.js
│   │   │   ├── doc-lg.js
│   │   │   ├── doc.js
│   │   │   ├── download.js
│   │   │   ├── pencil.js
│   │   │   ├── play.js
│   │   │   ├── spinner.js
│   │   │   ├── video.js
│   │   │   └── x.js
│   │   └── chat/
│   │       ├── add-user.js
│   │       ├── bubbles.js
│   │       ├── constants.js
│   │       ├── draft.js
│   │       ├── idle.js
│   │       ├── messaging.js
│   │       ├── modal.js
│   │       ├── realtime.js
│   │       ├── search.js
│   │       ├── section-info.js
│   │       ├── shared-media.js
│   │       ├── sidebar.js
│   │       ├── unread.js
│   │       └── workspace.js
│   └── views/
│       ├── auth/
│       │   ├── forgot-password.blade.php
│       │   ├── login.blade.php
│       │   ├── register.blade.php
│       │   ├── reset-password.blade.php
│       │   ├── setup-profile.blade.php
│       │   └── verify-email.blade.php
│       ├── chat/
│       │   └── index.blade.php
│       ├── components/
│       │   ├── auth/
│       │   │   ├── alert.blade.php
│       │   │   ├── button.blade.php
│       │   │   ├── code-input.blade.php
│       │   │   └── input.blade.php
│   │       ├── chat/
│   │       │   ├── conversation-item.blade.php
│   │       │   ├── conversation-list.blade.php
│   │       │   ├── conversation-list-items.blade.php
│   │       │   ├── message-area.blade.php
│   │       │   ├── message-input.blade.php
│   │       │   ├── right-sidebar.blade.php
│   │       │   ├── section-label.blade.php
│   │       │   └── workspace-list.blade.php
│       │   ├── emails/
│       │   │   └── layout.blade.php
│       │   ├── icons/
│       │   │   ├── camera.blade.php
│       │   │   ├── chat-bubble.blade.php
│       │   │   ├── check.blade.php
│       │   │   ├── chevron-left.blade.php
│       │   │   ├── contact.blade.php
│       │   │   ├── dots-grid.blade.php
│       │   │   ├── download.blade.php
│       │   │   ├── eye.blade.php
│       │   │   ├── eye-off.blade.php
│       │   │   ├── file-archive.blade.php
│       │   │   ├── file-doc.blade.php
│       │   │   ├── file-image.blade.php
│       │   │   ├── file-video.blade.php
│       │   │   ├── ghost.blade.php
│       │   │   ├── google.blade.php
│       │   │   ├── help.blade.php
│       │   │   ├── info.blade.php
│       │   │   ├── join.blade.php
│       │   │   ├── location.blade.php
│       │   │   ├── play.blade.php
│       │   │   ├── plus.blade.php
│       │   │   ├── search.blade.php
│       │   │   ├── send.blade.php
│       │   │   ├── settings.blade.php
│       │   │   ├── spinner.blade.php
│       │   │   ├── workspace.blade.php
│       │   │   └── x.blade.php
│       │   ├── avatar-picker.blade.php
│       │   ├── modal.blade.php
│       │   ├── password-input.blade.php
│       │   └── text-input.blade.php
│       ├── emails/
│       │   ├── password-reset-code.blade.php
│       │   └── verification-code.blade.php
│       ├── layouts/
│       │   ├── app.blade.php
│       │   └── auth.blade.php
│       ├── profile/
│       │   └── index.blade.php
│       └── welcome.blade.php
├── routes/
│   ├── channels.php
│   ├── console.php
│   └── web.php
├── scripts/
│   └── kill-dev-ports.php
├── storage/
│   ├── app/
│   │   ├── private/
│   │   └── public/
│   │       ├── avatars/
│   │       └── uploads/
│   ├── framework/
│   │   ├── cache/
│   │   │   └── data/
│   │   ├── sessions/
│   │   ├── testing/
│   │   └── views/
│   └── logs/
└── tests/
    ├── Feature/
    │   ├── Auth/
    │   │   ├── EmailTemplatesTest.php
    │   │   ├── EmailVerificationTest.php
    │   │   ├── LoginTest.php
    │   │   ├── PasswordResetTest.php
    │   │   ├── RegistrationTest.php
    │   │   └── ResetStatusDuplicateTest.php
    │   ├── ChatTest.php
    │   ├── ContactTest.php
    │   ├── DraftTest.php
    │   ├── ExampleTest.php
    │   ├── MessageTest.php
    │   ├── PageTest.php
    │   ├── ProfileTest.php
    │   ├── SetupProfileTest.php
    │   ├── StatusTest.php
    │   ├── TypingTest.php
    │   └── WorkspaceTest.php
    ├── Unit/
    │   └── ExampleTest.php
    ├── Pest.php
    └── TestCase.php
```
