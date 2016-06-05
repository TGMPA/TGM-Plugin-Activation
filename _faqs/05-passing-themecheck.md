---
title:     My theme does not pass the theme review if I include TGMPA. How do I fix this ?
anchor:    passing-theme-review
permalink: /faq/passing-theme-review/
---

If you are submitting a theme to wordpress.org or to a commercial theme repository, such as ThemeForest, your theme will be reviewed before being accepted and the [Theme Check plugin] is one of the tools used during these reviews.

1. Typical review feedback which you might see if you include TGMPA is:

   > REQUIRED: path/to/class-tgm-plugin-activation.php. Themes should use add_theme_page() for adding admin pages.
   >
   > WARNING: More than one text-domain is being used in this theme. This means the theme will not be compatible with WordPress.org language packs. The domains found are your-text-domain, tgmpa

   **Solution**: To fix this, download a fresh copy of TGMPA using the [Custom Generator] and indicate your distribution channel to get the correct version which will pass the Theme Check rules.


2. With older versions of TGMPA you might also encounter the following feedback from Theme Check:

   > WARNING: Found a translation function that is missing a text-domain. Function _n_noop, with the arguments ...
   >
   > REQUIRED: screen_icon() found in the file class-tgm-plugin-activation.php. Deprecated since version 3.8.

   **Solution**: Both these issues indicate that you are using a **_very_** old version of TGMPA and you should really [upgrade to the latest version].


3. A last issue you might encounter if you distribute your theme via ThemeForest is feedback along the lines of:

   > All translated strings must be escaped.

   Let us reassure you: all TGMPA output strings **_are_** escaped.

   Most strings are escaped late, i.e. at the point where they are echo-ed out, as only then you know the context in which the string is used. This means you will often not find the escape call together with the translation call.
   This is correct and is nothing to worry about.


[Theme Check plugin]: https://wordpress.org/plugins/theme-check/
[Custom Generator]: {{ '/download/' | prepend: site.tgmpa.url }}
[upgrade to the latest version]: {{ '/download/' | prepend: site.tgmpa.url }}