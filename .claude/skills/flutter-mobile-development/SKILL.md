---
name: flutter-mobile-development
description: Use when building or reviewing a Flutter app — widget structure, state management, and platform-specific considerations for iOS/Android.
---

# Flutter Mobile Development

## Widget structure

- Split large `build()` methods into smaller private widgets rather than one deeply nested tree — it improves readability and lets Flutter's rebuild optimization skip unaffected subtrees.
- Prefer `const` constructors wherever a widget's inputs are compile-time constant — it's a real performance win since Flutter can skip rebuilding const subtrees entirely.
- Separate presentation widgets from logic: keep business logic in a state-management layer (Provider, Riverpod, Bloc) rather than inline in widget build methods.

## State management

- Choose a single state-management approach for the project and use it consistently — mixing several ad hoc (setState here, a singleton there, a stream elsewhere) makes state flow hard to reason about.
- Scope state to the narrowest widget subtree that needs it; a value used by one screen doesn't belong in a global app-wide store.
- Keep async state (loading/error/data) modeled explicitly (e.g., a sealed class or enum with associated data) rather than juggling separate booleans that can drift out of sync.

## Platform considerations

- Test on both iOS and Android throughout development, not just at the end — platform-specific widgets, safe-area insets, and permission flows genuinely differ.
- Use `MediaQuery`/`LayoutBuilder` for responsive layouts instead of hardcoded pixel dimensions, so the UI adapts across phone/tablet form factors.
- Handle platform permission flows (camera, location, notifications) explicitly per platform — the request UX and required manifest/plist entries differ between Android and iOS.

## Performance

- Profile with Flutter DevTools before optimizing blindly — jank is often caused by a specific expensive widget rebuild or an unbounded list without `ListView.builder`'s lazy construction, not by "Flutter being slow" in general.
- Use `ListView.builder`/`GridView.builder` for any list with more than a handful of items instead of building the full list eagerly.

## Common pitfalls

- Don't call `setState` inside a build method or in response to every minor state change on a widget with expensive children — batch related state updates and scope rebuilds narrowly.
