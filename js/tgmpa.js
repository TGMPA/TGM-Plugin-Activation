(function( $ ) {
	'use strict';

	/*global siteurl, ghreleases, marked, JSZip, JSZipUtils, saveAs */

	var _hash,
		versionNr     = $( '.version-number' ), // Site-wide.
		releaseDate   = $( '.release-date' ), // Homepage, download page.
		zipUrls, tarUrls, releasesTable, releaseNotes, releases, spinner, publishFieldset, feedbackElm,
		feedbackMsg, reportError, latestVersion, latestRelease, releasePublished, previousRelease; // Download page.

	zipUrls         = $( '.latest-zip' );
	tarUrls         = $( '.latest-tar' );
	releasesTable   = $( '#releases-table' );
	releaseNotes    = $( '#release-notes' );
	releases        = releasesTable.find( 'tbody' );
	spinner         = $( '#spinner' );
	publishFieldset = $( '#tgmpa-form-publish' );
	feedbackElm     = $( '.generator-feedback' );
	feedbackMsg     = $( '#generator-feedback' );
	reportError     = $( '#report-generator-error' );
	latestVersion   = '';

	/**
	 * Redirect hash locations from the pre-2.5.0 website to their new location.
	 */
	if ( window.location.hash ) {

		// This hash is part of the features/home page and has been renamed.
		if ( '#dependencies' === window.location.hash ) {
			window.location.hash = '#requirements';
		}

		// These hashes now have their own page.
		else if ( '#download' === window.location.hash || '#installation' === window.location.hash || '#screenshots' === window.location.hash || '#authors' === window.location.hash ) {
			_hash = window.location.hash.split( '#' );
			window.location.replace( siteurl + '/' + _hash );
		}

		// No action for #features and #license hashes as they are still part of the homepage.
	}

	/**
	 * Sort helper function for sorting the releases array by version number.
	 *
	 * @param {object} a Release A.
	 * @param {object} b Release B.
	 * @returns {number}
	 */
	function compareVersionNumber( a, b ) {
		var aVersion, bVersion, aStatus, bStatus;

		aVersion = getTagFromTagName( a.tag_name );
		bVersion = getTagFromTagName( b.tag_name );
		aStatus  = getStatusFromTagName( a.tag_name );
		bStatus  = getStatusFromTagName( b.tag_name );

		if ( aVersion < bVersion ) { // 2.4.0 vs 2.5.0.
			return -1;
		}
		if ( aVersion > bVersion ) { // 2.5.0 vs 2.4.0.
			return 1;
		}
		if ( aStatus < bStatus ) { // Alpha vs RC2.
			return -1;
		}
		if ( aStatus > bStatus ) { // RC1 vs Alpha.
			return 1;
		}

		// Variable a must be equal to b.
		return 0;
	}

	/**
	 * Get the version number #.#.# from a tag name.
	 * Presumes the tag is in one of the following forms:
	 * - #.#.#
	 * - v#.#.#
	 * - #.#.#-alpha|beta|rc
	 *
	 * @param {string} tagName
	 * @returns {string}
	 */
	function getTagFromTagName( tagName ) {
		if ( tagName.match( /[0-9\.]+/ ) ) {
			return tagName.replace( /^(?:v)?([0-9\.]+)(?:-(?:alpha|beta|rc[0-9-]?))?.*$/i, '$1' );
		} else {
			return '';
		}
	}

	/**
	 * Get the version number #.#.# from a release (tag)name.
	 * Presumes the release is in one of the following forms:
	 * - #.#.#-alpha|beta|rc
	 * - v#.#.#
	 * - #.#.# (yyyy-mm-dd)
	 *
	 * @param {string} name
	 * @returns {string}
	 */
	function getVersionFromName( name ) {
		if ( name.match( /[0-9]+\.[0-9]+\.[0-9]+/ ) ) {
			return name.replace( /^.*?([0-9]+\.[0-9]+\.[0-9]+\-(?:alpha|beta|rc[0-9-]?)).*$/i, '$1' );
		} else {
			return '';
		}
	}

	/**
	 * Get the release status `alpha|beta|rc|rc-1` from a tag name.
	 * Presumes the tag is in one of the following forms:
	 * - #.#.#
	 * - v#.#.#
	 * - #.#.#-alpha|beta|rc
	 *
	 * @param {string} tagName
	 * @returns {string}
	 */
	function getStatusFromTagName( tagName ) {
		if ( tagName.match( /alpha|beta|rc[0-9-]?/i ) ) {
			return tagName.replace( /^(?:v)?(?:[0-9\.]+)(?:-(alpha|beta|rc[0-9-]?))?.*$/i, '$1' ).toLowerCase();
		} else {
			return '';
		}
	}

	/**
	 * Get the release date from a release name if available.
	 * Presume the release name is in the following form: `#.#.# (yyyy-mm-dd)`.
	 *
	 * @param {object} release
	 * @returns {string}
	 */
	function getReleaseDate( release ) {
		var months, releaseDate;

		months = [ 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October',  'November', 'December' ];

		releaseDate = '';
		if ( release.name.match( /\([0-9]{4}-[0-9]{2}-[0-9]{2}\)/ ) ) {
			releaseDate = release.name.replace( /^[^\(]+\(([0-9]{4}-[0-9]{2}-[0-9]{2})\).*$/, '$1' );
		}
		if ( '' === releaseDate && release.published_at ) {
			releaseDate = release.published_at.substr( 0, 10 );
		}

		releaseDate = new Date( releaseDate );
		return months[ releaseDate.getUTCMonth() ] + ' ' + releaseDate.getUTCDate() + ', ' + releaseDate.getUTCFullYear();
	}

	/**
	 * Create the release table rows.
	 *
	 * @param {number} nrOfRows
	 * @returns {string}
	 */
	function createReleaseTableRows( nrOfRows ) {
		var i, maxRows, releaseDate,
			tableRows = '';

		// Reverse the sort order (newest first).
		ghreleases.reverse();

		maxRows = Math.min( nrOfRows, ghreleases.length );

		for ( i = 0; i < maxRows; i++ ) {

			// Prepare the release date - this may be contained in the name for older releases.
			releaseDate = getReleaseDate( ghreleases[ i ] );

			tableRows += [ '<tr><td>', ghreleases[ i ].tag_name, '</td><td>', releaseDate, '</td><td><a href="', ghreleases[i].zipball_url, '">zipball</a></td><td><a href="', ghreleases[i].tarball_url, '">tarball</a></td></tr>' ].join( '' );
		}

		return tableRows;
	}

	/**
	 * Replace the URLs for the zip and tarballs with the correct URLS for the latest release,
	 * add the version nr and release date to the page + add information about
	 * the last five releases before the current one to the 'Download' section.
	 *
	 * Used in the 'Download' section as well as in the download links in the header of each page.
	 */
	if ( ( ghreleases && ghreleases.length ) && ( zipUrls.length || tarUrls.length || versionNr.length || releases.length ) ) {

		// Sort the release by version number and status.
		ghreleases.sort( compareVersionNumber );

		// Take out the latest release.
		latestRelease = ghreleases.pop();

		/* Add the version nr of the latest release to the download sections and the page header. */
		if ( versionNr.length && latestRelease.name ) {
			latestVersion = getVersionFromName( latestRelease.name );
			if ( '' === latestVersion && latestRelease.tag_name ) {
				latestVersion = getVersionFromName( latestRelease.tag_name );
			}
			if ( '' !== latestVersion ) {
				versionNr.html( latestVersion );
			}
		}

		/* Replace the URLs for the zip and tarballs with the correct URLS for the latest release. */
		if ( zipUrls.length && latestRelease.zipball_url ) {
			zipUrls.attr( 'href', latestRelease.zipball_url );
		}

		if ( tarUrls.length && latestRelease.tarball_url ) {
			tarUrls.attr( 'href', latestRelease.tarball_url );
		}

		/* Add the release date of the latest release to the download section. */
		if ( releaseDate.length ) {
			releasePublished = getReleaseDate( latestRelease );
			if ( releasePublished ) {
				releaseDate.html( ', released at ' + releasePublished );
			}
		}

		/* Add the last five releases before the current one to the 'Download' section. */
		if ( releases.length ) {
			releases.html( createReleaseTableRows( 5 ) );
		}

		/* Add the changelog / release notes of the latest version to the download page */
		if ( latestRelease.body.length ) {

			// Markdown-to-html using the marked library - https://github.com/chjj/marked
			marked.setOptions({
				renderer: new marked.Renderer(),
				gfm: true,
				tables: true,
				breaks: true,
				pedantic: false,
				sanitize: true,
				smartLists: true,
				smartypants: true
			});

			/* If available, add the changelog for the previous release. */
			previousRelease = ghreleases.shift();
			if ( previousRelease.body.length ) {
				releaseNotes.prepend( marked( previousRelease.body ) ).prepend( '<h5>Version ' + previousRelease.name + '</h5>' );
			}

			/* Add the changelog for the latest release. */
			releaseNotes.prepend( marked( latestRelease.body ) ).prepend( '<h5>Version ' + latestRelease.name + '</h5>' );
			$( '#no-release-notes' ).remove();

		} else {
			releaseNotes.remove();
		}

	}
	/* Remove the releases table and changelog section if no GH data was received. */
	else {
		releasesTable.remove();
		releaseNotes.remove();
	}

	/**
	 * Report the`results back to the user.
	 *
	 * @param {string} text      The text to show.
	 * @param {string} classname The class to give to the element. Either 'success', 'warning' or 'error'.
	 */
	function showMessage( text, classname ) {
		spinner.hide();
		feedbackElm.fadeTo( 200, 1 ).attr( 'aria-hidden', 'false' );
		feedbackMsg.addClass( classname ).html( text );
		if ( 'error' === classname ) {
			reportError.fadeTo( 0, 1 ).attr( 'aria-hidden', 'false' );
		}
		if ( 'success' === classname ) {
			feedbackElm.delay( 5000 ).fadeTo( 1200, 0 ).attr( 'aria-hidden', 'true' );
		}
	}

	function resetMessage() {
		spinner.show();
		feedbackElm.fadeTo( 0, 0 ).attr( 'aria-hidden', 'true' );
		feedbackMsg.removeClass().html( '' );
		reportError.fadeTo( 0, 0 ).attr( 'aria-hidden', 'true' );
	}

	/**
	 * Validate a text-domain slug.
	 *
	 * Allowed characters: alphanumeric, underscore and dash.
	 *
	 * @param {string} slug The slug to validate.
	 * @returns {number} -1 if it doesn't match, 0 if it does.
	 */
	function validateSlug( slug ) {
		return slug.search( /^[a-z0-9_-]+$/i );
	}

	/**
	 * Validate function prefix.
	 *
	 * Allowed characters: alphanumeric, underscore.
	 *
	 * @param {string} prefix The prefix to validate.
	 * @returns {number} -1 if it doesn't match, 0 if it does.
	 */
	function validatePrefix( prefix ) {
		return prefix.search( /^[a-z0-9_]+$/i );
	}

	/**
	 * Validate an addon name.
	 *
	 * Allowed characters (for now): alphanumeric, underscore, space and dash.
	 *
	 * @param {string} addonName The name to validate.
	 * @returns {number} -1 if it doesn't match, 0 if it does.
	 */
	function validateName( addonName ) {
		return addonName.search( /^[a-z0-9 _-]+$/i );
	}

	/**
	 * Validate the received add-on type.
	 *
	 * @param {string} addonType The target usage of TGMPA.
	 * @returns {number} -1 if it doesn't match, 0 or higher if it does.
	 */
	function validateAddonType( addonType ) {
		var valid = new Array( 'parent-theme', 'child-theme', 'plugin' );
		return $.inArray( addonType, valid );
	}

	/**
	 * Validate the received publish type.
	 *
	 * @param {string} publishType The target publication channel.
	 * @returns {number} -1 if it doesn't match, 0 or higher if it does.
	 */
	function validatePublishType( publishType ) {
		var valid = new Array( 'wporg', 'themeforest', 'other' );
		return $.inArray( publishType, valid );
	}

	/**
	 * Escape a string for use in a regular expression.
	 *
	 * @param {string} str The string to escape.
	 * @returns {string}
	 */
	function reEscape( str ) {
		return str.replace( /[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&' );
	}

	/**
	 * Adjust the file level version tag to indicate that the file was adjusted with the generator.
	 *
	 * Used for both class-tgm-plugin-activation.php as well as example.php but only when they're adjusted.
	 *
	 * @param {string} content     The content to search through.
	 * @param {string} addonName   The name of the theme or plugin.
	 * @param {string} addonType   Whether the target is a parent-theme, child-theme or plugin.
	 * @param {string} publishType The target publication channel.
	 * @returns {string}
	 */
	function addGeneratorUseIndicator( content, addonName, addonType, publishType ) {
		var replacement = ' for ' + addonType.replace( /-/g, ' ' ) + ' ' + addonName;
		if ( 'wporg' === publishType ) {
			replacement += ' for publication on WordPress.org';
		} else if ( 'themeforest' === publishType ) {
			replacement += ' for publication on ThemeForest';
		}
		return content.replace( /(\* @version\s+[0-9\.]+)([\r\n]+)/, '$1' + replacement + '$2' );
	}

	/**
	 * Replace text domains in translation functions.
	 *
	 * Used for both class-tgm-plugin-activation.php as well as example.php.
	 *
	 * The regexes will work as long as there are no strings which contain a ')' within the string,
	 * though the few strings which contain '(%s)' and '%1$s (%2$d/%3$d)' are accounted for.
	 * If another type of string with a ')' in it would be added, the regexes will need adjusting
	 * or a new regex may need to be added.
	 *
	 * @param {string} searchString The original text domain.
	 * @param {string} replacement  The text domain to replace it with.
	 * @param {string} content      The content to search through.
	 * @returns {string}
	 */
	function replaceTextDomain( searchString, replacement, content ) {
		var reBracketsA, reBracketsB, reNoBrackets;

		// Regex escape, but we're not going to be using the searchstring within brackets, so don't escape dashes.
		searchString = reEscape( searchString );
		searchString = searchString.replace( /\\-/g, '-' );

		replacement  = '$1' + replacement + '$2';

		// Deals with "All <span class="count">(%s)</span>" type strings.
		reBracketsA   = new RegExp( '((?:_[_enx]|_[en]x|_n[x]?_noop|__ngettext_noop|translate_nooped_plural)\\((?:[^\\)]+%s\\)){2}[^\\)]+,\\s+\')' + searchString + '(\'\\s+\\))', 'g' );

		// Deals with "Updating Plugin %1$s (%2$d/%3$d)" type strings.
		reBracketsB   = new RegExp( '((?:_[_enx]|_[en]x|_n[x]?_noop|__ngettext_noop|translate_nooped_plural)\\([^\\)]+?%1\\$s \\(%2\\$d/%3\\$d\\)\',\\s+\')' + searchString + '(\'\\s+\\))', 'g' );

		// Deals with strings without () in them or only with () within a potential translators comment.
		reNoBrackets = new RegExp( '((?:_[_enx]|_[en]x|_n[x]?_noop|__ngettext_noop|translate_nooped_plural)\\((?:\\s+\\/\\* translators: [^*]+)?[^\\)]+,\\s+\')' + searchString + '(\'\\s+\\))', 'g' ); // N.B.: This will also catch esc_attr__ and esc_html__ variants.

		content = content.replace( reBracketsA, replacement );
		content = content.replace( reBracketsB, replacement );
		content = content.replace( reNoBrackets, replacement );
		return content;
	}

	/**
	 * Adjust the path to the bundled plugin based on the target usage of TGMPA.
	 *
	 * Used for example.php.
	 *
	 * @param {string} content   The content to search through.
	 * @param {string} addonType Whether the target is a parent-theme, child-theme or plugin.
	 * @returns {string}
	 */
	function replaceBundledPluginVariable( content, addonType ) {
		var re = /('source'\s+=>\s+)get_stylesheet_directory\(\)([^,]+,)/;
		if ( 'parent-theme' === addonType ) {
			return content.replace( re, '$1get_template_directory()$2' );
		} else if ( 'plugin' === addonType ) {
			return content.replace( re, '$1dirname( __FILE__ )$2' );
		}
		return content;
	}

	/**
	 * Adjust the file include call for the TGMPA file based on the target usage of TGMPA.
	 *
	 * Used for example.php.
	 *
	 * @param {string} content   The content to search through.
	 * @param {string} addonType Whether the target is a parent-theme, child-theme or plugin.
	 * @returns {string}
	 */
	function replaceIncludeCall( content, addonType ) {
		var searchString = 'require_once dirname( __FILE__ ) . \'/class-';
		if ( 'parent-theme' === addonType ) {
			return content.replace( searchString, 'require_once get_template_directory() . \'/path/to/class-' );
		} else if ( 'child-theme' === addonType ) {
			return content.replace( searchString, 'require_once get_stylesheet_directory() . \'/path/to/class-' );
		}

		return content;
	}

	/**
	 * Replace the config variables to change the menu page with more logical defaults.
	 *
	 * Used for example.php when the target usage is a plugin.
	 *
	 * @param {string} content The content to search through.
	 * @returns {string}
	 */
	function replaceMenuConfigVariables( content ) {
		content = content.replace( /('parent_slug'\s+=>\s+')themes.php(',)/, '$1plugins.php$2' );
		content = content.replace( /('capability'\s+=>\s+')edit_theme_options(',)/, '$1manage_options$2' );
		return content;
	}

	/**
	 * Remove the config variables to change the menu page.
	 *
	 * Used for example.php when the target usage is a theme to be published on wp.org or themeforest.
	 *
	 * @param {string} content The content to search through.
	 * @returns {string}
	 */
	function removeMenuConfigVariables( content ) {
		return content.replace( /(\t)+'parent_slug'[ ]+=> '[^']*',.*\s+'capability'[ ]+=> '[^']*',[ ]+.*[\n\r]*/, '' );
	}

	/**
	 * Replace the content of the add_admin_menu() function to comply with the theme review requirements.
	 *
	 * Used for class-tgm-plugin-activation.php when the target usage is a theme to be published
	 * on wp.org or themeforest.
	 *
	 * @param {string} content The content to search through.
	 * @returns {string}
	 */
	function replaceAddAdminMenuFunction( content ) {
		var re, replacement;

		re = /(protected function add_admin_menu\([^\)]*\) \{\s+)(?:[^\}]+\}){3}(\s+})/;
		replacement = '$1$this->page_hook = add_theme_page( $args[\'page_title\'], $args[\'menu_title\'], $args[\'capability\'], $args[\'menu_slug\'], $args[\'function\'] );$2';
		return content.replace( re, replacement );
	}

	/**
	 * Remove the hook-ins for loading of our own text domain and remove the related functions.
	 *
	 * Used for class-tgm-plugin-activation.php when the target usage is a theme to be published on wp.org.
	 *
	 * @param {string} content The content to search through.
	 * @returns {string}
	 */
	function removeLoadTextDomainFunctions( content ) {
		var reHookIns, reLoadFunction, reOverloadFunctionA, reOverloadFunctionB,
			replacement = '';

		reHookIns          = /[\t]+\/\*(?:[^\*]+\*)+\/\s+add_action\( 'init', array\( \$this, 'load_textdomain' \)[^)]*\);\s+add_filter\( 'load_textdomain_mofile'[^\r\n]+/;
		reLoadFunction     = /[\t]+\/[\*]{2}(?:[^*]+\*)+\/\s+public function load_textdomain\(\) \{(?:[^\}]+\}){4}/;
		reOverloadFunctionA = /[\t]+\/[\*]{2}(?:[^*]+\*)+\/\s+public function correct_plugin_mofile\([^\)]*\) \{(?:[^\}]+\}){4}/;
		reOverloadFunctionB = /[\t]+\/[\*]{2}(?:[^*]+\*)+\/\s+public function overload_textdomain_mofile\([^\)]*\) \{(?:[^\}]+\}){5}/;

		content = content.replace( reHookIns, replacement );
		content = content.replace( reLoadFunction, replacement );
		content = content.replace( reOverloadFunctionA, replacement );
		content = content.replace( reOverloadFunctionB, replacement );
		return content;
	}

	/**
	 * If no prefix or name has been given (yet), pre-fill the fields based on the text-domain value.
	 */
	$( '#tgmpa-text-domain' ).on( 'blur', function() {
		var prefixElm = $( '#tgmpa-prefix' ),
			nameElm   = $( '#tgmpa-name' );

		if ( '' === prefixElm.val() ) {
			prefixElm.val( $( this ).val().replace( /[ -]/g, '_' ) );
		}

		if ( '' === nameElm.val() ) {
			nameElm.val( $( this ).val().replace( /[_-]/g, ' ' ).toTitleCase() );
		}
	});

	/**
	 * Only show the publication channel fieldset if the targeted usage is a theme.
	 */
	$( 'input:radio[name="tgmpa-flavor"]' ).on( 'change', function() {
		var value = $( this ).val();

		if ( 'parent-theme' === value || 'child-theme' === value ) {
			publishFieldset.fadeTo( 400, 1 ).attr( 'aria-hidden', 'false' );
		} else {
			publishFieldset.fadeTo( 400, 0 ).attr( 'aria-hidden', 'true' );
		}
	});

	// Make sure the state is correct on page load.
	if ( $( 'input:radio[name="tgmpa-flavor"]' ).is( ':checked' ) ) {
		$( 'input:radio[name="tgmpa-flavor"]:checked' ).trigger( 'change' );
	} else {
		publishFieldset.fadeTo( 400, 0 ).attr( 'aria-hidden', 'true' );
	}

	//=========================
	// Custom TGMPA Generation
	//=========================
	$( '#generator-form' ).on( 'submit', function( event ) {
		var tgmpaDir, slug, prefix, addonName, addonType, publishType;

		event.preventDefault();

		// Don't do anything if not submitted through our form.
		if ( '1' !== $( 'input[name="tgmpa_generate"]' ).val() ) {
			return false;
		}

		// Clear any old feedback.
		resetMessage();

		tgmpaDir = 'TGM-Plugin-Activation-' + latestVersion;

		// Get the input from the form.
		if ( $( 'input:radio[name="tgmpa-flavor"]' ).is( ':checked' ) ) {
			addonType = $( 'input:radio[name="tgmpa-flavor"]:checked' ).val();
		}

		slug   = $( '#tgmpa-text-domain' ).val();

		prefix = $( '#tgmpa-prefix' ).val();
		if ( 'undefined' === typeof prefix || '' === prefix ) {
			prefix = slug;
		}

		addonName = $( '#tgmpa-name' ).val();
		if ( 'undefined' === typeof addonName || '' === addonName ) {
			addonName = slug.replace( /-/g, ' ' ).toTitleCase();
		}

		publishType = 'other';
		if ( $( 'input:radio[name="tgmpa-publish"]' ).is( ':checked' ) ) {
			publishType = $( 'input:radio[name="tgmpa-publish"]:checked' ).val();
		}

		// Make sure the prefix has underscores, no dashes.
		prefix = prefix.replace( /-/g, '_' );

		/**
		 * Validate the received data.
		 */
		if ( ( validateSlug( slug ) === -1 || validatePrefix( prefix ) === -1 ) || ( validateName( addonName ) === -1 || validateAddonType( addonType ) === -1 ) || validatePublishType( publishType ) === -1 ) {
			showMessage( 'Invalid input received.', 'error' );

			return false;
		}

		// JSZipUtils.getBinaryContent( 'https://api.github.com/repos/TGMPA/TGM-Plugin-Activation/zipball/2.5.2', function( err, data ) {
		JSZipUtils.getBinaryContent( '../releases/' + tgmpaDir + '.zip', function( err, data ) {
			if ( err ) {
				showMessage( 'Failed to retrieve TGMPA: ' + err, 'error' );
				return false;
			}

			try {

				JSZip.loadAsync( data ).then( function updateContent( zip ) {
					var exampleFileContent, classFileContent;

					/*
					 * File example.php.
					 */
					exampleFileContent = zip.file( tgmpaDir + '/example.php' ).async( 'string' ).then( function( content ) {

						// Replace text domain.
						content = replaceTextDomain( 'theme-slug', slug, content );

						// Replace the file include call.
						content = replaceIncludeCall( content, addonType );

						// Replace the bundled plugin variable.
						content = replaceBundledPluginVariable( content, addonType );

						// Replace function name.
						content = content.replace( /([ '"])my_theme_register_required_plugins(['"\(])/g, '$1' + prefix + '_register_required_plugins$2' );

						// Replace id used for notices.
						content = content.replace( /('id'\s+=>\s+')tgmpa(',)/, '$1' + slug + '$2' );

						// If plugin: change the typical menu location and capability.
						if ( 'plugin' === addonType ) {
							content = replaceMenuConfigVariables( content );
						}

						// WP.org & Themeforest: remove config variables to change the menu page.
						else if ( ( 'wporg' === publishType || 'themeforest' === publishType ) && ( 'parent-theme' === addonType || 'child-theme' === addonType ) ) {
							content = removeMenuConfigVariables( content );
						}

						// Show that the file was adjusted using the generator.
						content = addGeneratorUseIndicator( content, addonName, addonType, publishType );

						return content;
					} );

					// Replace the original file with the new content.
					zip.file( tgmpaDir + '/example.php', exampleFileContent );

					/*
					 * File class-tgm-plugin-activation.php.
					 */
					if ( 'other' !== publishType && ( 'parent-theme' === addonType || 'child-theme' === addonType ) ) {
						classFileContent = zip.file( tgmpaDir + '/class-tgm-plugin-activation.php' ).async( 'string' ).then( function( content ) {

							// Replace the add admin menu function.
							content = replaceAddAdminMenuFunction( content );

							if ( 'wporg' === publishType ) {

								// Remove the load textdomain related functions.
								content = removeLoadTextDomainFunctions( content );

								// Replace text domain.
								content = replaceTextDomain( 'tgmpa', slug, content );
							}

							// Show that the file was adjusted using the generator.
							content = addGeneratorUseIndicator( content, addonName, addonType, publishType );

							return content;
						} );

						// Replace the original file with the new content.
						zip.file( tgmpaDir + '/class-tgm-plugin-activation.php', classFileContent );

						if ( 'wporg' === publishType ) {

							// Remove the languages directory.
							zip.remove( tgmpaDir + '/languages' );
						}
					}

					/*
					 * Remove various development related files.
					 */
					zip.remove( tgmpaDir + '/.editorconfig' );
					zip.remove( tgmpaDir + '/.jscsrc' );
					zip.remove( tgmpaDir + '/.jshintignore' );
					zip.remove( tgmpaDir + '/.scrutinizer.yml' );
					zip.remove( tgmpaDir + '/.travis.yml' );
					zip.remove( tgmpaDir + '/composer.json' );
					zip.remove( tgmpaDir + '/phpcs.xml' );

					return zip;

				} ).then( function serveZip( zip ) {

					/*
					 * Everything has been adjusted, we can trigger the download.
					 */
					if ( JSZip.support.blob ) {
						zip.generateAsync( { type:'blob' } ).then( function success( blob ) {

							// Uses FileSaver.js.
							saveAs( blob, tgmpaDir + '-' + slug + '.zip' );
							showMessage( 'Custom TGMPA succesfully created!', 'success' );

						}, function failure( error ) {
							showMessage( 'Failed to generate Custom TGMPA file: ' + error, 'error' );
						} );

					} else {
						showMessage( 'This browser is not supported.', 'warning' );
					}

					return false;
				} );

			} catch ( error ) {
				showMessage( ' ' + error, 'error' );
			}
		});

		// Make sure that no matter what the spinner will be hidden.
		spinner.hide();
	});

})( jQuery );
