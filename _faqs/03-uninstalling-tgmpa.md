---
title:     How do I remove TGMPA from my theme/plugin ?
anchor:    uninstalling-tgmpa
permalink: /faq/uninstalling-tgmpa/
---

If your theme or plugin no longer needs **{{ site.tgmpa.name }}** (TGMPA) support, then these are the steps you need to take to remove TGMPA. Some initiative may be needed depending on how the theme or plugin author has added it in, and it is assumed you are comfortable editing PHP files / FTP as needed.

<ol>
<li>Find and delete the plugin registration function. It will look something like:

{% highlight php %}
<?php
/**
 * Required and Recommended Plugins
 */
function prefix_register_plugins() {

	/**
	 * Array of plugin arrays. Required keys are name and slug.
	 * If the source is NOT from the .org repo, then source is also required.
	 */
	$plugins = array(

		// WordPress SEO
		array(
			'name'     => 'WordPress SEO by Yoast',
			'slug'     => 'wordpress-seo',
			'required' => false,
		),
		...
	);

	tgmpa( $plugins );
}
add_action( 'tgmpa_register', 'prefix_register_plugins' );
{% endhighlight %}

	There should only be one instance of <code>tgmpa(</code> or <code>tgmpa_register</code> in your theme or plugin (other than the TGMPA class file), so search for that. The registration function may be with other code in <code>functions.php</code>, <code>init.php</code> or a separate file such as <code>include/tgmpa.php</code> or other file.</li>
<li>Find and delete the <code>require_once()</code> call that references the TGMPA class file:

{% highlight php %}
<?php
/**
 * Include the TGM_Plugin_Activation class.
 */
require_once dirname( __FILE__ ) . '/class-tgm-plugin-activation.php';
{% endhighlight %}

	The file name is almost certainly unique, so search your theme or plugin for that. The theme or plugin author may have used <code>require</code>, <code>include</code> or <code>include_once</code> instead of <code>require_once</code>, and they may have added extra <code>( )</code> around the file path.</li>
<li>Find and delete the TGMPA class file. Since the class file is no longer referenced, the whole <code>class-tgm-plugin-activation.php</code> file (or equivalent if renamed) can be deleted from your theme or plugin.</li>
</ol>

With the plugins registration, the class file reference, and the class file itself all removed, your theme or plugin will no longer be using TGMPA.