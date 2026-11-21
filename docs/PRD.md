# Astro Kundan Astrology Website — Product Requirements

**Status:** Draft for review; first Blade implementation started  
**Product:** Public astrology services website and its Laravel content-management backend  
**Document basis:** Existing `website-backend` Laravel repository, the approved Astro Kundan homepage concept, and the service-image concepts supplied in this workspace.

## 1. Product summary

Astro Kundan will be a responsive public website that helps visitors understand the astrologer’s services, read educational astrology content, and make a private consultation inquiry. The public site will use Laravel Blade, Bootstrap 5, custom CSS, and JavaScript. The existing Filament admin panel will remain the content-management system.

The first release is an informational and lead-generation website. It will not take payments or promise appointment availability until those requirements and integrations are decided. A generated portrait is being used as a temporary hero stand-in; replace it with the owner's original photo asset when that file is available in the project.

## 2. Goals

- Present Astro Kundan and the astrologer clearly and professionally.
- Help visitors find relevant guidance for love, relationships, marriage, kundli matching, horoscope questions, and spiritual concerns.
- Make it easy to enquire about a specific service from any service page.
- Let authorized staff manage services, pages, blog posts, images, SEO metadata, and incoming inquiries through the existing admin panel.
- Preserve the backend’s sitemap, SEO, redirects, analytics, and contact-lead capabilities.

## 3. Users

### Prospective visitor

Wants to learn about a service, understand how a consultation works, and contact the astrologer privately from a phone or desktop.

### Content editor

Creates and updates service descriptions, page copy, images, FAQs, and blog articles without editing source code.

### Administrator

Manages editor access, site settings, SEO, media, redirects, and contact inquiries.

## 4. Scope

### First release

- Public homepage, about page, service index, service detail pages, blog index, article pages, contact/consultation inquiry page, and privacy/terms pages.
- Astrology service content managed through the existing Services admin resource.
- Service cards with a generated image, title, short description, and clear details/inquiry links.
- Editable FAQs on blog articles, CMS pages, and service detail pages, shown to visitors and represented in the page's SEO FAQ data.
- Lead capture through the existing contact-lead endpoint and admin inbox.
- Editable page content, blog, metadata, sitemap, redirects, site settings, and media through Filament.
- Responsive presentation, accessible form controls, and mobile navigation.

### Not in first release

- Online booking calendar, appointment availability, payment checkout, refunds, order history, or customer accounts.
- Automated astrology calculations, birth-chart reports, or personalized predictions.
- CRM, invoicing, payroll, project management, or other Business Manager features.
- Hindi translations unless approved as a separate content requirement.

## 5. Information architecture

| Page | Purpose | Primary action |
|---|---|---|
| Home | Introduce Astro Kundan and surface key services | Book a private consultation |
| About | Introduce the astrologer, approach, and consultation format | Contact Astro Kundan |
| Services | Browse available guidance services | View service / enquire |
| Service detail | Explain one service, its process, and relevant FAQs | Enquire about this service |
| Blog | Browse educational astrology articles | Read an article |
| Article | Read a full article with related content | Explore a relevant service |
| Contact | Send a private enquiry and find verified contact details | Submit enquiry |
| Privacy and terms | Explain data use and website terms | Return to site |

Primary navigation: **Home, About, Services, Blog, Contact**. The “Book a Consultation” call to action should lead to the contact/enquiry flow until online booking is implemented.

## 6. Homepage requirements

The accepted design direction is a warm ivory background with deep plum and muted gold accents, subtle celestial details, clear typography, and generous spacing.

The homepage should include:

1. A compact announcement strip and responsive navigation.
2. A split hero with the astrologer’s approved photo, a concise headline, short supporting copy, and primary/secondary calls to action. During initial implementation use the generated stand-in image until the supplied original photo is added to the project.
3. A trust/consultation-format row. Numerical experience claims such as “20+ years” must be confirmed by the site owner before publication.
4. A service-card section with the generated images, service names, short summaries, and links.
5. A short astrologer/about section, recent blog articles, FAQs, and a final consultation call to action.
6. A footer with contact/social links, privacy/terms links, and copyright text.

The first approved hero headline is **“Find Clarity in Love, Marriage & Life”**. Supporting copy and all service claims remain editable and subject to owner review.

## 6.1 FAQ requirements

- Every blog article, CMS page, and service detail page can have its own set of FAQs.
- Editors can add and update FAQ questions and answers from the relevant Filament content form.
- Render entered FAQs as a readable, keyboard-accessible accordion or list on the public page.
- Use the same approved question-and-answer content for the visible FAQ and the SEO FAQ structured data. Do not publish FAQ structured data for content that is not shown to visitors.
- If a page has no FAQs, omit its FAQ section and FAQ structured data.
- CMS pages already have a dynamic FAQ content block and SEO FAQ fields; implementation must keep these sources aligned and avoid showing duplicate FAQ lists.

## 7. Service content

The initial content set should cover the services identified by the owner. Final names and descriptions will be confirmed before publication:

- Love and relationship guidance
- Marriage concerns and compatibility
- Kundli matching
- Horoscope consultation
- Vashikaran-related spiritual guidance
- Black-magic or negative-energy concerns

The homepage concept currently shows four cards: Love & Relationship, Marriage & Compatibility, Kundli Matching, and Negative Energy Guidance. Additional services can appear on the Services page and receive their own detail pages.

Each service record should have a title, unique slug, summary, detailed content, category, featured image, publication status, and SEO metadata. The backend already supports these fields, including featured/pinned display flags and service categories. A service detail page should explain the consultation process, show its FAQs, and provide a service-specific enquiry action.

Service copy must describe consultations honestly. It must not promise guaranteed relationship, marriage, or supernatural outcomes, or encourage coercion or harm.

## 8. Contact and inquiry requirements

The inquiry form should request only what is needed for a callback:

- Name (required)
- Email address (required by the current API)
- Phone number (optional)
- Service of interest (optional; may be sent in the existing `subject` field)
- Message (required)
- Privacy notice/consent text with a link to the privacy page

Submitting a service-specific inquiry should prefill the service name. The visitor should receive a clear success message; validation errors should identify fields that need correction. The current endpoint rate-limits public inquiries to five submissions per minute and stores the source and IP address.

The backend can queue email notifications to configured recipient addresses and display inquiries in the Filament Contact Leads inbox. Before launch, the deployment must run a queue worker and have valid SMTP settings; otherwise queued notifications may not be delivered. Add a spam-control layer such as a honeypot or CAPTCHA if real traffic indicates that rate limiting is insufficient.

The current lead record does not store birth date, birth time, or birthplace. Do not collect these details in the first-release form. If personalized chart work later requires them, add a separately approved, privacy-reviewed intake flow.

## 9. Blog and content-management requirements

- Staff can create, edit, preview, publish, and unpublish service pages, CMS pages, and blog posts.
- Blog posts support categories, tags, featured images, featured/pinned ordering, and SEO metadata. Each article can include visible FAQs and matching FAQ structured data.
- Public pages display only published content; drafts remain unavailable to visitors.
- Editors can manage content and leads according to their existing role permissions; administrators retain system settings and user/role management.
- Site settings should hold approved brand name, tagline, logo, favicon, contact details, social links, SMTP details, analytics IDs, indexing preference, and footer copy.
- Replace the current generic seeded “Tech” category, “First Post,” and corporate placeholder page copy with owner-approved astrology content before public launch.

## 10. SEO and discoverability

The website should use existing SEO capabilities for per-page meta title and description, canonical URL, robots instructions, Open Graph/Twitter metadata, FAQ/schema data, redirects, broken-link review, and XML sitemap generation. Published pages, posts, services, and categories should be represented in the sitemap with correct canonical URLs and image references.

Each published page must have a meaningful title, description, one clear page heading, descriptive image alt text, and working internal links. Analytics or advertising tracking should only be enabled when the owner supplies IDs and approves their use.

## 11. Existing backend reviewed

The project is a Laravel 13 / PHP 8.4 application in Docker, with MySQL, Redis, Blade public views, Vite frontend assets, and a Filament admin panel. Bootstrap 5 is installed as a frontend dependency. The Business Manager module has been removed; Contact Leads is the remaining feature module.

| Existing capability | Current support | Product use / remaining work |
|---|---|---|
| CMS pages | Page CRUD, dynamic sections (hero, text/image, feature grid, CTA, FAQ), SEO | Use for Home, About, Contact, Privacy, and Terms; build polished Blade rendering |
| Services | CRUD, categories, summary/content, image, status, featured/pinned, tags, SEO | Use for astrology service catalog and detail pages; add final copy and images |
| Blog | Posts, categories, tags, featured/pinned, images, comments, SEO | Use for astrology education and organic discovery |
| Contact leads | Public form endpoint, validation, rate limit, admin inbox, notes/status, queued email | Connect site inquiry forms; configure SMTP and queue worker |
| Media | Media library and visual image picker | Manage astrologer, service, blog, logo, and social images |
| SEO | Metadata, structured data/FAQ fields, sitemap, redirects, broken-link tracking, audit page | Configure and verify per published page; display the same FAQs that feed the SEO FAQ data |
| Site settings | Brand/contact/social settings, SMTP, analytics IDs, indexing switch, custom scripts | Configure final production values |
| Authentication | Filament admin and role permissions; Sanctum APIs with email OTP flows | Keep public browsing anonymous; protect admin and staff workflows |
| Commerce/booking | Not available in the retained product scope | Separate future feature decision and implementation |

### Current gaps that must be addressed by the website build

- The public Blade layout currently renders diagnostic metadata and a dark debug-style wrapper rather than the approved design.
- Homepage, service list/detail, blog, about, and contact Blade views are basic content renderers and need the new responsive design.
- FAQ data can be entered in SEO forms, and CMS pages also have a page-builder FAQ block; the public templates still need consistent visible FAQ rendering for posts, pages, and services.
- Existing sample content is generic; there are no approved astrology services or launch articles yet.
- Service prices, appointment scheduling, payments, and WhatsApp contact automation are not implemented.
- The Docker app command installs Composer packages but does not start a queue worker; queued lead email delivery needs an operational worker.
- The repository’s Vite build output is ignored by Git, so fresh environments need the documented frontend build step before serving Blade pages.
- The production setup must replace sample admin credentials, disable debug mode, set real SMTP/URLs, and remove or secure the local migration web route before launch.

## 12. Non-functional requirements

- Responsive at common mobile, tablet, and desktop widths; no horizontal overflow at 360 px viewport width.
- Keyboard-operable navigation and forms, visible focus states, semantic headings, associated labels, and useful image alt text.
- Use optimized image dimensions/formats and lazy-load below-the-fold images; maintain a fast mobile experience.
- Validate and sanitize public form input server-side; keep secrets out of source control; use HTTPS in production.
- Do not expose draft content, inquiry records, internal notes, or admin-only APIs to unauthenticated visitors.
- Provide clear privacy information for contact data and avoid collecting sensitive birth details in the first release.

## 13. Success measures

- Visitors can reach a relevant service page from the homepage in one or two actions.
- Service enquiry forms submit successfully, return clear feedback, and create an admin lead record.
- Editors can update services, images, blog posts, and page SEO without source-code edits.
- All required public routes render the approved design on mobile and desktop.
- Sitemap and metadata expose only the intended published pages.

## 14. Acceptance criteria

1. Homepage matches the approved visual direction and uses the astrologer reference image supplied by the owner.
2. Each visible service card has the matching service image, title, summary, and working detail/enquiry links.
3. Service detail pages render published service content, featured image, FAQs where entered, SEO metadata, and a prefilled inquiry action.
4. Published blog articles and CMS pages render their own FAQs when configured, and visible FAQ content matches their SEO FAQ structured data.
5. The contact form validates the required fields and creates a lead that appears in the admin inbox.
6. Blog and CMS content can be managed through Filament and appears publicly only when published.
7. Admin can update SEO fields, site details, and images without editing Blade source.
8. Navigation, layout, forms, and cards remain usable on mobile and keyboard accessible.
9. Before launch, SMTP delivery, queue processing, administrator credentials, debug settings, and production migration access are configured securely.

## 15. Decisions to confirm before implementation

- Final published service names and exact service claims, especially vashikaran and black-magic-related wording.
- Actual phone, WhatsApp, email, address, consultation hours, and preferred callback method.
- Whether “20+ years” or any other experience/credential statement is accurate and approved.
- English-only first release or English and Hindi.
- Whether inquiries should be handled only by phone/email or later through a booking calendar and payment flow.
- Final approval of generated service-card images and the crop/source image to use for the astrologer portrait.
