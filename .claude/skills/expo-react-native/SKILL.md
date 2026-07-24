---
name: expo-react-native
description: Use when building, configuring, or deploying a React Native app with Expo — navigation, native modules, EAS builds, and OTA updates.
---

# Expo / React Native

## Project setup

- Use the latest Expo SDK unless the project pins an older one; check `app.json`/`app.config.ts` for the current SDK version before adding native dependencies.
- Prefer Expo Router (file-based routing) for new projects; it maps directly to navigation structure and avoids hand-wired navigator boilerplate.
- Keep native-only code behind `Platform.OS` checks or platform-specific file extensions (`.ios.tsx`/`.android.tsx`) rather than runtime branching scattered through shared components.

## Native modules & config

- Check Expo's module compatibility table before adding a third-party native package — a package without an Expo config plugin usually means ejecting to a bare workflow or using `expo-dev-client`.
- Any native module requiring custom native code needs `expo-dev-client` (a custom dev build) — Expo Go alone can't load unlisted native modules.
- Keep permissions declarations (camera, location, etc.) in `app.json`/`app.config.ts` under the correct platform key; a missing permission string causes silent failures or store rejections, not crashes.

## Builds & deployment

- Use EAS Build for production binaries; configure build profiles (`development`, `preview`, `production`) in `eas.json` rather than one-off local flags.
- Use EAS Update for JS-only OTA updates — but remember native code changes (new native modules, SDK upgrades) always require a fresh store build, OTA can't ship those.
- Test a release build (not just Expo Go / dev client) before submitting — dev-only warnings and Metro's fast refresh can mask real production behavior.

## Common pitfalls

- Don't assume Expo Go supports every package — always verify against the current SDK's compatibility list first.
- Version-lock Expo SDK and React Native together; upgrading one without the other's SDK-matched dependencies via `expo install` causes native-JS mismatches.
