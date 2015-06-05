---
title:     Authors
anchor:    authors
permalink: /authors/
---

### {{ page.title }}

The **{{ site.tgmpa.name }}** library was created by [Thomas Griffin] and is currently maintained by @thomasgriffin, @GaryJones and @jrfnl.


##### Contributors

<div class="contributors">

{% for contributor in site.github.contributors %}
[![Avatar]({{ contributor.avatar_url }}){: style="width: 30px;"}]({{ contributor.html_url }}) [@{{ contributor.login }}]({{ contributor.html_url }})
{: .contributor }
{% else %}
This project would not be possible without the help of [our amazing contributors] on GitHub.
{% endfor %}

</div>


#### Want to contribute as well ?

We very much welcome new contributors to the TGMPA library. To get you started, please read the [contributing guidelines].

If you want to translate the TGMPA library, please download the [`tgmpa.pot`] file from the `/languages/` directory in the `develop` branch. Once you have finished your translation, please submit a pull request with your the `.po` (and `.mo`) file(s).

If you want to contribute to or translate this website, please read the separate [gh-pages readme] and [gh-pages contributing guidelines].


[Thomas Griffin]: https://thomasgriffin.io
[our amazing contributors]: {{ '/graphs/contributors' | prepend: site.tgmpa.github }}
[contributing guidelines]: {{ '/blob/develop/CONTRIBUTING.md' | prepend: site.tgmpa.github }}
[`tgmpa.pot`]: {{ '/develop/languages/tgmpa.pot' | prepend: site.tgmpa.github }}
[gh-pages readme]: {{ '/blob/gh-pages/README.md' | prepend: site.tgmpa.github }}
[gh-pages contributing guidelines]: {{ '/blob/gh-pages/CONTRIBUTING.md' | prepend: site.tgmpa.github }}
