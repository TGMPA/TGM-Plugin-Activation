{::comment}

REMOVE THIS COMMENT & SAVE THE FILE UNDER A NEW NAME TO USE THIS TEMPLATE.
ALSO remove the '[must-use]' and '[optional]' helper lines and any unused optional attributes.

---------------------------
Template for Homepage sections (Features menu item).
---------------------------

Guidelines:

- Sections should each be in their own file and should be saved to the `_homepage` directory.

- Add a number in front of the file name to influence the sort order of the sections.
  You may need to re-number other homepage-files to achieve the desired effect.

- Always set the `anchor` attribute. Use a relatively short and descriptive text string.

- Homepage sections will not be included separately in the menu, nor in the sitemap.

{:/comment}
---
[must-use]
anchor:    a-descriptive-slug
---

### A title for the section.

The section text. You can use all normal GitHub flavoured markdown syntaxes.

However for multi-line code samples this syntax is preferred - don't forget to make sure that the code sample starts with `<?php` !

	{% highlight php linenos %}
	<?php
	// some PHP code
	{% endhighlight %}

There are also a number of variables available for use in all documents. It is strongly advised to use these when appropriate. For a list of these with some explanations, see example-page.md