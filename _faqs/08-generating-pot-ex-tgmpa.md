---
title:     How can I exclude the TGMPA text strings from my `.pot` file ?
anchor:    excluding-tgmpa-from-pot-file
permalink: /faq/excluding-tgmpa-from-pot-file/
---

As TGMPA - since version 2.6.0 - comes with its own translation files, you can save the translators of your theme or plugin some work, by excluding the TGMPA strings from your `.pot` file.

> **_WARNING_**: Only do this if you are distributing the TGMPA translation files with your theme/plugin! In other words: do *not* do this if you distribute your theme via wordpress.org!


The easiest way to set this up, is by using the program [Poedit]. So install this first if you haven't got it already.

1. Open your `.pot` file in Poedit.
2. Use the menu at the top to go to `Catalogue` ⇒ `Properties` or just press the key combination Alt+Enter.
3. Go to the second tab `Sources paths`.
4. At the bottom of this screen you can add files or folders to be excluded when generating or updating the `.pot` file:

   ![screenshot-1]({{ '/images/other/faq-generating-pot-files-1.png' | prepend: site.tgmpa.url }})

   You can either exclude a whole directory ...

   ![screenshot-2]({{ '/images/other/faq-generating-pot-files-2.png' | prepend: site.tgmpa.url }})

   ... or exclude individual files from parsing by the `.pot` generator.

   ![screenshot-3]({{ '/images/other/faq-generating-pot-files-3.png' | prepend: site.tgmpa.url }})

5. Once you're finished, click `OK` and then use `Catalogue` ⇒ `Update from sources` to update your `.pot` file and the TGMPA strings will no longer be included.


[Poedit]: https://poedit.net/