# Changelog

## 0.2.1

Phone and card layout fixes (theme styles only; pages do not need updating).

- Explore Black Sets cards: the number, heading and text now line up across both cards (before, the shorter 3D Scans card pushed its heading lower). Spacing inside the cards is tighter, and each card shows a 3-line excerpt ending in "…" so both cards are the same height on phones; the full text is on the Gallery and 3D Scans pages.
- Our Happy Clients: on phones the last row of logos is centred.
- Footer on phones: one centred column (logo, social icons, menu links, contact details, mailing list); the mailing-list box and Subscribe button are full width so the placeholder is no longer cut off.

## 0.2.0

Fixes and design corrections after the first hosted install.

- Logo now visible: a reversed (white) version of the real Black Sets logo is used on the dark header and footer.
- Phone menu fixed: it opens full screen with every link (before, the header's blur effect trapped the menu inside the header bar).
- Design closer to the original brand: original button bronze (#64563D), the original's wide-tracked capital headings with an underline bar, white "Explore Black Sets" band, hero slideshow using the archived original slides.
- Client logos no longer render as grey blocks; project posters fill their frames, and landscape key art is shown whole over a blurred backdrop; grid rows line up.
- Micro animations: header entrance, hero title lines rising in, scroll reveals with stagger, underline bars drawing in, button light sweep, image hover zoom, staggered phone menu, reading progress bar, gallery lightbox with previous/next. All respect "reduce motion".
- Copy restored to the original site's wording. Removed text that was not on the original site: invented testimonials (the original only had Wix placeholders), invented FAQ answers, invented Privacy Policy and Terms text (the originals are empty pages), two invented project synopses and extra marketing lines, and invented meta descriptions.
- Contact form uses the original fields (First Name, Last Name, Email, Message, "Send", "Thanks for submitting!"); mailing list uses "Enter your email here*" / "Subscribe Now".
- New Appearance > Black Sets Pages screen: updates pages created by setup to the new design, skipping pages you edited unless you tick them (previous versions stay in Revisions).

## 0.1.0

First release of the Black Sets theme, built from the archived original site (`blacksets.co`).

- Cinematic dark luxury redesign (`#070707`, `#c5a059`) with Cormorant Garamond display headings paired with Manrope body text (SIL OFL, latin + latin-ext bundled locally, zero external font requests).
- 9 editable block-editor pages generated under WordPress Pages: Home, Behind the Scenes Gallery, 3D Scans & Virtual Sets, Projects & Credits, News & Insights, Contact & Enquiries, FAQ & About, Privacy Policy, Terms & Conditions.
- Primary Navigation Menu seeded with all pages and dropdown hierarchy.
- Responsive mobile optimization: two-column grids on phones for short content (service cards, client logos, project posters, BTS gallery, footer links), full-width hero with bottom-anchored equal-width CTA button pair, hamburger drawer navigation panel, zero horizontal overflow across 1920/768/390/360/320px viewports.
- Responsive BTS Gallery with 24+ master high-resolution set construction photographs, hover zoom overlay, and integrated fullscreen lightbox modal.
- 3D Scans & Virtual Sets feature page with full 3-phase case study of the Voodoo Shop in *Jeepers Creepers: Reborn* (The Proposal -> The Visualisation -> The End Product) and high-resolution point cloud/render showcase.
- Projects & Credits featuring 8 film/TV titles with high-res key art posters, genre tags, and loglines.
- Contact form with anti-spam honeypot, in-page AJAX submission, private storage in wp-admin under Enquiries (`bsets_enquiry` post type), and email notifications.
- Master 2303x1952 PNG logo preserved; crisp favicons generated directly from the master logo.
- Runtime dynamic footer copyright (`wp_date('Y')`).
- One-click setup flow under Appearance > Black Sets Setup to sideload all media into the WordPress Media Library, seed pages, configure permalinks (`/%postname%/`), and set the static front page.
- Built-in GitHub release updater tracking `aliameenco-creator/blacksets`.
