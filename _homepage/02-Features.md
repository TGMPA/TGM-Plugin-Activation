---
anchor: features
---

###	Features

The **{{ site.tgmpa.name }}** library revolutionizes how plugins can be handled with WordPress themes and other plugins.

By using classes that are utilized within WordPress, the **{{ site.tgmpa.name }}** library can automatically install, update and activate multiple plugins that are either bundled with a theme, downloaded from the WordPress Plugin Repository or downloaded elsewhere on the internet (perhaps a private repository).

The library uses the WP_Filesystem Abstraction class to find the best way to install the plugins - `WP_Filesystem` searches through a number of methods (Direct, FTP, FTP Sockets, SSH) and determines the best one to use based on the user's server setup. If any FTP credentials are needed, a form will be displayed to prompt users to input their FTP credentials in order to continue processing the request. The library uses WordPress' own `Plugin_Upgrader` and `Plugin_Installer_Skin` and extensions of other WordPress upgrader classes to handle singular and bulk installations.