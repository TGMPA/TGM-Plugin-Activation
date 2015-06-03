---
title:     How do I upgrade TGMPA ?
anchor:    upgrading-tgmpa
permalink: /faq/upgrading-tgmpa/
---

If you already use TGMPA in your theme or plugin and want to upgrade to a newer version, follow the instructions below:

1. [Download] the latest release.
2. Read the [Changelog] to see what has been fixed/changed/improved in the new version.
3. Replace the old class file `class-tgm-plugin-activation.php` with the new version.
4. Carefully check the `example.php` file which is included in the release for new or changed options and code and adjust your existing function accordingly if necessary.
   The following page will always contain up-to-date information on the current [configuration options].
5. Release an updated version of your theme/plugin.


[Download]: {{ '/download/' | prepend: site.tgmpa.url }}
[Changelog]: {{ '/blob/master/CHANGELOG.md' | prepend: site.tgmpa.github }}
[configuration options]: {{ '/configuration/' | prepend: site.tgmpa.url }}