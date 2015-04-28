# Changelog for TGM Plugin Activation library

## 2.4.2 (2015-04-27)
* Fixed: Bundled/pre-packaged plugins would no longer install when using the Bulk installer. This was a regression introduced in v2.4.1. [#321], [#316] Props [Juliette Reinders Folmer]. Thanks [tanshcreative] for reporting.
* Fixed: Bulk installer did not honour a potentially set `default_path` for local prep-packaged plugins. [#203], [#332] Props [Juliette Reinders Folmer]. Thanks [pavot] and [djcowan] for reporting.
* Removed call to `screen_icon()` function which was deprecated in WP 3.8. [#244], [#224], [#234]. Props [Nate Wright]. Thanks [hamdan-mahran] and [Sandeep] for reporting.
* Fixed: _"PHP Fatal error: Class 'TGM_Bulk_Installer' not found"_ [#185] Thanks [Chris Talkington] for reporting.

## 2.4.1 (2015-04-22)

* Improve escaping for URLs and attributes.

## 2.4.0 (2014-03-17)

* All textdomain strings now made to `tgmpa` and remove all notices dealing with textdomain and translation issues.
* The `_get_plugin_basename_from_slug` method now checks for exact slug matches to prevent issues with plugins that start with the same slug.
* Commenting style now adjusted so it is easier to comment large chunks of code if necessary.
* Plugins from an external source now properly say `Private Repository` in the list table output.
* `add_submenu_page` has been changed to `add_theme_page` for better theme check compatibility.
* Removed the use for `parent_menu_slug` and `parent_menu_url` for $config options (see above).
* Nag messages can now be forced on via a new `dismissable` config property. When set to false, nag cannot be dismissed.
* New config `dismiss_msg` used in conjunction with `dismissable`. If `dismissable` is false, then if `dismiss_msg` is not empty, it will be output at the top of the nag message.
* Better contextual message for activating plugins - changed to "Activate installed plugin(s)" to "Begin activating plugin(s)".
* Added cache flushing on theme switch to prevent stale entries from remaining in the list table if coming back to a theme with TGMPA.
* TGMPA is now a singleton to prevent extra settings overrides.
* Fixed bug with duplicating plugins if multiple themes/plugins that used TGMPA were active at the same time.
* Added contextual message updates depending on WordPress version.
* Better nag message handling. If the nag has been dismissed, don't even attempt to build message (performance enhancement).
* Ensure class can only be instantiated once (instantiation moved inside the `class_exists` check for TGMPA).
* Change instances of `admin_url` to `network_admin_url` to add better support for MultiSite (falls back gracefully for non-MultiSite installs).
* Updated much of the code to match WP Coding Standards (braces, yoda conditionals, etc.).
* Myriads of other bug fixes and enhancements

## 2.3.6 (2012-04-23)

* Fixed API error when clicking on the plugin install row action link for an externally hosted plugin

## 2.3.5 (2012-04-16)

* Fixed nag message not working when nag_type string was not set (props @jeffsebring)

## 2.3.4 (2012-03-30)

* Fixed undefined index notice when checking for required plugins (props @jeffsebring)
* Fixed bug where, during a bulk install, if the plugin was defined in the source as pre-packaged but also existed in the repo, it would erroneously pull the plugin from the repo instead (props @wpsmith)
* Added ability to set nag type for the admin notice via 'nag_type' string (props @wpsmith)

## 2.3.3 (2012-02-03)

* Changed license to reflect GPL v2 or later (to be compatible with the WordPress repo)

## 2.3.2 (2012-02-03)

* Fixed bug (100%) with not loading class properly

## 2.3.1 (2012-02-03)

* Fixed bug with not finding class (reverted back to Plugin_Upgrader)

## 2.3.0 (2012-01-25)

* Improved sorting of plugins by sorting them by required/recommended (while still maintaining alphabetical order within each group)
* Improved output of strings in nag messages
* Added 2 new strings: install_link and activate_link to customize the text for the nag action links
* Added new class: TGM_Plugin_Installer to prepare for must-use plugin support

## 2.2.2 (2012-01-08)

* Fixed bug that allowed users to click on the Install Plugins page when all the plugin installations and activations were already complete

## 2.2.1 (2012-01-05)

* Fixed bug that caused WordPress core upgrades to fail (WordPress doesn't check for including WP_Upgrader on core upgrades)

## 2.2.0 (2012-01-02)

* Fixed erroneous links for plugins linked to the WordPress Repo
* Improved UI of plugins by listing them in WordPress' default table layout
* Improved support for installing plugins if security credentials require FTP information
* Improved support for MultiSite
* Added 3 new classes (all extensions of existing WordPress classes): TGMPA_List_Table for outputting required/recommended plugins in a familiar table format, TGM_Bulk_Installer for bulk installing plugins and TGM_Bulk_Installer_Skin for skinning the bulk install process
* Added extra defensive measures to prevent duplication of classes
* Added ability to bulk install and bulk activate plugins
* Added new config options: 'parent_menu_slug', 'parent_menu_url', 'is_automatic', and 'message'
* Added new string: 'complete' (displayed when all plugins have been successfully installed and activated)
* Added support for singular/plural strings throughout the library
* Added permission checks to action links
* Added new filter tgmpa_default_screen_icon to set the default icon for the plugin table page
* Added new optional plugin parameters: 'version', 'force_activation', 'force_deactivation' and 'external_url'
* Removed 'button' string (deprecated with use of plugins table)

## 2.1.1 (2011-10-19)

* Fixed nag not re-appearing if user switched themes and then re-activated the previous theme (UX improvement)

## 2.1.0 (2011-10-18)

* Fixed duplicate nag message on admin options pages
* Fixed FTP nonce error when FTP credentials aren't defined in wp-config.php
* Improved handling of failed FTP connections with WP_Filesystem
* Improved string labeling for semantics
* Improved nag messages so that they are now consolidated into one message
* Improved plugin sorting by listing them alphabetically
* Improved plugin messages after installation and activation
* Added automatic activation of plugins after installation (users no longer need to click the "Activate this plugin" link)
* Added links to repo plugins for their plugin details and information (done via thickbox)
* Added option to dismiss nag message
* Added tgmpa_notice_action_links filter hook to filter nag message action links
* Added new methods: admin_init(), thickbox(), dismiss(), populate_file_path(), _get_plugin_data_from_name() and is_tgmpa_page()

## 2.0.0 (2011-10-03)

* Improved nag messages by adding a strings argument to filter default messages
* Improved nag message output by using the Settings API
* Improved internals by adding API for developers to use (code in class no longer has to be touched)
* Improved API function name (now tgmpa) for semantics
* Improved example.php with instructions for setup
* Added internal style sheet for styling
* Added ability to define custom text domain for localization
* Added new properties $default_path and $strings
* Added new methods register(), config(), _get_plugin_basename_from_slug() and actions()
* Removed unnecessary is_wp_error() check

## 1.1.0 (2011-10-01)

* Improved property $args to accept arrays of arguments for each plugin needed
* Improved add_submenu_page to add_theme_page
* Improved admin notices to display different messages based on status of plugin (not installed, installed but not activated)
* Improved block-level documentation
* Improved handling of plugin installation and activation with plugins_api, Plugin_Upgrader and Plugin_Skin_Installer
* Added support for multiple plugins of each instance (pre-packaged and repo)
* Added new property $domain to hold textdomain for internationalization
* Added CSS for slight UI enhancements
* Added extra conditional checks current_user_can( 'install_plugins' ) and current_user_can( 'activate_plugins' ) for security
* Removed menu display if all included plugins were successfully installed and activated
* Removed unnecessary conditional check before class is defined

## 1.0.0 (2011-09-29)

* Initial release into the wild


[Chris Talkington]: https://github.com/ctalkington
[Juliette Reinders Folmer]: https://github.com/jrfnl
[djcowan]: https://github.com/djcowan
[hamdan-mahran]: https://github.com/hamdan-mahran
[Nate Wright]: https://github.com/NateWr
[pavot]: https://github.com/pavot
[Sandeep]: https://github.com/InsertCart
[tanshcreative]: https://github.com/tanshcreative

[#332]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/332
[#321]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/321
[#316]: https://github.com/thomasgriffin/TGM-Plugin-Activation/issues/316
[#244]: https://github.com/thomasgriffin/TGM-Plugin-Activation/pull/244
[#234]: https://github.com/thomasgriffin/TGM-Plugin-Activation/issues/234
[#224]: https://github.com/thomasgriffin/TGM-Plugin-Activation/issues/224
[#203]: https://github.com/thomasgriffin/TGM-Plugin-Activation/issues/203