---
title:     I've bundled a new version of a plugin with my theme. Why does TGMPA not show there is an update available ?
anchor:    updating-bundled-plugins
permalink: /faq/updating-bundled-plugins/
---

Updating with bundled plugins can only be done by setting a minimum required version of the plugin in the settings file - the version array key. Set this to the version number of the updated bundled plugin and things should be fine.

TGMPA cannot obtain information about the version number of bundled plugins itself, so it relies on the information you provide in the settings file to determine if there is an update.