# Changelog

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
