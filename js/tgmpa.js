(function ($) {

	/**
	 * Redirect hash locations from the pre-2.5.0 website to their new location.
	 */
	if ( window.location.hash ) {
		// This hashe is part of the features/home page and has been renamed.
		if ( window.location.hash === '#dependencies' ) {
			window.location.hash = '#requirements';
		}
		// These hashes now have their own page
		else if( window.location.hash === '#download' || window.location.hash === '#installation' || window.location.hash === '#screenshots'  || window.location.hash === '#authors' ) {
			var _hash = window.location.hash.split( '#' );
			window.location.replace( siteurl + '/' + _hash );
		}
		// No action for #features an #license hashes as they are still part of the homepage
	}


	/**
	 * Sort helper function for sorting the releases array by version number.
	 */
	function compare_version_number( a, b ) {
		var a_version = get_tag_from_tag_name( a.tag_name );
		var b_version = get_tag_from_tag_name( b.tag_name );
		var a_status = get_status_from_tag_name( a.tag_name );
		var b_status = get_status_from_tag_name( b.tag_name );

		if( a_version < b_version ) { // 2.4.0 vs 2.5.0
			return -1;
		}
		if( a_version > b_version ) { // 2.5.0 vs 2.4.0
			return 1;
		}
		if( a_status < b_status ) { // alpha vs rc2
			return -1;
		}
		if( a_status > b_status ) { // rc1 vs alpha
			return 1;
		}
		// a must be equal to b
		return 0
	}


	/**
	 * Get the version number #.#.# from a tagname.
	 * Presumes the tag is in one of the following forms:
	 * - #.#.#
	 * - v#.#.#
	 * - #.#.#-alpha|beta|rc
	 */
	function get_tag_from_tag_name( tag_name ) {
		if ( tag_name.match( /[0-9\.]+/ ) ) {
			return tag_name.replace( /^(?:v)?([0-9\.]+)(?:-(?:alpha|beta|rc[0-9-]?))?.*$/i, "$1" );
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
	 */
	function get_version_from_name( name ) {
		if ( name.match( /[0-9]+\.[0-9]+\.[0-9]+/ ) ) {
			return name.replace( /^.*?([0-9]+\.[0-9]+\.[0-9]+\-(?:alpha|beta|rc[0-9-]?)).*$/i, "$1" );
		} else {
			return '';
		}
	}


	/**
	 * Get the release status `alpha|beta|rc|rc-1` from a tagname.
	 * Presumes the tag is in one of the following forms:
	 * - #.#.#
	 * - v#.#.#
	 * - #.#.#-alpha|beta|rc
	 */
	function get_status_from_tag_name( tag_name ) {
		if ( tag_name.match( /alpha|beta|rc[0-9-]?/i ) ) {
			return tag_name.replace( /^(?:v)?(?:[0-9\.]+)(?:-(alpha|beta|rc[0-9-]?))?.*$/i, "$1" ).toLowerCase();
		} else {
			return '';
		}
	}


	/**
	 * Get the release date from a release name if available.
	 * Presume the release name is in the following form: `#.#.# (yyyy-mm-dd)`.
	 */
	function get_releasedate( release ) {
		var months =  [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October",  "November", "December" ];
		
		var release_date = '';
		if( release.name.match( /\([0-9]{4}-[0-9]{2}-[0-9]{2}\)/ ) ) {
			release_date = release.name.replace( /^[^\(]+\(([0-9]{4}-[0-9]{2}-[0-9]{2})\).*$/, "$1" )
		}
		if ( '' === release_date && release.published_at ) {
			release_date = release.published_at.substr( 0, 10 );
		}

		release_date = new Date( release_date );
		return months[ release_date.getUTCMonth() ] + ' ' + release_date.getUTCDate() + ', ' + release_date.getUTCFullYear();
	}


	/**
	 * Replace the URLs for the zip and tarballs with the correct URLS for the latest release,
	 * add the version nr and release date to the page + add information about
	 * the last five releases before the current one to the 'Download' section.
	 *
	 * Used in the 'Download' section as well as in the download links in the header of each page.
	 */
	var zip_urls     = $('.latest-zip');
	var tar_urls     = $('.latest-tar');
	var version_nr   = $('.version-number');
	var release_date = $('.release-date');
	var releases     = $('#releases-table tbody');

	if ( ( ghreleases && ghreleases.length ) && ( zip_urls.length || tar_urls.length || version_nr.length || releases.length ) ) {

		// Sort the release by version number and status.
		ghreleases.sort( compare_version_number );

		// Take out the latest release.
		var latest_release = ghreleases.pop();
		
		/* Add the version nr of the latest release to the download sections and the page header. */
		if ( version_nr.length && latest_release.name ) {
			var latest_version = get_version_from_name( latest_release.name );
			if ( '' === latest_version && latest_release.tag_name ) {
				latest_version = get_version_from_name( latest_release.tag_name );
			}
			if ( '' !== latest_version ) {
				version_nr.html( latest_version );
			}
		}

		/* Replace the URLs for the zip and tarballs with the correct URLS for the latest release. */
		if ( zip_urls.length && latest_release.zipball_url ) {
			zip_urls.attr( 'href', latest_release.zipball_url );
		}

		if ( tar_urls.length && latest_release.tarball_url ) {
			tar_urls.attr( 'href', latest_release.tarball_url );
		}

		/* Add the release date of the latest release to the download section. */
		if ( release_date.length ) {
			var published = get_releasedate( latest_release );
			if ( published ) {
				release_date.html( ', released at ' + published );
			}
		}
		
		/* Add the changelog / release notes of the latest version to the download page */
		if ( latest_release.body.length ) {
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
			$('#release-notes').prepend( marked( latest_release.body ) );
			$('#no-release-notes').remove();
		}
		else {
			$('#release-notes').remove();
		}

		/* Add the last five releases before the current one to the 'Download' section. */
		if ( releases.length ) {

			// Reverse the sort order (newest first).
			ghreleases.reverse();

			var tablerows = '';
			var max_rows = Math.min( 5, ghreleases.length );

			for ( var i = 0; i < max_rows; i++ ) {
				// Prepare the release date - this may be contained in the name for older releases.
				var release_date_cell = get_releasedate( ghreleases[i] );

				tablerows += ['<tr><td>', ghreleases[i].tag_name, '</td><td>', release_date_cell, '</td><td><a href="', ghreleases[i].zipball_url, '">zipball</a></td><td><a href="', ghreleases[i].tarball_url, '">tarball</a></td></tr>'].join('');
			}
			releases.html( tablerows );
		}
	}
	/* Remove the releases table and changelog section if no GH data was received. */
	else {
		$('#releases-table').remove();
		$('#release-notes').remove();
	}

})(jQuery);
