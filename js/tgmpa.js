(function( $ ) {
	/*global siteurl, ghreleases, marked */

	var _hash,
		zipUrls     = $( '.latest-zip' ),
		tarUrls     = $( '.latest-tar' ),
		versionNr   = $( '.version-number' ),
		releaseDate = $( '.release-date' ),
		releases    = $( '#releases-table tbody' ),
		latestRelease, latestVersion, releasePublished;

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
			$( '#release-notes' ).prepend( marked( latestRelease.body ) ).prepend( '<h5>Version ' + latestRelease.name + '</h5>' );
			$( '#no-release-notes' ).remove();
		} else {
			$( '#release-notes' ).remove();
		}

		/* Add the last five releases before the current one to the 'Download' section. */
		if ( releases.length ) {
			releases.html( createReleaseTableRows( 5 ) );
		}
	}
	/* Remove the releases table and changelog section if no GH data was received. */
	else {
		$( '#releases-table' ).remove();
		$( '#release-notes' ).remove();
	}

})( jQuery );
