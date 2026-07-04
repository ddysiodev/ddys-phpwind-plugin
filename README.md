# DDYS API PHPWind Extension

English | [简体中文](README.zh-CN.md)

Official PHPWind extension for the [DDYS](https://ddys.io/) API. It adds frontend app pages, post shortcodes, a WindEditor insert button, a local JSON proxy, caching, diagnostics, and a server-side request form without exposing the API Key in the browser.

- Repository: [ddysiodev/ddys-phpwind-plugin](https://github.com/ddysiodev/ddys-phpwind-plugin)
- GitHub Release: [v0.1.0](https://github.com/ddysiodev/ddys-phpwind-plugin/releases/tag/v0.1.0)
- Download ZIP: [ddys-phpwind-plugin-v0.1.0.zip](https://github.com/ddysiodev/ddys-phpwind-plugin/releases/download/v0.1.0/ddys-phpwind-plugin-v0.1.0.zip)
- Extension directory: `ddys_open`
- Target: PHPWind 9 AppCenter extension system
- Distribution: GitHub Release ZIP

## Features

- AppCenter manifest with PHPWind 9 metadata, extension resources, install service, main navigation registration, admin menu, editor app, UBB conversion hook, and read-page fallback hook.
- Admin settings for API Base URL, source site URL, API Key, timeout, cache TTLs, default count, theme, layout, navigation snippet, and request form.
- Admin diagnostics for connection tests, cache status, cache clearing, endpoint inspection, and shortcode/page/proxy generation.
- Frontend pages for movies, latest, hot, search, suggestions, calendar, movie detail, sources, related items, comments, collections, collection detail, shares, share detail, requests, activities, users, types, genres, and regions.
- Post shortcode rendering for `[ddys_*]` tags through PHPWind UBB conversion and read-page fallback.
- WindEditor toolbar button for inserting common DDYS shortcodes.
- Local JSON proxy under the PHPWind site domain, keeping the API Key server-side.
- Server-side request submission with nonce validation, rate limiting, field validation, and clear errors.
- Per-endpoint file caching with separate TTLs for dictionaries, fresh lists, details, and community data.
- Safety checks for PHPWind entry guards, route allowlists, parameter allowlists, escaped output, media URL validation, timeouts, cache isolation, and sensitive settings.

## Installation

1. Download `ddys-phpwind-plugin-v0.1.0.zip` from Releases.
2. In the PHPWind admin panel, open AppCenter or local application installation, then upload the ZIP.
3. Or unzip manually and upload `ddys_open` to `src/extensions/ddys_open`, then install it from AppCenter.
4. Confirm that PHPWind copies extension resources to `themes/extres/ddys_open`.
5. Open `低端影视 API` in the admin AppCenter menu and configure API Base URL, cache TTLs, display options, and the request form.
6. To enable request submission, set an API Key and run the connection test.

The release ZIP contains a top-level `ddys_open/` directory because PHPWind's installer expects an application folder at the archive root.

## Frontend Routes

Default dynamic entries:

```text
index.php?m=app&app=ddys_open
index.php?m=app&app=ddys_open&c=index&a=run&view=movies&type=movie&genre=drama&region=us&year=2026&sort=latest&page=1&per_page=12
index.php?m=app&app=ddys_open&c=index&a=run&view=hot
index.php?m=app&app=ddys_open&c=index&a=run&view=search
index.php?m=app&app=ddys_open&c=index&a=run&view=suggest&q=interstellar
index.php?m=app&app=ddys_open&c=index&a=run&view=calendar
index.php?m=app&app=ddys_open&c=index&a=run&view=movie&slug=this-tempting-madness
index.php?m=app&app=ddys_open&c=index&a=run&view=sources&slug=this-tempting-madness
index.php?m=app&app=ddys_open&c=index&a=run&view=related&slug=this-tempting-madness
index.php?m=app&app=ddys_open&c=index&a=run&view=comments&slug=this-tempting-madness
index.php?m=app&app=ddys_open&c=index&a=run&view=collections
index.php?m=app&app=ddys_open&c=index&a=run&view=collection&slug=editor-choice
index.php?m=app&app=ddys_open&c=index&a=run&view=shares
index.php?m=app&app=ddys_open&c=index&a=run&view=share&id=1
index.php?m=app&app=ddys_open&c=index&a=run&view=requests
index.php?m=app&app=ddys_open&c=index&a=run&view=activities
index.php?m=app&app=ddys_open&c=index&a=run&view=user&username=demo
index.php?m=app&app=ddys_open&c=index&a=run&view=types
index.php?m=app&app=ddys_open&c=index&a=run&view=genres
index.php?m=app&app=ddys_open&c=index&a=run&view=regions
```

Local proxy examples:

```text
index.php?m=app&app=ddys_open&c=index&a=api&route=latest&limit=6
index.php?m=app&app=ddys_open&c=index&a=api&route=movie&slug=this-tempting-madness
index.php?m=app&app=ddys_open&c=index&a=api&route=collections&page=1
index.php?m=app&app=ddys_open&c=index&a=api&route=shares&page=1
index.php?m=app&app=ddys_open&c=index&a=api&route=user&username=demo
```

Request form endpoint:

```text
index.php?m=app&app=ddys_open&c=index&a=request
```

Admin settings route:

```text
admin.php?m=app&app=ddys_open&c=manage&a=run
```

## Shortcodes

```text
[ddys_latest limit="12"]
[ddys_hot limit="10"]
[ddys_search]
[ddys_suggest q="interstellar" limit="8"]
[ddys_calendar year="2026" month="7"]
[ddys_movie slug="this-tempting-madness"]
[ddys_sources slug="this-tempting-madness"]
[ddys_related slug="this-tempting-madness"]
[ddys_comments slug="this-tempting-madness"]
[ddys_collections page="1"]
[ddys_collection slug="editor-choice"]
[ddys_shares page="1"]
[ddys_share id="1"]
[ddys_requests page="1"]
[ddys_activities page="1"]
[ddys_user username="demo"]
[ddys_types]
[ddys_genres]
[ddys_regions]
[ddys_request_form]
```

Full movie list example:

```text
[ddys_movies type="movie" genre="drama" region="us" year="2026" sort="latest" page="1" per_page="12"]
```

## Cache

Runtime cache files are stored in:

```text
src/extensions/ddys_open/cache
```

The extension writes `.htaccess` and `index.html` to reduce direct access risk. Cache can be cleared from the admin settings page.

## Development Check

Run from this repository root:

```powershell
node tools/check.mjs
```

The check covers PHPWind extension structure, Manifest metadata, injected services, controllers, admin page, frontend page, shortcode coverage, proxy, request form, cache, safety boundaries, UTF-8 encoding, icon dimensions, and sensitive files.
