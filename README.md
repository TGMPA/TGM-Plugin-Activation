# TGM Plugin Activation

**Lead Developers:**
[Thomas Griffin](https://github.com/thomasgriffin) ([@jthomasgriffin](https://twitter.com/jrf_nl)), [Gary Jones](https://github.com/GaryJones) ([@GaryJ](https://twitter.com/GaryJ)), [Juliette Reinders Folmer](https://github.com/jrfnl) ([@jrf_nl](https://twitter.com/jrf_nl))  
**Version:** 2.5.0-alpha  
**Requires at least:** 3.7.0  
**Tested up to:** 4.2.0  

## Description

TGM Plugin Activation is a PHP library that allows you to easily require or recommend plugins for your WordPress themes (and plugins). It allows your users to install and even automatically activate plugins in singular or bulk fashion using native WordPress classes, functions and interfaces. You can reference pre-packaged plugins, plugins from the WordPress Plugin Repository or even plugins hosted elsewhere on the internet.

## Installation

1. Head to the [Releases](https://github.com/thomasgriffin/TGM-Plugin-Activation/releases) page and download the latest release zip.
2. Extract the class file and place it somewhere in your theme hierarchy.
3. Add a `require_once` call within `functions.php` (or other theme file) referencing the class file.
4. Create a function, hooked to `tgmpa_register`, that registers the plugin and configurations.

For steps 3 and 4, it is recommended you view, copy and paste the contents of `example.php`
and amend to suit. The `example.php` file is a model for how you should include the class in your theme.

*Some important things to note:*

1. With the `require_once` call, make sure to amend the path to the correct location within your theme.
2. For plugins pulled from the .org repo, the source argument is optional. Otherwise it is required and should point
   to the absolute path for the plugin zips within your theme, or to a URL for zips elsewhere online.
3. The `$config` variable holds an array of arguments that can be used to customize aspects of the class.
   If you define an absolute default path for packaged plugins, you do not need to specify the directory path
   for your pre-packaged plugin within the `'source'` argument. You will only need to specify the zip file name.

## Feedback

See https://github.com/thomasgriffin/TGM-Plugin-Activation/issues for current issues and for reporting bugs and enhancements.

__Note:__ TGM Plugin Activation library authors are not responsible for the *end-user support* for any plugin or theme which uses the library.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## Contributing to TGM Plugin Activation

If you have a patch, or stumbled upon an issue with TGM Plugin Activation core, you can contribute this back to the code. Please read our [contributor guidelines](CONTRIBUTING.md) for more information how you can do this.
