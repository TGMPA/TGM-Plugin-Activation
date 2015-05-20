---
title:     I get a fatal PHP error '<code>Call to protected TGM_Plugin_Activation::__construct() from invalid context</code>'. Help!
anchor:    fatal-php-error-1
permalink: /faq/fatal-php-error-1/
---

There are at least two copies of TGMPA active in your WP install and one of those is using a really old version of TGMPA (2.3.6 or less - over two years old) which conflicts with the current way of doing things which causes that error message. Please contact the theme/plugin author of the theme/plugin using the old version of TGMPA and urge them to upgrade the TGMPA version they include.