=== Plugin Name ===
Contributors: oneweekonetool, boonebgorges, digitaleffie, gossettphd, janaremy, jcmeloni, jeremyboggs, knoxdw, patrickmj, patrickrashleigh, sramsay, zmccune, chnm
Donate link: http://anthologize.org/
Tags: book, pdf, tei, epub, publish, ebook
Requires at least: 3.3
Tested up to: 3.6
Stable tag: 0.7.1

Use the power of WordPress to transform your content into a book.

== Description ==

Anthologize is a free, open-source, WordPress-based platform for publishing. Grab posts from your WordPress blog, pull in feeds from external sites, or create new content directly in Anthologize. Then outline, order, and edit your work, crafting it into a coherent volume for export in several ebook formats, including PDF, EPUB, and TEI.

Visit [anthologize.org](http://anthologize.org/ "Anthologize") to learn more about Anthologize.

== Installation ==

The best way to install Anthologize is via the Add New link under Dashboard > Plugins.

To install Anthologize manually, follow these steps.

1. Upload the `anthologize` directory to `/wp-content/plugins/`
1. Make sure the `/anthologize/templates/epub/temp/` directory is writable by the server
1. Activate Anthologize through the WordPres 'Plugins' menu
1. Visit Dashboard > Anthologize to start compiling your project

If you're upgrading manually from a previous version of Anthologize, please be sure to deactivate your existing plugin before replacing it with the new files, and reactivate after uploading.

== Screenshots ==

1. The Anthologize Project Organizer screen

== Usage ==

Many optimizations to the PDF export have been added, but resource limits on the server will always be a potential issue. Here are some tips to try if you encounter errors or white screens of death while exporting PDF:

1. Include page breaks between parts and items. This appears to reduce the memory that the PDF classes (TCPDF) require.
2. Change the following information in the php.ini file in the Resource Limits section. On most hosted servers, this file is in the top web directory and/or in your WordPress directory. If there is one in your WordPress directory, edit that one. If you have questions, please contact your web hosting provider.

Change the max_execution_time setting:
max_execution_time = 180;

Change the memory_limit setting:
memory_limit = 128M;


The latest release of PHP has a default memory limit of 128M, but this might not be in place on your server. Increasing the execution time (measured in seconds) can also help.
In a hosted server environment, increasing the resources Anthologize consumes could hurt performance for everyone else on your server. It might be worth consulting your hosting company before increasing these resource limits and exporting projects on a regular basis.

Cover images in ePub output.

To add your own cover images, just upload them to the anthologize/templates/epub/covers directory and they will appear as options in the export screen. Make sure they are readable by the server.

== Changelog ==

= 0.7.1 =
* WP 3.6 support
* Update jQuery BlockUI
* Preview Project fix
* Set utf-8 encoding on HTML export for improved character handling
* Minor style tweak
* Fix bug that prevented more than 5 projects from showing on My Projects screen

= 0.7 =
* Refactored loading process for better performance in various hosting situations
* Fixes validation issues with epub exports
* Fixes permissions issues with PDF export by moving TCPDF cache location to WP upload directory
* Localization fixes
* Added Spanish translalation
* Improvements to HTML export format
* PHP 5.4+ compatibility
* Improved adherence to WordPress coding standards
* Added a Credits section

= 0.6.2-alpha =
* Improved compatibility with WP 3.3

= 0.6.1-alpha =
* Removed WordPress filter call that may have caused whitescreens on exports on some installations
* Added some unit tests

= 0.6-alpha = 
* Code name "Wide Wale"
* many optimizations to PDF export
* improved CJK handling in PDF export
* added part- and item- page break options for PDF
* added anthologize logo and part-item breadcrumbs to PDF output
* partially OOified epub export
* added part-item nesting to epub ToC
* added cover image option to epub output (might not work in all readers. standards-schmandards)
* added link-localization to epub output (internal links in your site are internal links in the epub)
* regularized code style throughout
* added role control; admins can choose which user roles can Anthologize projects
* added Multisite awareness; super admins can choose which users can Anthologize across the network
* added a Preview feature for in-browser previews of projects, parts, and items
* fixed a bug that may have caused items to dissociate from projects when autosaving
* added automatic support for custom post types in filters
* fixed issues with quote double-escaping in exports
* added full compatibility with latest WP
* added real (but experimental) RTF export format

= 0.5-alpha =
* Code name "Gabardine"
* anthologize_register_format() API allows third-party developers to register their output-format plugins and options
* Newly added theming functions allow plugin developers to use familiar WordPress loops for creating new output formats
* Improved character encoding all-around
* Increased support for Korean, Japanese, Chinese text
* RTF export format discontinued in favor of a more stable HTML output (RTF facilities will likely reappear in a future version).
* New post filters on the project organizer screen: filter by date, filter by post type
* Minimize/maximize parts to make project editing easier
* Add multiple items to parts by dragging the Posts header on the project organizer screen
* Linked Table of Contents and better pagination in PDF
* Improved support for Gravatars in exports
* Methods added to the TEI class that allow for some automated indexing
* Many bugfixes and stability enhancements


= 0.4-alpha =
* Better PHP error handling for increased export reliability
* Better character encoding in output formats
* Better image handling in PHP export
* Required compression libraries for ePub are now bundled with Anthologize
* Project organizer screen improvements: Anthologize remembers your last used filter when you return to the page; a bug related to item ordering was fixed; "Are you sure?" message added to the Delete Project button; better handling of item names with colons and other characters
* Export screen improvements: project metadata (such as copyright information) is saved; selecting projects from the dropdown automatically pulls in saved metadata
* Namespaced WordPress post type names to ensure compatibility with other plugins
* Anthologize content is now set to 'draft' by default, keeping it out of WordPress searches and reducing conflict with plugins hooking to publish_post
* Frontmatter added to PDF export
* Improved TEI output

= 0.3-alpha =
* Initial public release

== Who built this? ==

Anthologize was built during [One Week | One Tool](http://oneweekonetool.org/ "One Week | One Tool"), an NEH Summer Institute at George Mason University's [Center for History and New Media](http://chnm.gmu.edu/ "CHNM").

Major sponsors of Anthologize:
* <a href="http://www.neh.gov/divisions/odh">The Office of Digital Humanities of the National Endowment for the Humanties</a>
* <a href="http://chnm.gmu.edu">The Roy Rosenzweig Center for History and New Media</a>
* <a href="http://openlab.citytech.cuny.edu">City Tech OpenLab</a>
* <a href="http://www.demokratie-dialog.de/">Demokratie & Dialog e.V.</a>

See the Credits page in the Anthologize dashboard for more details.
