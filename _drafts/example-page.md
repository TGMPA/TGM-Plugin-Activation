{::comment}

REMOVE THIS COMMENT & SAVE THE FILE UNDER A NEW NAME TO USE THIS TEMPLATE.
ALSO remove the '[must-use]' and '[optional]' helper lines and any unused optional attributes.

---------------------------
Template for Pages.
---------------------------

Guidelines:

- Pages should be saved in the root directory as either .md, .html or .xml files

- Pages are automatically added to the menu and the sitemap.
  N.B.: the homepage has `sitemap: exclude: true` to prevent it being added twice.

- To use a custom (shorter) page title in the menu, set the `menutitle:` attribute.

- To exclude a page from the menu, set the `menu:` attribute to false.

- Add a number in front of the file name to influence the sort order of the menu.
  You may need to re-number other pages to achieve the desired effect.

- To exclude a page from the sitemap (such as the 404 page), set the `sitemap:` `exclude:` attribute to `true`.

- Always set the `anchor` attribute. Use a relatively short and descriptive text string.

- For pages which will be included in the menu, set the `permalink` attribute to the same value
  as the `anchor` attribute surrounded by slashes.
  Don't try and be clever by creating nesting in permalinks. This *will* break things.

- Pages will have their own permalink based file.

{:/comment}
---
[must-use]
anchor:      a-descriptive-slug
permalink:   /a-descriptive-slug/

[optional]
title:       Example page title which will be used as the `<title>` for individual page files
description: Meta description for in the header
menutitle:   (Short) Title for use in menu if different from page title
menu:        true
sitemap:
    lastmod:    2014-01-23
    priority:   0.8
    changefreq: 'monthly'
    exclude:    false
    
[Only for the blog page - don't set anywhere else]
needsposts: true
---

### A title for the page - use {{ page.title }} to reuse the title given in the frontmatter.

The page text.

You can use all normal [GitHub flavoured markdown syntaxes](https://guides.github.com/features/mastering-markdown/) and can even mix html and markdown.

However for multi-line code samples this syntax is preferred - don't forget to make sure that the code sample starts with `<?php` !

{% highlight php %}
<?php
// some PHP code
{% endhighlight %}

There are also a number of variables available for use in all documents. It is strongly advised to use these when appropriate.
- {{ site.baseurl }}     "/", i.e. the relative root url
- {{ site.tgmpa.name }}: "TGM Plugin Activation"
- {{ site.tgmpa.title }}: Fallback title for the <title> tag if no `title` is set in the Frontmatter at the top of the file.
- {{ site.tgmpa.description }}: Fallback for the <<meta name="description"> header tag if no `description` is set in the Frontmatter at the top of the file.
- {{ site.tgmpa.url }}: http://tgmpluginactivation.com, i.e. the site url
- {{ site.tgmpa.logo }}: http://tgmpluginactivation.com/images/logo.png, the url to the logo file
- {{ site.tgmpa.version }}: 2.5.1, the current version - not to worry if this is not up to date as we'll use the GitHub API for up-to-date info
- {{ site.tgmpa.minwp }}: 3.7, the minimum WP version needed for the current TGMPA version
- {{ site.tgmpa.minphp }}: 5.2.4, the minimum PHP version needed for the current TGMPA version
- {{ site.tgmpa.twitternick }}: tgmpa
- {{ site.tgmpa.twitterhash }}: tgmpa
- {{ site.tgmpa.twitterurl }}: https://twitter.com/tgmpa
- {{ site.tgmpa.gplus }}: https://plus.google.com/114044312047618704188, used in the SEO header tags
- {{ site.tgmpa.github }}: https://github.com/TGMPA/TGM-Plugin-Activation
- {{ site.tgmpa.analytics }}: used in the Google Analytics js code

Additionally attributes you set in the Frontmatter can be accessed via {{ page.attribute }}. So if you want to use the same title in the page content as you've set as `title` attribute, you can use `{{ page.title }}` to do so.
