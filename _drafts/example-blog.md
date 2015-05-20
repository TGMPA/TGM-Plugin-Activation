{::comment}

REMOVE THIS COMMENT & SAVE THE FILE UNDER A NEW NAME TO USE THIS TEMPLATE.
ALSO remove the '[must-use]' and '[optional]' helper lines and any unused optional attributes.

---------------------------
Template for Homepage sections (Features menu item).
---------------------------

Guidelines:

- Blog permalinks are based on the following pattern /:categories/:year/:month/:day/:title/

- Blogs have to be saved in the `_post` directory with a file name containing the post date in the form:
  'YY-MM-DD-filename.md' (or.html)

- Blogs will automatically be added to the Blog page, the RSS feed and to the sitemap.
  The Blog menu item will only show if there are blogs to display.


{:/comment}
---
[must-use]
title:       Example blog title - this will be used in the <title> tag and the permalink so keep it to the point
author:      githubusername
anchor:      a-descriptive-slug

[optional]
description: Meta description for in the header
permalink:   /you-can-change-the-permalink-but-shouldnt-for-blogs/
category:    Category under which this is filed - will also be used in the normal permalink
tags:        Comma delimited list of tags for the post
short:       Excerpt of the blog post. Can use simple markdown, but no line breaks. Will be used on blog archive pages if available. Only use this for really long blog posts.
sitemap:
    lastmod:    2014-01-23
    priority:   0.7
    changefreq: 'weekly'
    exclude:    false
---

### A title for the blog - use {{ page.title }} to reuse the title given in the frontmatter.

The section text. You can use all normal GitHub flavoured markdown syntaxes.

However for multi-line code samples this syntax is preferred - don't forget to make sure that the code sample starts with `<?php` !

	{% highlight php linenos %}
	<?php
	// some PHP code
	{% endhighlight %}

There are also a number of variables available for use in all documents. It is strongly advised to use these when appropriate. For a list of these with some explanations, see example-page.md