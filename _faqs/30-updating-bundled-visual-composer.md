---
title:     I've bundled a new version of Visual Composer with my theme. Why doesn't updating via TGMPA work ?
anchor:    updating-bundled-visual-composer
permalink: /faq/updating-bundled-visual-composer/
---

> This particular situation (only) occurs when you, as a theme developer, have a developers license for Visual Composer allowing you to distribute it with your theme.

Visual Composer - by design - looks at the Envato Market place server to see if there are updates available for the plugin.
If you ship the update as a zip file bundled in with your theme, the update routine within Visual Composer will overrule your bundled update, making the update fail.

To fix this, add the below line of code to your `functions.php` to disable the external updater in Visual Composer:

{% highlight php %}
vc_set_as_theme();
{% endhighlight %}

For more information - see the [Visual Composer developers documentation](https://wpbakery.atlassian.net/wiki/pages/viewpage.action?pageId=524297).
