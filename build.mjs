#!/usr/bin/env node
/**
 * Static build for Vercel (or any static host).
 *
 * Renders every page template in /templates into plain HTML in /dist, using the
 * same assets and the 7.0 design layer as the WordPress plugin. No dependencies.
 *
 * Environment variables (all optional):
 *   SITE_URL                  Public URL, used for canonical links and the sitemap.
 *   WEB3FORMS_ACCESS_KEY      Delivers enquiries by email through Web3Forms.
 *                             Without it the form opens the visitor's email app.
 *   CONTACT_EMAIL, CONTACT_WHATSAPP, CONTACT_LINKEDIN, CONTACT_UPWORK
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const DIST = path.join(ROOT, 'dist');
const VERSION = JSON.parse(fs.readFileSync(path.join(ROOT, 'package.json'), 'utf8')).version;
const SITE_URL = (process.env.SITE_URL || 'https://grantpublishingco.com').replace(/\/+$/, '');
const FORM_KEY = process.env.WEB3FORMS_ACCESS_KEY || '';
const contact = {
  email: process.env.CONTACT_EMAIL || 'hello@grantpublishingco.com',
  whatsapp: process.env.CONTACT_WHATSAPP || '2349036016020',
  linkedin: process.env.CONTACT_LINKEDIN || 'https://www.linkedin.com/in/abdulqudustella/',
  upwork: process.env.CONTACT_UPWORK || 'https://www.upwork.com/freelancers/abdulqudust',
};

const read = (...p) => fs.readFileSync(path.join(ROOT, ...p), 'utf8');
const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
const pages = JSON.parse(read('pages.json'));

/* ------------------------------ routes ------------------------------ */
const pagePath = (key) => {
  const d = pages[key];
  if (!d) return '';
  return d.parent ? [pagePath(d.parent), d.slug].filter(Boolean).join('/') : d.slug;
};
const url = (key) => (key === 'home' || !pagePath(key) ? '/' : `/${pagePath(key)}/`);
const asset = (f) => `/assets/${path.basename(f)}`;

/* ---------------------------- small blocks ---------------------------- */
const directionIcon = '<svg class="gp-direction-icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 18 18 6M6 6h12v12"/></svg>';
const icon = (name) => read('assets', 'icons', `${name}.svg`);
const iconLink = (channel, href, label, external = false) =>
  `<a class="gp-social-link" href="${esc(href)}" aria-label="${esc(label)}" title="${esc(label)}"${external ? ' target="_blank" rel="noopener noreferrer"' : ''}>${icon(channel)}</a>`;
const contactLinks = (cls = 'gp-contact-links-list') =>
  `<div class="gp-social-links ${cls}" role="group" aria-label="Contact Grant Publishing Co.">` +
  iconLink('linkedin', contact.linkedin, 'AbdulQudus Tella on LinkedIn', true) +
  iconLink('upwork', contact.upwork, 'AbdulQudus Tella on Upwork', true) +
  iconLink('whatsapp', `https://wa.me/${contact.whatsapp}`, 'Message Grant Publishing Co. on WhatsApp') +
  iconLink('email', `mailto:${contact.email}`, 'Email Grant Publishing Co.') + '</div>';

function header(key) {
  const defs = pages;
  const nav = [['services', 'Services'], ['case-studies', 'Our Work'], ['about', 'About'], ['insights', 'Insights'], ['contact', 'Contact']]
    .map(([k, label]) => {
      const active = k === key || (defs[key] && defs[key].parent === k);
      return `<a href="${url(k)}"${active ? ` aria-current="${k === key ? 'page' : 'true'}"` : ''}>${label}</a>`;
    }).join('');
  const cta = `<a class="gp-button" href="${url('contact')}?request=project#gpc-enquiry">Get a free quote</a>`;
  return `<a class="gpc-skip-link" href="#gpc-main">Skip to content</a><header class="gp-header"><div class="gp-wrap gp-header-inner"><a class="gp-wordmark" href="/" aria-label="Grant Publishing Co. home"><img class="gp-logo" src="${asset('grant-header-128.webp')}" srcset="${asset('grant-header-128.webp')} 128w, ${asset('grant-header-256.webp')} 256w" sizes="(max-width: 720px) 56px, 64px" alt="Grant Publishing Co." width="64" height="64" decoding="async"></a><nav class="gp-nav" aria-label="Main navigation">${nav}</nav><div class="gp-header-cta">${cta}</div><details class="gp-mobile-nav"><summary>Menu</summary><nav aria-label="Mobile navigation">${nav}${cta}</nav></details></div></header><span id="gpc-main" tabindex="-1"></span>`;
}

function footer() {
  const links = [['services', 'Services'], ['case-studies', 'Our Work'], ['about', 'About'], ['feedback', 'Client Feedback'], ['insights', 'Insights'], ['faq', 'FAQs'], ['contact', 'Contact'], ['enquiry', 'Free book assessment']]
    .map(([k, l]) => `<a href="${url(k)}">${l}</a>`).join('');
  const legal = `<nav class="gp-footer-legal" aria-label="Legal"><a href="${url('privacy-policy')}">Privacy Policy</a><a href="${url('terms-of-service')}">Terms of Service</a></nav>`;
  return `<footer class="gp-footer" id="contact"><div class="gp-wrap"><div class="gp-footer-intro"><h2>Books built to<br>be discovered.</h2><a class="gp-button" href="${url('contact')}#gpc-enquiry">Discuss your project</a></div><div class="gp-footer-grid"><div class="gp-footer-identity"><a class="gp-footer-brand" href="/" aria-label="Grant Publishing Co. home"><img class="gp-logo" src="${asset('grant-footer-576.webp')}" alt="Grant Publishing Co." width="1448" height="1086" loading="lazy" decoding="async"></a><p>Book design, publishing and marketing for authors and publishers.<br>Led by AbdulQudus Tella.</p></div><div class="gp-footer-explore"><h3>Explore</h3><nav aria-label="Footer navigation" class="gp-footer-links">${links}</nav></div><div class="gp-footer-contact"><h3>Let’s connect</h3><p>Share your book, your goals<br>and the support you need.</p>${contactLinks('gp-footer-socials')}<a class="gp-footer-email" href="mailto:${esc(contact.email)}">${esc(contact.email)}</a><p class="gp-footer-contact-note">Choose a channel to start<br>a direct conversation.</p></div></div><div class="gp-footer-bottom"><p class="gp-footer-fine">© ${new Date().getFullYear()} Grant Publishing Co. All rights reserved.</p>${legal}</div></div></footer>`;
}

const SERVICES = ['Book formatting', 'Cover design', 'Book publishing and Amazon KDP setup', 'Amazon Ads campaign setup', 'Amazon Ads management', 'Amazon listing optimization', 'Book descriptions and A+ Content', 'Book launch or relaunch', 'Author platform', 'Series and catalog strategy', 'Book marketing strategy', 'Publishing consultation', 'Not sure yet'];
const SERVICE_LABELS = { 'Book formatting': 'Book formatting: ebook, paperback & hardcover', 'Cover design': 'Book cover design', 'Amazon Ads management': 'Amazon Ads ongoing management', 'Amazon listing optimization': 'Amazon Book Visibility / listing optimisation', 'Publishing consultation': 'Publishing consultation' };

function form(defaultRequest) {
  const opts = SERVICES.map((s) => `<option value="${esc(s)}">${esc(SERVICE_LABELS[s] || s)}</option>`).join('');
  return `<form class="gpc-audit-form gpc-enquiry-form gpc-static-form" id="gpc-enquiry" action="https://api.web3forms.com/submit" method="post" data-contact-email="${esc(contact.email)}" data-default-request="${defaultRequest}">
<input type="hidden" name="access_key" value="${esc(FORM_KEY)}"><input type="hidden" name="subject" value="New enquiry from the Grant Publishing Co. website"><input type="hidden" name="from_name" value="Grant Publishing Co. website">
<div class="gpc-honeypot" aria-hidden="true"><label for="gpc-fax">Leave this field empty</label><input id="gpc-fax" name="botcheck" type="checkbox" tabindex="-1" autocomplete="off" style="display:none"></div>
<p class="gpc-required-note">Fields marked * are required.</p>
<div class="gpc-audit-field"><label for="gpc-request">How can we help? *</label><select id="gpc-request" name="request" required><option value="project">Discuss a project</option><option value="assessment">Request a free initial book assessment</option></select></div>
<fieldset class="gpc-form-group"><legend>Your details</legend><div class="gpc-audit-form-row"><div class="gpc-audit-field"><label for="gpc-name">Your name *</label><input id="gpc-name" name="name" type="text" autocomplete="name" maxlength="120" required></div><div class="gpc-audit-field"><label for="gpc-email">Email address *</label><input id="gpc-email" name="email" type="email" autocomplete="email" maxlength="254" required></div></div></fieldset>
<fieldset class="gpc-form-group"><legend>Your goals</legend><div class="gpc-audit-field"><label for="gpc-message">What would you like help with? *</label><textarea id="gpc-message" name="message" maxlength="5000" required placeholder="Tell us about your book, the support you need or what you would like to improve."></textarea></div></fieldset>
<details class="gpc-book-details" data-book-details><summary>Book &amp; project details (optional)</summary><fieldset class="gpc-form-group"><legend>Your book &amp; project</legend>
<div class="gpc-audit-form-row"><div class="gpc-audit-field"><label for="gpc-book">Book title (optional)</label><input id="gpc-book" name="book" type="text" maxlength="240"></div><div class="gpc-audit-field"><label for="gpc-link">Amazon or book link (optional)</label><input id="gpc-link" name="book_url" type="url" placeholder="https://" maxlength="1000"></div></div>
<div class="gpc-audit-form-row"><div class="gpc-audit-field"><label for="gpc-service">Service of interest (optional)</label><select id="gpc-service" name="service"><option value="">Please select</option>${opts}</select></div><div class="gpc-audit-field"><label for="gpc-status">Publication status (optional)</label><select id="gpc-status" name="publication"><option value="">Please select</option><option>Already published</option><option>Preparing for launch</option><option>Relaunching a book</option><option>Still writing</option></select></div></div>
<div class="gpc-audit-field"><label for="gpc-website">Author website (optional)</label><input id="gpc-website" name="website" type="url" placeholder="https://" maxlength="1000"></div></fieldset></details>
<p class="gpc-audit-form-disclaimer">Free initial assessments use publicly available information on Amazon. Please include your Amazon book link if you are requesting an assessment. They do not include a manuscript review or access to your private sales data. For paid work, we may request a synopsis or manuscript to understand the book more fully.</p>
<div class="gpc-audit-field"><label class="gpc-consent" for="gpc-consent"><input id="gpc-consent" name="consent" type="checkbox" value="yes" required><span>I agree that Grant Publishing Co. may use these details to respond to my enquiry. *</span></label></div>
<p class="gpc-policy-link"><a href="${url('privacy-policy')}" target="_blank" rel="noopener noreferrer">Privacy Policy<span class="gpc-sr-only"> (opens in a new tab)</span></a></p>
<p class="gpc-next-step">We will review your enquiry and reply about the next step. Any paid work, deliverables, timing and fees are agreed before the project begins.</p>
<button class="gpc-audit-submit" type="submit">Send enquiry</button><p class="gpc-form-status" role="status" aria-live="polite" aria-atomic="true" tabindex="-1"></p>
<p>Prefer a direct conversation?</p>${contactLinks('gp-form-socials')}</form>`;
}

/* ---------------------------- framework & faq ---------------------------- */
const STAGES = {
  G: ['Ground the book in the right market.', 'Identify the intended reader, genre and market context.', 'We consider the book’s genre, intended reader and comparable reading choices. This gives the work a useful starting point before choosing a design direction or promotional channel.'],
  R: ['Refine the positioning.', 'Clarify what the book offers and why it is relevant to that reader.', 'We clarify the reading experience, central promise and reasons a reader might choose the book. Recommendations should reflect what the manuscript or supplied information actually supports.'],
  A: ['Align the listing and presentation.', 'Bring the cover, interior and public book information into agreement.', 'We connect positioning with the cover, interior, description and other listing details relevant to the agreed work. Design and formatting projects can focus on this stage without including publication or marketing.'],
  N: ['Navigate visibility channels.', 'Choose suitable routes to readers, including launches and advertising where appropriate.', 'We consider the channels that suit the book and its goals. A launch, author platform or advertising campaign is a choice to assess, rather than the automatic answer to every problem.'],
  T: ['Track growth and improve performance.', 'Review progress against an agreed baseline and make informed adjustments where included.', 'Where review is part of the scope, we agree a baseline, timeframe and suitable measures. Tracking depends on available client-authorised data. Observations inform adjustments; they do not establish guaranteed growth or prove that one change caused a result.'],
};
function framework(kind) {
  if (kind === 'assessment') {
    return `<p class="gp-framework-assessment" data-gpc-framework="assessment">The free assessment uses public Amazon-visible information to identify possible weaknesses and useful next steps. It is an initial diagnostic, not a complete five-stage engagement, access to private sales data or a promise of performance improvement. Learn how our <a href="${url('about')}#grant-framework">GRANT Framework</a> guides the wider work.</p>`;
  }
  const ctx = kind === 'about' ? 'about' : 'home';
  const intro = ctx === 'about'
    ? 'The GRANT Framework is our working method for diagnosing needs, recommending services and evaluating progress. Market fit informs positioning; positioning guides presentation; a clear book proposition helps us choose visibility channels. Review then informs the next decision where it is included.'
    : 'The GRANT Framework is our working method for finding your book’s next priority, from market fit and presentation to suitable visibility channels and agreed review.';
  const cards = Object.entries(STAGES).map(([l, s]) => `<div class="gp-step gp-framework-card"><div class="gp-framework-stage"><div class="gp-label"><p>${l}</p></div><div class="gp-item-title"><h3>${esc(s[0])}</h3></div><div class="gp-copy"><p>${esc(ctx === 'about' ? s[2] : s[1])}</p></div></div></div>`).join('');
  return `<div class="gp-framework gp-framework-${ctx}" data-gpc-framework="${ctx}"${ctx === 'about' ? ' id="grant-framework"' : ''}><div class="gp-copy"><p>${esc(intro)}</p></div><div class="gp-five-stage">${cards}</div>${ctx === 'home' ? `<div class="gp-action gp-secondary"><a class="elementor-button" href="${url('about')}#grant-framework"><span class="elementor-button-text">Explore the GRANT Framework</span></a></div>` : ''}</div>`;
}
const FAQ = JSON.parse(read('scripts', 'faq.json'));
const faqGroup = () => `<div class="gp-faq-list">${FAQ.map(([q, a]) => `<details class="gp-faq-item"><summary>${esc(q)}</summary><div class="gp-faq-answer"><p>${esc(a)}</p></div></details>`).join('')}</div>`;

/* ---------------------------- case cards ---------------------------- */
const CASES = [
  ['case-grandfather', 'My Dear Grandfather', 'Barsha Rai', 'my-dear-grandfather-302.webp', 302, 466, 'Description · Keyword research · Category review', 'Bring the story into focus on an Amazon page where plot and author information competed for attention.'],
  ['case-luma', 'Luma the Sleepy Star', 'John Capon', 'luma-final-800.webp', 800, 800, 'Cover redesign · Interior formatting', 'Bring an existing cover direction and illustrated manuscript together as a consistent paperback print package.'],
];
const caseCards = () => `<div class="gp-work-grid">${CASES.map((c) => `<article class="gp-work-card"><div class="gp-work-image"><img src="${asset(c[3])}" alt="${esc(c[1])} book cover, by ${esc(c[2])}" width="${c[4]}" height="${c[5]}" loading="lazy" decoding="async"></div><div class="gp-work-copy"><p class="gp-label">${esc(c[6])}</p><h3>${esc(c[1])}</h3><p>${esc(c[7])}</p><a class="gp-button gp-button-secondary" href="${url(c[0])}">Read the case study</a></div></article>`).join('')}</div>`;

/* ---------------------------- showcase ---------------------------- */
const BOOKS = [
  ['Luma the Sleepy Star', 'John Capon', 'Cover redesign & interior formatting', 'luma-final-800.webp', { 'luma-final-480.webp': 480, 'luma-final-800.webp': 800, 'luma-final-1000.webp': 1000 }, 1000, 1000, 'https://www.linkedin.com/services/page/a29863343146852122/', 'Read John’s review on LinkedIn'],
  ['My Dear Grandfather', 'Barsha Rai · Cactus Rain Publishing', 'Amazon listing optimisation', 'my-dear-grandfather-302.webp', { 'my-dear-grandfather-302.webp': 302 }, 302, 466, 'https://www.amazon.com/dp/1947646117', 'View the book on Amazon'],
  ['Kathryn’s Beach', 'Nadine Laman · Cactus Rain Publishing', 'Amazon assessment & recommendations', 'kathryns-beach-640.webp', { 'kathryns-beach-360.webp': 360, 'kathryns-beach-640.webp': 640 }, 1287, 2048, 'https://www.amazon.com/dp/1947646168', 'View the book on Amazon'],
  ['Under the African Sun', 'Gail Gilbride', 'Amazon assessment & improvement recommendations', 'under-the-african-sun-324.webp', { 'under-the-african-sun-324.webp': 324 }, 324, 500, 'https://www.amazon.com/dp/B01MSCNCIO', 'View the book on Amazon'],
];
function servicesShowcase() {
  const slides = BOOKS.map((b, i) => {
    const set = Object.entries(b[4]).map(([f, w]) => `${asset(f)} ${w}w`).join(', ');
    return `<div class="gp-showcase-slide${i === 0 ? ' is-active' : ''}" data-slide role="group" aria-roledescription="slide" aria-label="${i + 1} of ${BOOKS.length}: ${esc(b[0])}, ${esc(b[1])}"${i ? ' hidden inert aria-hidden="true"' : ''}><a class="gp-showcase-book-link" href="${esc(b[7])}" target="_blank" rel="noopener noreferrer"${i ? ' tabindex="-1"' : ''} aria-label="${esc(b[8])}: ${esc(b[0])}, ${esc(b[1])} (opens in a new tab)"><img ${i ? 'data-src' : 'src'}="${asset(b[3])}" ${i ? 'data-srcset' : 'srcset'}="${esc(set)}" sizes="(max-width: 720px) calc(100vw - 64px), (max-width: 1120px) 42vw, 520px" width="${b[5]}" height="${b[6]}" style="--gp-showcase-source-width:${b[5]}px;--gp-showcase-source-height:${b[6]}px" alt="${esc(b[0])} by ${esc(b[1])}" decoding="async"${i ? '' : ' fetchpriority="high"'}></a><div class="gp-showcase-caption"><p class="gp-showcase-title">${esc(b[0])}</p><p class="gp-showcase-author">${esc(b[1])}</p><p class="gp-showcase-scope">${esc(b[2])}</p></div></div>`;
  }).join('');
  const dots = BOOKS.map((b, i) => `<button type="button" data-indicator="${i}" aria-label="Show ${esc(b[0])}" aria-pressed="${i === 0}"><span aria-hidden="true"></span></button>`).join('');
  return `<section class="gp-book-showcase gp-showcase-services" data-book-showcase role="region" aria-roledescription="carousel" aria-label="Books from our client projects"><div class="gp-showcase-stage">${slides}</div><div class="gp-showcase-controls" hidden><button type="button" data-previous aria-label="Previous book">←</button><div class="gp-showcase-indicators" role="group" aria-label="Choose a book">${dots}</div><button type="button" data-next aria-label="Next book">→</button><button type="button" class="gp-showcase-toggle" data-toggle aria-label="Pause automatic book rotation"><span aria-hidden="true">Ⅱ</span></button></div><p class="gp-showcase-status gpc-sr-only" role="status" aria-live="polite" aria-atomic="true"></p></section>`;
}

/* ---------------------------- home blocks ---------------------------- */
const HERO_SLIDES = [
  ['Luma the Sleepy Star', 'John Capon', 'Cover redesign & interior formatting', 'luma-final-front-734.webp', 734, 734, url('case-luma'), true],
  ['Christmas Road Trip Activity Book', 'Rachael Funmilayo', 'Client project · cover and interior layout', 'portfolio-christmas-road-trip-presentation.webp', 1254, 1254, `${url('case-studies')}#portfolio-christmas-road-trip`],
  ['The Seventh Binding', 'Interior portfolio sample', 'Chapter opening and reading spread', 'formatting-seventh-chapter-two.webp', 723, 539, `${url('book-formatting')}#interior-seventh-binding`],
  ['The King of Elfland’s Daughter', 'Lord Dunsany · independent design study', 'Cover presentation & interior typesetting', 'portfolio-elfland-presentation.webp', 1600, 900, `${url('case-studies')}#portfolio-elfland`],
  ['The Cognate Shortcut', 'VocabEasy.org · client project', 'Book cover presentation', 'portfolio-cognate-shortcut-presentation.webp', 1200, 857, `${url('case-studies')}#portfolio-cognate-shortcut`],
];
function cinematicHero() {
  const slides = HERO_SLIDES.map((s, i) => `<div class="gp-showcase-slide${i === 0 ? ' is-active' : ''}${s[7] ? ' gp-book-scene' : ''}" data-slide role="group" aria-roledescription="slide" aria-label="${i + 1} of ${HERO_SLIDES.length}: ${esc(s[0])}"${i ? ' hidden inert aria-hidden="true"' : ''}><a class="gp-showcase-book-link" href="${esc(s[6])}"${i ? ' tabindex="-1"' : ''} aria-label="Explore ${esc(s[0])}"><img ${i ? 'data-src' : 'src'}="${asset(s[3])}" width="${s[4]}" height="${s[5]}" alt="${esc(s[0])}: ${esc(s[2])}" decoding="async"${i ? '' : ' fetchpriority="high"'}>${s[7] ? `<img class="gp-scene-interior" src="${asset('luma-interior-spread-720.webp')}" width="720" height="411" alt="Luma the Sleepy Star: final formatted interior spread" decoding="async">` : ''}</a><div class="gp-showcase-caption"><p class="gp-showcase-title">${esc(s[0])}</p><p class="gp-showcase-author">${esc(s[1])}</p><p class="gp-showcase-scope">${esc(s[2])}</p></div></div>`).join('');
  return `<section class="gp-home-hero gp-cinematic-hero gp-theme-night" id="home" data-gpc-cinematic><div class="gp-wrap"><div class="gp-hero-grid"><div class="gp-hero-copy"><p class="gp-label">BOOK DESIGN, PUBLISHING &amp; MARKETING</p><div class="gp-hero-title"><h1>Books built to<br> <span class="gp-hero-second-line">be <em>discovered.</em></span></h1></div><div class="gp-lead"><p>Book design, interior formatting, publishing support and Amazon visibility for independent authors and publishers.</p></div><div class="gp-actions"><div class="gp-action"><a class="elementor-button" href="${url('case-studies')}">Explore our work ${directionIcon}</a></div><div class="gp-action gp-secondary"><a class="elementor-button" href="${url('contact')}?request=project#gpc-enquiry">Discuss your book ${directionIcon}</a></div></div><p class="gp-hero-assessment"><a href="${url('enquiry')}?request=assessment#gpc-enquiry">Already published? Request a free book assessment.</a></p><div class="gp-reputation"><span class="gp-stars" aria-label="5 out of 5 stars">★★★★★</span><p><strong>5.0 / 5 client feedback</strong><br><a class="gp-review-source-link" href="${url('feedback')}">Nadine Laman &amp; John Capon</a></p></div></div><div class="gp-hero-art"><section class="gp-book-showcase gp-cinematic-showcase gp-showcase-home" data-book-showcase data-stop-on-browse role="region" aria-roledescription="carousel" aria-label="Selected book projects and interiors"><div class="gp-showcase-stage">${slides}</div><div class="gp-showcase-controls" hidden><button type="button" data-previous aria-label="Previous project">←</button><span class="gp-showcase-position" aria-hidden="true"><span data-current>1</span> / ${HERO_SLIDES.length}</span><button type="button" data-next aria-label="Next project">→</button><p class="gp-showcase-help">Use an arrow to stop and explore.</p></div><p class="gp-showcase-status gpc-sr-only" role="status" aria-live="polite" aria-atomic="true"></p></section></div></div></div></section>`;
}
const SELECTED = [
  ['Luma the Sleepy Star', 'John Capon', 'COVER REDESIGN & INTERIOR FORMATTING', 'luma-final-1000.webp', 1000, 1000, 'An illustrated manuscript brought together as a cover wrap and 35-page paperback interior. Delivered as print PDFs.', 'Explore the Luma project', 'case-luma'],
  ['My Dear Grandfather', 'Barsha Rai · Cactus Rain Publishing', 'AMAZON LISTING OPTIMISATION', 'my-dear-grandfather-302.webp', 302, 466, 'Description work, keyword research and category recommendations, with a careful distinction between observations and outcomes.', 'Explore the listing project', 'case-grandfather'],
  ['Kathryn’s Beach', 'Nadine Laman · Cactus Rain Publishing', 'APPROVED A+ CONTENT EXAMPLE', 'kathryns-beach-aplus-600.webp', 600, 606, 'A coordinated presentation of the story, author and related titles. An approved deliverable example, without a performance claim.', 'View the A+ Content example', 'case-studies'],
];
const selectedWork = () => `<section class="gp-section gp-selected-work" id="selected-work" data-gpc-selected-work><div class="gp-wrap"><div class="gp-editorial-heading"><div><p class="gp-label">SELECTED WORK</p><div class="gp-heading"><h2>Real books.<br><em>Considered work.</em></h2></div></div><div class="gp-copy"><p>Different books call for different support. Explore the decisions, deliverables and scope behind the work.</p><a class="gp-text-link" href="${url('case-studies')}">Explore all our work ${directionIcon}</a></div></div><div class="gp-selected-grid">${SELECTED.map((p, i) => `<article class="gp-selected-card gp-selected-card-${i}"><a class="gp-selected-image" href="${url(p[8])}" aria-label="${esc(p[7])}"><img src="${asset(p[3])}" width="${p[4]}" height="${p[5]}" alt="${esc(p[0])} book cover" loading="lazy"></a><div class="gp-selected-copy"><p class="gp-label">${esc(p[2])}</p><h3>${esc(p[0])}</h3><p class="gp-project-author">${esc(p[1])}</p><p>${esc(p[6])}</p><a class="gp-text-link" href="${url(p[8])}">${esc(p[7])} ${directionIcon}</a></div></article>`).join('')}</div></div></section>`;
const GROUPS = [
  ['Book design & production', 'A book that feels considered, inside and out.', 'Readable interiors, genre-aware covers and appropriately prepared ebook and print files.', [['book-formatting', 'Book formatting'], ['cover-design', 'Book cover design']]],
  ['Publishing & launch', 'Bring the files, editions and next steps together.', 'Edition preparation, agreed KDP setup and a clear plan for bringing your book to market.', [['publishing-support', 'Publishing & KDP setup'], ['launch-promotion', 'Book launch & promotion']]],
  ['Amazon visibility', 'Give the right reader a clearer picture.', 'Considered metadata, book descriptions and A+ Content that communicate the book’s reading proposition.', [['amazon-visibility', 'Amazon Book Visibility'], ['book-presentation', 'Book Descriptions & A+ Content']]],
  ['Marketing & strategy', 'Choose the work that matters next.', 'Research, priorities and suitable routes to readers. Advertising setup and optional recurring management follow an agreed scope, with ad spend separate.', [['book-strategy', 'Book Marketing Strategy'], ['amazon-ads', 'Amazon Ads Setup & Management'], ['author-platform', 'Author Platform'], ['catalog-strategy', 'Series & Catalog Strategy']]],
];
const serviceExplorer = () => `<section class="gp-section gp-reference-services gp-theme-night" id="services" data-service-explorer><div class="gp-wrap"><div class="gp-editorial-heading"><div><p class="gp-label">CONNECTED EXPERTISE</p><div class="gp-heading"><h2>From manuscript<br>to <em>market.</em></h2></div></div><div class="gp-copy"><p>Focused support for one stage, or a connected scope built around your book.</p><a class="gp-text-link" href="${url('services')}">Explore all ten services ${directionIcon}</a></div></div><div class="gp-service-explorer"><div class="gp-service-tabs" aria-label="Service groups" hidden>${GROUPS.map((g, i) => `<button type="button" id="gpc-service-tab-${i}" data-service-tab="${i}"><span class="gp-service-tab-number">0${i + 1}</span><span>${esc(g[0])}</span>${directionIcon}</button>`).join('')}</div><div class="gp-service-panels">${GROUPS.map((g, i) => `<section class="gp-service-panel" id="gpc-service-panel-${i}" data-service-panel="${i}"><p class="gp-label">${esc(g[0])}</p><h3>${esc(g[1])}</h3><p>${esc(g[2])}</p><ul>${g[3].map(([k, l]) => `<li><a href="${url(k)}">${esc(l)} ${directionIcon}</a></li>`).join('')}</ul></section>`).join('')}</div></div></div></section>`;

const workSamples = () => `<section class="gp-section gp-tone-lilac gp-work-samples" id="interior-design" data-gpc-work-samples><div class="gp-wrap"><div class="gp-split"><div><p class="gp-label">BOOK FORMATTING &amp; INTERIOR DESIGN</p><div class="gp-heading"><h2>The detail<br>inside the book.</h2></div><p>Title pages, chapter openings and reading spreads show how typography and spacing shape the interior.</p><p>These owner-supplied formatting samples show individual layouts. They are not additional case studies or evidence of publication.</p><a class="gp-button" href="${url('book-formatting')}#formatting-samples">Explore the formatting samples</a></div><figure><a href="${url('book-formatting')}#formatting-samples"><img src="${asset('formatting-seventh-chapter-two.webp')}" width="723" height="539" alt="The Seventh Binding formatting sample: chapter two opening and facing text page" loading="lazy" decoding="async"></a><figcaption>An interior formatting sample. Author and publication details have not been supplied.</figcaption></figure></div></div></section>`;

/* ---------------------------- placeholder engine ---------------------------- */
function resolve(html, key) {
  const partial = (name) => resolve(read('partials', name), key);
  let out = html
    .replace(/\{\{header\}\}/g, () => header(key))
    .replace(/\{\{footer\}\}/g, () => footer())
    .replace(/\{\{contact_links\}\}/g, () => contactLinks())
    .replace(/\{\{form:(assessment|project)\}\}/g, (_, d) => form(d))
    .replace(/\{\{showcase:services\}\}/g, () => servicesShowcase())
    .replace(/\{\{cases:hub\}\}/g, () => caseCards())
    .replace(/\{\{faq:general\}\}/g, () => faqGroup())
    .replace(/\{\{framework:(home|about|assessment)\}\}/g, (_, k) => framework(k))
    .replace(/\{\{premium:selected\}\}/g, () => selectedWork())
    .replace(/\{\{premium:services\}\}/g, () => serviceExplorer())
    .replace(/\{\{premium:work-samples\}\}/g, () => workSamples())
    .replace(/\{\{premium:author-path\}\}/g, () => partial('home-author-path.html'));
  out = out.replace(/\{\{(url|asset):([a-z0-9.\-]+)\}\}/g, (_, t, v) => (t === 'url' ? url(v) : asset(v)));
  const left = out.match(/\{\{[^}]+\}\}/g);
  if (left) console.warn(`  ! unresolved placeholder(s) on ${key}: ${[...new Set(left)].join(', ')}`);
  return out;
}

function homeBody() {
  let h = read('templates', 'home.html');
  const heroStart = h.indexOf('<div class="gp-home-hero"');
  const proofStart = h.indexOf('<div class="gp-proof');
  if (heroStart < 0 || proofStart < 0) return resolve(h, 'home');
  h = h.slice(0, heroStart) + '<!--HERO-->' + h.slice(proofStart);
  return resolve(h, 'home').replace('<!--HERO-->', cinematicHero() + selectedWork() + serviceExplorer() + resolve(read('partials', 'home-author-path.html'), 'home'));
}

/* ---------------------------- page shell ---------------------------- */
const needsEnquiry = (html) => html.includes('gpc-enquiry-form');
function shell(key, body) {
  const d = pages[key];
  const title = key === 'home' ? `${d.title} | Book design, publishing and marketing` : `${d.title} | Grant Publishing Co.`;
  const canonical = `${SITE_URL}${url(key)}`;
  const v = `?v=${VERSION}`;
  const scripts = [
    'interactions.js',
    ...(['home', 'book-formatting', 'case-studies'].includes(key) ? ['reference.js'] : []),
    ...(['home', 'services'].includes(key) ? ['showcase.js'] : []),
    ...(needsEnquiry(body) ? ['static-form.js'] : []),
    'v7.js', 'v7-webgl.js',
  ].map((s) => `<script src="/assets/${s}${v}" defer></script>`).join('\n');
  return `<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>${esc(title)}</title>
<meta name="description" content="${esc(d.description || '')}">
<link rel="canonical" href="${canonical}">
<meta property="og:type" content="website"><meta property="og:site_name" content="Grant Publishing Co."><meta property="og:title" content="${esc(title)}"><meta property="og:description" content="${esc(d.description || '')}"><meta property="og:url" content="${canonical}">
<meta name="theme-color" content="#ffffff">
<link rel="icon" href="/assets/grant-header-128.webp" type="image/webp">
<link rel="preload" href="/assets/manrope-latin-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/cormorant-garamond-latin-italic.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/fonts.css${v}">
<link rel="stylesheet" href="/assets/site.bundle.min.css${v}">
<link rel="stylesheet" href="/assets/v7.css${v}">
</head>
<body class="page gpc-field-notes gpc-reference-design gpc-page-${key}">
${body}
${scripts}
</body>
</html>
`;
}

/* ---------------------------- build ---------------------------- */
function copyDir(src, dest, skip = () => false) {
  fs.mkdirSync(dest, { recursive: true });
  for (const e of fs.readdirSync(src, { withFileTypes: true })) {
    if (skip(e.name)) continue;
    const s = path.join(src, e.name), d = path.join(dest, e.name);
    e.isDirectory() ? copyDir(s, d, skip) : fs.copyFileSync(s, d);
  }
}
fs.rmSync(DIST, { recursive: true, force: true });
fs.mkdirSync(DIST, { recursive: true });
copyDir(path.join(ROOT, 'assets'), path.join(DIST, 'assets'), (n) => n.endsWith('.src.css'));
fs.copyFileSync(path.join(ROOT, 'scripts', 'static-form.js'), path.join(DIST, 'assets', 'static-form.js'));

const urls = [];
for (const key of Object.keys(pages)) {
  const file = path.join(ROOT, 'templates', `${key}.html`);
  if (!fs.existsSync(file)) { console.warn(`  ! no template for ${key}`); continue; }
  const body = key === 'home' ? homeBody() : resolve(fs.readFileSync(file, 'utf8'), key);
  const out = path.join(DIST, key === 'home' ? '' : pagePath(key), 'index.html');
  fs.mkdirSync(path.dirname(out), { recursive: true });
  fs.writeFileSync(out, shell(key, body));
  urls.push(url(key));
  console.log(`  ✓ ${url(key)}`);
}

fs.writeFileSync(path.join(DIST, 'sitemap.xml'), `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${urls.map((u) => `  <url><loc>${SITE_URL}${u}</loc></url>`).join('\n')}\n</urlset>\n`);
fs.writeFileSync(path.join(DIST, 'robots.txt'), `User-agent: *\nAllow: /\nSitemap: ${SITE_URL}/sitemap.xml\n`);
fs.writeFileSync(path.join(DIST, '404.html'), shell('home', `<div class="gpc-site gp-page"><div class="gp-section gp-tone-paper"><div class="gp-wrap"><p class="gp-label">PAGE NOT FOUND</p><div class="gp-heading"><h2>That page <em>moved.</em></h2></div><div class="gp-copy"><p>Try the home page, or tell us what you were looking for.</p></div><div class="gp-actions"><div class="gp-action"><a class="elementor-button" href="/">Back to home</a></div><div class="gp-action gp-secondary"><a class="elementor-button" href="${url('contact')}">Contact us</a></div></div></div></div></div>`).replace(/<body class="[^"]*"/, '<body class="page gpc-field-notes gpc-reference-design"').replace(/<script[^>]*><\/script>\n?/g, ''));
console.log(`\nBuilt ${urls.length} pages into ${path.relative(process.cwd(), DIST) || DIST} (v${VERSION}).`);
if (!FORM_KEY) console.log('Note: WEB3FORMS_ACCESS_KEY is not set. Forms will open the visitor\'s email app instead of sending directly.');
