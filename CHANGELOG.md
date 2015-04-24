# Changelog for TGM Plugin Activation library

## Unreleased

<!-- * .... [#](). Props [](). Thanks []() for reporting. -->

* Enhancement: Better support for GitHub hosted plugins:
  
  Previously using standard GitHub packaged zips as download source would not work as, even though the plugin would be installed, it would not be recognized as such by TGMPA because of the non-standard directory name which would be created for the plugin, i.e. `my-plugin-master` instead of `my-plugin`. A work-around for this has been implemented and you can now use GitHub-packaged `master` branch or release zips to install plugins. Have a look at the `example.php` file for a working example.
  One caveat: this presumes that the plugin is based in the root of the GitHub repo and not in a `/src` subdirectory.
  [#327], [#280], [#283] Props [Juliette Reinders Folmer]. Thanks [Dan Fisher] and [Luis Martins] for reporting/requesting this enhancement.

* Admin Page improvements:
  - Allow for filtering of the plugin action links on the admin page similar to WP Core. The available filters are `tgmpa_plugin_action_links` and `tgmpa_network_plugin_action_links`. [#300], [#226] Thanks [Juliette Reinders Folmer] for the inspiration.
  - Leverage the css styling of the Core plugins page [#227]. Props [Shiva Poudel].
  - Allow for moving the Admin Page to a different place in the menu. This is mainly to accommodate plugins using TGMPA as it is terribly illogical for the TGMPA page to be under the _"Appearance"_ menu in that case. This has been now been done in a way that Theme Check will not choke on it. [#310] Props [Juliette Reinders Folmer].

* Admin notices improvements:
  - For installs including both plugins as well as themes, notices will now be dismissable for each separately. This prevents a situation where a theme would have TGMPA included, the user has dismissed the notice about it, a plugin with TGMPA gets installed and the notice about it requiring certain other plugins is never shown. [#174] Thanks [Chris Howard] for reporting.
  - Fixed: The reset of dismissed notices on `switch_theme` was only applied for the current user, not for all users. [#246] Props [Juliette Reinders Folmer].
  - Fixed: Admin notices would show twice under certain circumstances. [#249], [#237] Props [Juliette Reinders Folmer]. Thanks [manake] for reporting.

* Bulk Installer:
  - Fixed: Bundled/pre-packaged plugins would no longer install when using the Bulk installer. This was a regression introduced in v2.4.1. [#321], [#316] Props [Juliette Reinders Folmer]. Thanks [tanshcreative] for reporting.
  - Fixed: If a bulk install was initiated using the bottom _Bulk Actions_ dropdown, the install page would display an outdated TGMPA plugin table at the bottom of the page after the bulk installation was finished. [#319] Props [Juliette Reinders Folmer].
  - Fixed: Bulk installer did not honour a potentially set `default_path` for local prep-packaged plugins. [#203], [#332] Props [Juliette Reinders Folmer]. Thanks [pavot] and [djcowan] for reporting.
  - Fixed: The _"Show Details"_ links no longer worked. This was a regression briefly introduced in the `develop` branch. [#326]

* Theme Check compatibility:
  - Removed call to `screen_icon()` function which was deprecated in WP 3.8. [#244], [#224], [#234]. Props [Nate Wright]. Thanks [hamdan-mahran] and [Sandeep] for reporting.
  - Prevent _"The theme appears to use include or require"_ warning. [#262], [#258] Props [Juliette Reinders Folmer]. Thanks [Tim Nicholson] for reporting.
  - Preempt the disallowing of the use of the `add_theme_page()` function. See [the theme review meeting notes](https://make.wordpress.org/themes/2015/04/21/this-weeks-meeting-important-information-regarding-theme-options/) for further information on this decision. [#315] Props [Juliette Reinders Folmer].

* Miscellaneous fixes:
  - Fixed: _"PHP Fatal error: Class 'TGM_Bulk_Installer' not found"_ [#185] Thanks [Chris Talkington] for reporting.
  - Fixed: _"Undefined index: skin_update_failed_error"_ [#260], [#240] Props [Juliette Reinders Folmer]. Thanks [Parhum Khoshbakht] and [Sandeep] for reporting.
  - Made admin urls environment aware by using `self_admin_url()`. [#255], [#171] Props [Juliette Reinders Folmer].
  - Fixed: the Adminbar would be loaded twice causing conflicts (with other plugins). [#208] Props [John Blackbourn].

* I18N improvements:
  - Make configurable message texts singular/plural context aware. [#173] Props [Yakir Sitbon].
  - Language strings which are being overridden should use the including plugin/theme language domain. [#217] Props [Christian Foellmann].

* Housekeeping:
  - Applied a number of best practices. [#284],
    [#281] - props [Ninos Ego],
    [#286] - props [krishna19],
    [#325], [#324], [#331] - props [Juliette Reinders Folmer]
  - Allow for extending of the TGMPA class and fixed issues with PHP 5.2 [#303] which were originally caused by this. Props [Juliette Reinders Folmer].
  - Tighten the file permissions on our files. [#322]
  - Cleaned up some of the documentation. [#179] Props [Gregory Karpinsky].
  - Comply with the [WordPress Coding Standards](https://make.wordpress.org/core/handbook/coding-standards/)
  - Added Travis CI integration for coding standards and php-linting. [#304], [#329] Props [Juliette Reinders Folmer].
  - Added Scrutinizer CI integration for code quality. [#330]
  - Added [Contributing guidelines](https://github.com/thomasgriffin/TGM-Plugin-Activation/blob/master/CONTRIBUTING.md).


## 2.4.1 (2015-04-22)

* Improve escaping for URLs and attributes.

## 2.4.0 (2014-03-17)

* All textdomain strings now made to `tgmpa` and remove all notices dealing with textdomain and translation issues.
* The `_get_plugin_basename_from_slug()` method now checks for exact slug matches to prevent issues with plugins that start with the same slug.
* Commenting style now adjusted so it is easier to comment large chunks of code if necessary.
* Plugins from an external source now properly say _"Private Repository"_ in the list table output.
* `add_submenu_page()` has been changed to `add_theme_page()` for better theme check compatibility.
* Removed the use for `parent_menu_slug` and `parent_menu_url` for `$config` options (see above).
* Nag messages can now be forced on via a new `dismissable` config property. When set to `false`, nag cannot be dismissed.
* New config `dismiss_msg` used in conjunction with `dismissable`. If `dismissable` is false, then if `dismiss_msg` is not empty, it will be output at the top of the nag message.
* Better contextual message for activating plugins - changed _"Activate installed plugin(s)"_ to _"Begin activating plugin(s)"_.
* Added cache flushing on theme switch to prevent stale entries from remaining in the list table if coming back to a theme with TGMPA.
* TGMPA is now a singleton to prevent extra settings overrides.
* Fixed bug with duplicating plugins if multiple themes/plugins that used TGMPA were active at the same time.
* Added contextual message updates depending on WordPress version.
* Better nag message handling. If the nag has been dismissed, don't even attempt to build message (performance enhancement).
* Ensure class can only be instantiated once (instantiation moved inside the `class_exists()` check for TGMPA).
* Change instances of `admin_url()` to `network_admin_url()` to add better support for MultiSite (falls back gracefully for non-MultiSite installs).
* Updated much of the code to match WP Coding Standards (braces, yoda conditionals, etc.).
* Myriads of other bug fixes and enhancements

## 2.3.6 (2012-04-23)

* Fixed API error when clicking on the plugin install row action link for an externally hosted plugin

## 2.3.5 (2012-04-16)

* Fixed nag message not working when `nag_type` string was not set (props [Jeff Sebring])

## 2.3.4 (2012-03-30)

* Fixed _"undefined index"_ notice when checking for required plugins (props [Jeff Sebring])
* Fixed bug where, during a bulk install, if the plugin was defined in the source as pre-packaged but also existed in the repo, it would erroneously pull the plugin from the repo instead (props [Travis Smith])
* Added ability to set nag type for the admin notice via `nag_type` string (props [Travis Smith])

## 2.3.3 (2012-02-03)

* Changed license to reflect GPL v2 or later (to be compatible with the WordPress repo)

## 2.3.2 (2012-02-03)

* Fixed bug (100%) with not loading class properly

## 2.3.1 (2012-02-03)

* Fixed bug with not finding class (reverted back to `Plugin_Upgrader`)

## 2.3.0 (2012-01-25)

* Improved sorting of plugins by sorting them by required/recommended (while still maintaining alphabetical order within each group)
* Improved output of strings in nag messages
* Added 2 new strings: `install_link` and `activate_link` to customize the text for the nag action links
* Added new class: `TGM_Plugin_Installer` to prepare for must-use plugin support

## 2.2.2 (2012-01-08)

* Fixed bug that allowed users to click on the Install Plugins page when all the plugin installations and activations were already complete

## 2.2.1 (2012-01-05)

* Fixed bug that caused WordPress core upgrades to fail (WordPress doesn't check for including `WP_Upgrader` on core upgrades)

## 2.2.0 (2012-01-02)

* Fixed erroneous links for plugins linked to the WordPress Repo
* Improved UI of plugins by listing them in WordPress' default table layout
* Improved support for installing plugins if security credentials require FTP information
* Improved support for MultiSite
* Added 3 new classes (all extensions of existing WordPress classes): `TGMPA_List_Table` for outputting required/recommended plugins in a familiar table format, `TGM_Bulk_Installer` for bulk installing plugins and `TGM_Bulk_Installer_Skin` for skinning the bulk install process
* Added extra defensive measures to prevent duplication of classes
* Added ability to bulk install and bulk activate plugins
* Added new config options: `parent_menu_slug`, `parent_menu_url`, `is_automatic`, and `message`
* Added new string: `complete` (displayed when all plugins have been successfully installed and activated)
* Added support for singular/plural strings throughout the library
* Added permission checks to action links
* Added new filter `tgmpa_default_screen_icon` to set the default icon for the plugin table page
* Added new optional plugin parameters: `version`, `force_activation`, `force_deactivation` and `external_url`
* Removed `button` string (deprecated with use of plugins table)

## 2.1.1 (2011-10-19)

* Fixed nag not re-appearing if user switched themes and then re-activated the previous theme (UX improvement)

## 2.1.0 (2011-10-18)

* Fixed duplicate nag message on admin options pages
* Fixed FTP nonce error when FTP credentials aren't defined in `wp-config.php`
* Improved handling of failed FTP connections with `WP_Filesystem`
* Improved string labeling for semantics
* Improved nag messages so that they are now consolidated into one message
* Improved plugin sorting by listing them alphabetically
* Improved plugin messages after installation and activation
* Added automatic activation of plugins after installation (users no longer need to click the _"Activate this plugin"_ link)
* Added links to repo plugins for their plugin details and information (done via thickbox)
* Added option to dismiss nag message
* Added `tgmpa_notice_action_links` filter hook to filter nag message action links
* Added new methods: `admin_init()`, `thickbox()`, `dismiss()`, `populate_file_path()`, `_get_plugin_data_from_name()` and `is_tgmpa_page()`

## 2.0.0 (2011-10-03)

* Improved nag messages by adding a strings argument to filter default messages
* Improved nag message output by using the Settings API
* Improved internals by adding API for developers to use (code in class no longer has to be touched)
* Improved API function name (now tgmpa) for semantics
* Improved `example.php` with instructions for setup
* Added internal style sheet for styling
* Added ability to define custom text domain for localization
* Added new properties `$default_path` and `$strings`
* Added new methods `register()`, `config()`, `_get_plugin_basename_from_slug()` and `actions()`
* Removed unnecessary `is_wp_error()` check

## 1.1.0 (2011-10-01)

* Improved property `$args` to accept arrays of arguments for each plugin needed
* Improved `add_submenu_page()` to `add_theme_page()`
* Improved admin notices to display different messages based on status of plugin (not installed, installed but not activated)
* Improved block-level documentation
* Improved handling of plugin installation and activation with `plugins_api`, `Plugin_Upgrader` and `Plugin_Skin_Installer`
* Added support for multiple plugins of each instance (pre-packaged and repo)
* Added new property `$domain` to hold textdomain for internationalization
* Added CSS for slight UI enhancements
* Added extra conditional checks `current_user_can( 'install_plugins' )` and `current_user_can( 'activate_plugins' )` for security
* Removed menu display if all included plugins were successfully installed and activated
* Removed unnecessary conditional check before class is defined

## 1.0.0 (2011-09-29)

* Initial release into the wild

[Christian Foellmann]: https://github.com/cfoellmann
[Chris Talkington]: https://github.com/ctalkington
[Dan Fisher]: https://github.com/danfisher85
[djcowan]: https://github.com/djcowan
[hamdan-mahran]: https://github.com/hamdan-mahran
[Sandeep]: https://github.com/InsertCart
[Jeff Sebring]: https://github.com/jeffsebring
[John Blackbourn]: https://github.com/johnbillion
[Juliette Reinders Folmer]: https://github.com/jrfnl
[Yakir Sitbon]: https://github.com/KingYes
[krishna19]: https://github.com/krishna19
[Luis Martins]: https://github.com/lmartins
[manake]: https://github.com/manake
[Nate Wright]: https://github.com/NateWr
[Ninos Ego]: https://github.com/Ninos
[Parhum Khoshbakht]: https://github.com/parhumm
[pavot]: https://github.com/pavot
[Chris Howard]: https://github.com/qwertydude
[Shiva Poudel]: https://github.com/shivapoudel
[tanshcreative]: https://github.com/tanshcreative
[Tim Nicholson]: https://github.com/timnicholson
[Gregory Karpinsky]: https://github.com/tivnet
[Travis Smith]: https://github.com/wpsmith

[#332]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/332
[#331]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/331
[#330]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/330
[#329]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/329
[#327]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/327
[#326]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/326
[#325]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/325
[#324]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/324
[#322]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/322
[#321]: https://github.com/thomasgriffin/TGM-Plugin-Activation/issues/321
[#319]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/319
[#316]: https://github.com/thomasgriffin/TGM-Plugin-Activation/issues/316
[#315]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/315
[#310]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/310
[#304]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/304
[#303]: https://github.com/thomasgriffin/TGM-Plugin-Activation/issues/303
[#300]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/300
[#286]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/286
[#284]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/284
[#283]: https://github.com/thomasgriffin/TGM-Plugin-Activation/issues/283
[#281]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/281
[#280]: https://github.com/thomasgriffin/TGM-Plugin-Activation/issues/280
[#262]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/262
[#260]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/260
[#258]: https://github.com/thomasgriffin/TGM-Plugin-Activation/issues/258
[#255]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/255
[#249]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/249
[#246]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/246
[#244]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/244
[#240]: https://github.com/thomasgriffin/TGM-Plugin-Activation/issues/240
[#237]: https://github.com/thomasgriffin/TGM-Plugin-Activation/issues/237
[#234]: https://github.com/thomasgriffin/TGM-Plugin-Activation/issues/234
[#227]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/227
[#226]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/226
[#224]: https://github.com/thomasgriffin/TGM-Plugin-Activation/issues/224
[#217]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/217
[#208]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/208
[#203]: https://github.com/thomasgriffin/TGM-Plugin-Activation/issues/203
[#185]: https://github.com/thomasgriffin/TGM-Plugin-Activation/issues/185
[#179]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/179
[#174]: https://github.com/thomasgriffin/TGM-Plugin-Activation/issues/174
[#173]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/173
[#171]: https://github.com/thomasgriffin/TGM-Plugin-Activation/issues/171
