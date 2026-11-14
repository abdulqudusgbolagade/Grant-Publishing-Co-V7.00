# Grant Publishing Co. 7.0.0

The Grant Publishing Co. website, redesigned with a light, modern SaaS look: bright surfaces, soft gradients, bento cards, floating 3D books, a WebGL gradient background, scroll reveals, parallax, cursor effects and micro-interactions.

This repository ships the site two ways from one source:

| Use | What you get |
| --- | --- |
| **Vercel (static site)** | `npm run build` renders all 26 pages into `dist/`. Deploy with the Vercel button or `vercel`. |
| **WordPress plugin** | `npm run package:plugin` creates `release/grant-publishing-site-7.0.0.zip` to upload in Plugins → Add New → Upload Plugin. |

Vercel cannot run WordPress or PHP, so the Vercel version is a static build of the same page templates. The WordPress plugin stays the full CMS version.

## Deploy on Vercel

1. Push this repository to GitHub.
2. In Vercel choose **Add New → Project** and import the repository.
3. Leave the settings as they are. `vercel.json` already sets the build command (`node scripts/build.mjs`) and the output folder (`dist`).
4. Optional environment variables (Project → Settings → Environment Variables):

| Variable | Purpose |
| --- | --- |
| `SITE_URL` | Your public address, for canonical links and the sitemap. Default `https://grantpublishingco.com`. |
| `WEB3FORMS_ACCESS_KEY` | Sends enquiry forms to your inbox through [Web3Forms](https://web3forms.com). Without it the form opens the visitor's email app. |
| `CONTACT_EMAIL`, `CONTACT_WHATSAPP`, `CONTACT_LINKEDIN`, `CONTACT_UPWORK` | Override the contact details shown in the header, footer and forms. |

5. Add your domain under Project → Settings → Domains.

Preview locally:

```bash
npm run build
npm run preview   # http://localhost:4173
```

Node 18 or newer is the only requirement. There are no npm dependencies.

## Install the WordPress plugin

```bash
npm run package:plugin
```

Upload `release/grant-publishing-site-7.0.0.zip` in Plugins → Add New → Upload Plugin and replace the current Grant plugin. Keep it active, clear any cache, then check `ver=7.0.0` on the stylesheet and script requests. Pages, forms, Elementor layouts and SEO values are untouched. The 7.0 design loads after the 6.x styles, so rolling back is a matter of reinstalling the 6.0.0 zip.

## Project layout

```
assets/               Images, fonts, 6.x bundle, and the 7.0 layer
  v7.src.css          Editable design source
  v7.css              Generated. Run: npm run css
  v7.js               Reveals, parallax, tilt, cursor, magnetic buttons, marquee
  v7-webgl.js         WebGL gradient backgrounds and the 3D book viewer
templates/, partials/ Page content (shared by WordPress and the static build)
pages.json            Page titles, slugs and descriptions
includes/             WordPress plugin PHP
scripts/build.mjs     Static site builder for Vercel
tools/build-css.py    Expands v7.src.css into v7.css
vercel.json           Vercel configuration and headers
```

## Editing the design

Edit `assets/v7.src.css`, then run `npm run css`. The `@@` prefix in that file expands to a high-specificity selector, and its declarations are marked `!important`, so the layer wins over the 6.x themes without touching them. GitHub Actions fails if `v7.css` is out of date.

## What changed in 7.0.0

- New light SaaS design system: ink and gradient buttons, white glass header, pastel gradient hero, bento service cards, soft shadows, one vivid call-to-action panel.
- WebGL: a mouse-reactive gradient shader behind the hero and page headers, and a real-time lit 3D book you can drag on the home page. Falls back to a CSS gradient if WebGL is unavailable.
- Motion: word-by-word headline reveals, scroll reveals, parallax, 3D card tilt, cursor ring with a "View" state, magnetic buttons, ripples and a scroll progress bar.
- Accessibility: all motion is switched off for visitors who prefer reduced motion, the 3D viewer works with the arrow keys, and focus states are visible.
- New static build and Vercel configuration.

## Notes

- The static build uses the page templates as the content source. WordPress-only filters (for example editorial copy rewrites applied at render time) are not part of it, so check the copy on each page before launch.
- Contact details default to the values in the plugin. Override them with the environment variables above.
