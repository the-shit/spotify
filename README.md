# 💩 Spotify Component

> **S**tream **P**ower **O**ver **T**he **I**nternet, **F**orever **Y**ours

## Here It Is

A Spotify control component for THE SHIT (Scaling Humans Into Tomorrow). Command-line music control that keeps you in your flow. No context switching, no distractions, just pure control over your soundtrack.

## The Essentials

- **Playback Control** - Play, pause, skip, previous. Direct and immediate.
- **Smart Search** - Find tracks, artists, albums, playlists instantly
- **Queue Management** - See what's coming, add what should be next
- **Device Switching** - Control any device from your terminal
- **Now Playing** - Current track info without leaving your workspace
- **Persistent Auth** - Authenticate once, control indefinitely

## Installation

```bash
# From THE SHIT core
php 💩 component:install spotify

# Or standalone
git clone https://github.com/the-shit/spotify.git 💩-components/spotify
cd 💩-components/spotify
composer install
```

## Setup

```bash
# Configure Spotify credentials
php spotify setup

# Authenticate with Spotify
php spotify login
```

Requirements:
- Spotify Premium account
- App credentials from [Spotify Developer Dashboard](https://developer.spotify.com/dashboard)
- Active Spotify session on any device

## Commands

```bash
# Playback
php spotify play                    # Resume playback
php spotify pause                   # Pause playback
php spotify skip                    # Next track
php spotify previous                # Previous track

# Search & Play
php spotify play "search query"     # Quick search and play
php spotify play --artist "name"    # Play artist radio
php spotify play --album "title"    # Play full album
php spotify play --playlist "mood"  # Play playlist

# Queue
php spotify queue                   # View upcoming tracks
php spotify queue "track name"      # Add to queue

# Info
php spotify current                 # Currently playing

# Devices
php spotify devices                 # List available devices
php spotify devices --switch <id>   # Switch playback device
```

## Human-AI Collaboration

Output adapts to context:

```bash
# JSON for automation
CONDUIT_USER_AGENT=ai php spotify current

# Formatted for humans
php spotify current
```

## Architecture

- **Laravel Zero** - Micro-framework foundation
- **OAuth 2.0 + PKCE** - Secure authentication flow
- **Token Persistence** - Seamless session management
- **Event Bus** - Component communication within THE SHIT
- **Smart Defaults** - Works out of the box

## The SHIT Philosophy

Tools should amplify, not complicate. This component gives you direct control over your music without the overhead. It's about maintaining momentum, staying in your zone, and letting the music flow while you work.

## Contributing

Found something? Fixed something? Open a PR. Keep it clean, keep it focused.

## License

MIT - Use it, modify it, ship it.

---

*For those who know that the right soundtrack makes everything better.*