---
title:     Configuring TGMPA
menutitle: Configuration
anchor:    configuration
permalink: /configuration/
---

### Configuring TGMPA for use with your theme/plugin


The **{{ site.tgmpa.name }}** library has an easy to use API to reference plugins and set config options. Consider the code below (taken from `example.php`).

> ##### IMPORTANT - Take note:
> The below code may be slightly out of date - the [download package] will always contain the most up-to-date and complete version.
> The actual `example.php` file also contains a few more detailed examples of how to configure plugins from different sources and with variant slugs.



{% highlight php %}
<?php
/**
 * Include the TGM_Plugin_Activation class.
 */
require_once dirname( __FILE__ ) . '/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'my_theme_register_required_plugins' );

/**
 * Register the required plugins for this theme.
 *
 *  <snip />
 *
 * This function is hooked into tgmpa_init, which is fired within the
 * TGM_Plugin_Activation class constructor.
 */
function my_theme_register_required_plugins() {
	/*
	 * Array of plugin arrays. Required keys are name and slug.
	 * If the source is NOT from the .org repo, then source is also required.
	 */
	$plugins = array(

		// This is an example of how to include a plugin bundled with a theme.
		array(
			'name'               => 'TGM Example Plugin', // The plugin name.
			'slug'               => 'tgm-example-plugin', // The plugin slug (typically the folder name).
			'source'             => get_stylesheet_directory() . '/lib/plugins/tgm-example-plugin.zip', // The plugin source.
			'required'           => true, // If false, the plugin is only 'recommended' instead of required.
			'version'            => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher. If the plugin version is higher than the plugin version installed, the user will be notified to update the plugin.
			'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
			'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
			'external_url'       => '', // If set, overrides default API URL and points to an external URL.
			'is_callable'        => '', // If set, this callable will be be checked for availability to determine if a plugin is active.
		),

		// This is an example of how to include a plugin from the WordPress Plugin Repository.
		array(
			'name'      => 'BuddyPress',
			'slug'      => 'buddypress',
			'required'  => false,
		),
		
		// <snip />
	);

	/*
	 * Array of configuration settings. Amend each line as needed.
	 *
	 * TGMPA will start providing localized text strings soon. If you already have translations of our standard
	 * strings available, please help us make TGMPA even better by giving us access to these translations or by
	 * sending in a pull-request with .po file(s) with the translations.
	 *
	 * Only uncomment the strings in the config array if you want to customize the strings.
	 */
	$config = array(
		'id'           => 'tgmpa',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled plugins.
		'menu'         => 'tgmpa-install-plugins', // Menu slug.
		'parent_slug'  => 'themes.php',            // Parent menu slug.
		'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => false,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.
		/*
		'strings'      => array(
			'page_title'                      => __( 'Install Required Plugins', 'theme-slug' ),
			'menu_title'                      => __( 'Install Plugins', 'theme-slug' ),
			// <snip>...</snip>
			'nag_type'                        => 'updated', // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
		)
		*/
	);

	tgmpa( $plugins, $config );

}
{% endhighlight %}



#### Plugin parameters

Each plugin can take an array of parameters, as indicated below (parameters in red are required, in orange sometimes required):

Parameter | Type | Since | Details
--------- | ---- | ----- | ---------
<span class="required">name</span> | string | v2.0.0 | The name of the plugin.
<span class="required">slug</span> | string | v2.0.0 | The plugin slug, which is typically the name of the folder that holds the plugin.
<span class="possibly-required">required</span> | boolean | v2.0.0 | Either `true` or `false`. Defaults to `false`.<br>If set to `true`, the plugin will show as _required_.<br>If set to `false` or not set, the plugin will show as _recommended_.
<span class="possibly-required">source</span> | string | v2.0.0 | The source of the plugin.<br>This parameter is required if the plugin you are referencing <u>is not</u> from the WordPress Plugin Repository.<br>You can reference either bundled plugins or plugins elsewhere on the internet from this parameter.<br>For bundled plugins, this should be the path to the .zip file. For external plugins, the direct url to the .zip file.
<span class="possibly-required">version</span> | string | v2.2.0 | The minimum version required for the plugin.<br>This parameter is useful if you require a minimum version of a plugin in order for your theme or plugin to work.<br>If the user has the plugin installed but does not meet the minimum version specified, a notice will show asking them to update the plugin to the latest version.<br>N.B.: This parameter is _required_ if you want users to update a plugin with a newer **_bundled_** version.
force_activation | boolean | v2.2.0 | Either `true` or `false`. Defaults to `false`.<br>If set to `true`, it forces the specified plugin to be active at all times while the current theme (or plugin) is active. The plugin can only be deactivated after switching themes or deactivation of your plugin.
force_deactivation | boolean | v2.2.0 | Either `true` or `false`. Defaults to `false`.<br>If set to `true`, it forces the specified plugin to be deactivated when the current theme is switched. This is useful for deactivating theme-specific plugins.
external_url | string | v2.2.0 | An external URL for the plugin.<br>By default, plugins referenced from the WordPress Plugin Repository are linked to their plugin information via a thickbox overlay. This parameter overrides this default behavior and allows you to specify an alternative URL for the plugin which - when clicked - will be opened in a new tab/window.
is_callable | string\|array | v2.5.0 | Advanced feature. If a plugin can be installed under two or more different slugs - for instance a basic version and a premium version using different slugs -, it might not be recognized correctly as active.<br>By setting `is_callable` to either a function `function_name` from that plugin or a class method - `array( 'class', 'method' )` - similar to how you hook in to actions and filters - TGMPA can still recognize the plugin as being active.


#### Configuration options

<p>
	The library also has a set of configuration options for you to manipulate on a global scale, as indicated below:
</p>

Option    | Type | Since | Details
--------- | ---- | ----- | ---------
id | string | v2.5.0 | Optional. A unique id for your theme/plugin's instance of TGMPA. Defaults to `'tgmpa'`.<br>This is used to prevent admin notices not showing up when there are several themes/plugins using TGMPA and the admin notice has been dismissed before.
default_path | string | v2.0.0 | Optional. The default absolute path for bundled plugins.<br>Typically, this would be set to somethings like: `get_stylesheet_directory() . '/plugins/` for themes or something along the lines of `plugin_dir_path( __FILE__ ) . 'plugins/'` for plugins.<br>If you set this, make sure the `source` parameters of the bundled plugins don't contain the path as well.
menu | string | v2.0.0 | Optional. The menu slug for the plugin install page. Defaults to `'tgmpa-install-plugins'`.<br>The slug for the actual plugin install page, so using the default it will look like this: `?page=tgmpa-install-plugins`
parent_slug | string | v2.5.0 | Optional. The parent menu slug for the plugin install page. Defaults to `'themes.php'`.<br>If you change this, make sure you also change the `capability` option to a capability which is appropriate for that parent menu.<br><br><em>In v2.2.0 - v2.3.6 there were two options `parent_menu_slug` and `parent_url_slug`. These were removed in v2.4.0 as they were causing issues with the Theme Checker. The functionality previously offered by these options has been reinstated in v2.5.0 via the `parent_slug` option in combination with the `capability` option in a way that is compatible with the Theme Checker.</em>
capability | string | v2.5.0 | Optional. The capability needed to access the plugin install page. Defaults to `'edit_theme_options'`.<br>If you change the `parent_slug`, make sure you change this to a capability which is appropriate for the new parent menu.<br>For instance, if you change this to `options-general.php` to have the plugin install page appear in the _Settings_ menu, the appropriate capability would be `manage_options`.
has_notices | boolean | v2.1.0 | Optional. Either `true` or `false`. Defaults to `true`.<br>If `true`, admin notices are shown for required/recommended plugins.<br><br><em>Pre-v2.2.0 this option was called `notices`</em>.
dismissable | boolean | v2.4.0 | Optional. Either `true` or `false`. Defaults to `true`.<br>If `true`, admin admin notices can be dismissed by the user.
dismiss_msg | string | v2.4.0 | Optional. If the `dismissable` option is set to false, then this message will be output at the top of the admin notice before listing the required/recommended plugins. This string will be filtered by `wp_kses_post()`.
is_automatic | boolean | v2.2.0 | Optional. Either `true` or `false`. Defaults to `false`.<br>If `true`, plugins will automatically be activated upon successful installation (for both singular and bulk installation processes).
message | string | v2.2.0 | Optional HTML content to include before the plugins table is output. This string will be filtered by `wp_kses_post()`.
strings | array | v2.0.0 | Optional. An array of customizable strings used throughout the library.<br>**_Only set the strings for which you want to customize the text_**. You can safely remove any strings which you're not customizing.<br>Strings prefixed with `_n_noop()` have both singular and plural forms (in that order).<br>Additionally some strings will contain `%s`/`%1$s` variables - see the comments at the end of each line for what each argument will be.<br>Please **_do_** make sure that you adjust the `text-domain` (`theme-slug`) of any customized strings to the `text-domain` of your theme/plugin.<br><br>**Call to action**:<br>TGMPA will start providing localized text strings soon. If you already have translations of our standard  strings available, please help us make TGMPA even better by giving us access to these translations or by sending in a pull-request with `.po` (and `.mo`) file(s) with the translations.<br>For more information on how to do this, please read the [contributing guidelines].
<span class="deprecated">parent_menu_slug</span> | string | v2.2.0 | **Deprecated** since 2.4.0. See `parent_slug`.
<span class="deprecated">parent_url_slug</span> | string | v2.2.0 | **Deprecated** since 2.4.0. See `parent_slug`.
<span class="deprecated">notices</span> | boolean | v2.0.0 | **Deprecated** since 2.2.0. See `has_notices`.


[download package]: {{ '/download/' | prepend: site.tgmpa.url }}
[contributing guidelines]: {{ '/blob/develop/CONTRIBUTING.md' | prepend: site.tgmpa.github }}
