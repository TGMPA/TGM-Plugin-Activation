---
title:       Custom TGMPA Generator
author:      jrfnl
anchor:      custom-tgmpa-generator
category:    News
tags:        ['website', 'generator', 'themecheck']
---

### Custom TGMPA Generator released

If you are a theme designer and publish themes on wordpress.org, you may have come across feedback from the Theme Check plugin and/or the Theme Review team along the lines of _"You are only allowed to use add_theme_page(), please remove the call to add_submenu_page()."_ or _"You are only supposed to use one text-domain."_.

So you go and do a search-and-replace and then get users reporting fatal errors because the search and replace also replaced some strings which shouldn't have been changed.

And then when TGMPA is updated, you have to do it all over again.

<p class="align-right">
	![screenshot-1]({{ '/images/screenshots/tgmpa-generator-medium.png' | prepend: site.tgmpa.url }})
</p>

Well, no more. We've heard you and we've worked hard to make this easier for you.

So today, we are releasing a _**Custom TGMPA Generator**_. You can find it on the [Download] page.

Just fill out the form with the _text-domain_ of your theme or plugin, what sort of WordPress add-on you will be including TGMPA in and, if it's a theme, the publication channel you'll be using and we'll generate a custom download of the current version of TGMPA for you with all the relevant code already adjusted.

We hope you like it. As this is a first release, there may of course still be some bugs. If you find one, please [report] it to us and we'll try and fix it as soon as possible.

Enjoy!

Oh and don't forget that [our survey] is still open, so if you haven't given your opinion yet, go and [do so now]!



[Download]: {{ '/download/' | prepend: site.tgmpa.url }}
[report]: {{ '/issues' | prepend: site.tgmpa.github }}
[our survey]: {{ '/2016/01/11/tgmpa-survey/' | prepend: site.tgmpa.github }}
[do so now]: http://goo.gl/forms/Fq1gbY9vCW