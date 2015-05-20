{::comment}

REMOVE THIS COMMENT & SAVE THE FILE UNDER A NEW NAME TO USE THIS TEMPLATE.
ALSO remove the '[must-use]' and '[optional]' helper lines and any unused optional attributes.

---------------------------
Template for FAQ questions.
---------------------------

Guidelines:

- FAQ questions should each be in their own file and should be saved to the `_faq` directory.

- Add a number in front of the file name to influence the sort order of the FAQ questions.
  You may need to re-number other faq-files to achieve the desired effect.

- Don't add the title in the FAQ content, but use the `title` attribute in the frontmatter/meta-data.
  You can *not* use markdown in the title. You *can* use HTML.
  The title will also be used as for the <title> tag for the question page. It will be stripped of html for that.

- Always set the `anchor` and `permalink` attributes. Use a relatively short and descriptive text string.

- Individual pages will be generated for each FAQ question and links to these will be included in the sitemap.

{:/comment}
---
[must-use]
title:       Is this a <em>question</em> title ?
anchor:      a-descriptive-slug
permalink:   /faq/a-descriptive-slug/

[optional]
description: Meta description for in the header
sitemap:
    lastmod:    2014-01-23
    priority:   0.5
    changefreq: 'monthly'
    exclude:    false
---

Add the answer text here. Do **NOT** add the question.

You can use all normal GitHub flavoured markdown syntaxes.

However for multi-line code samples this syntax is preferred - don't forget to make sure that the code sample starts with `<?php` !

	{% highlight php linenos %}
	<?php
	// some PHP code
	{% endhighlight %}

There are also a number of variables available for use in all documents. It is strongly advised to use these when appropriate. For a list of these with some explanations, see example-page.md