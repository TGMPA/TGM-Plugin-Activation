---
title:     Installing TGMPA
menutitle: Installation
anchor:    installation
permalink: /installation/
---

### {{ page.title }}

Installing the **{{ site.tgmpa.name }}** library is easy. Follow the instructions below:

1. [Download] the latest release, preferably using the _**[Custom TGMPA Generator]**_.
2. Drop the class file somewhere in your theme or plugin hierarchy.
3. Add a `require_once` call within `functions.php` (or other theme or plugin file) referencing the class file.
4. Create a function, hooked to `tgmpa_register`, that registers the plugin and configurations.

For steps 3 and 4, it is **_strongly recommended_** you view, copy and paste the contents of the `example.php` file which is included in the release package and amend to suit. The `example.php` file is a model for how you should include the class in your theme.


Some important things to note:

1. With the `require_once` call, make sure to amend the path to the correct location within your theme/plugin.
2. For plugins pulled from the .org repo, the `source` argument is optional. Otherwise it is required and should point to the absolute path for the plugin zips within your theme, or to a URL for zips elsewhere online.
3. Updating bundled plugins will _only_ work if you provide a `version` argument for the plugin where the value of that argument is the version number of the new version included.
4. The `$config` variable holds an array of arguments that can be used to customize aspects of the class. If you define an absolute `default_path` for bundled plugins, you do not need to specify the directory path for your bundled plugin within the `source` argument. You will only need to specify the zip file name.
5. Only add the `strings` array in the `$config` variable if you want to change the text strings. Remove them to use the default TGMPA text strings and translations. If you _do_ add your own text strings, make sure you change the text domain (`theme-slug`) to the text domain of your theme or plugin if you didn't download your package via the [Custom TGMPA Generator].

Continue reading about the [Configuration options].


[Custom TGMPA Generator]: {{ '/download/' | prepend: site.tgmpa.url }}
[Download]: {{ '/download/' | prepend: site.tgmpa.url }}
[Configuration options]: {{ '/configuration/' | prepend: site.tgmpa.url }}
