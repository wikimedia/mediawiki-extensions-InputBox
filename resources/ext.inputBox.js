( function () {
	mw.hook( 'wikipage.content' ).add( () => {
		const userPref = mw.user.options.get( 'search-special-page' );
		const searchPages = mw.config.get( 'SpecialSearchPages' );
		if ( searchPages[ userPref ] !== undefined ) {
			const searchUrl = mw.util.getUrl( searchPages[ userPref ] );
			const inputBoxForms = document.querySelectorAll( '.mw-inputbox-form, .mw-inputbox-form-inline' );
			for ( const inputboxForm of inputBoxForms ) {
				inputboxForm.action = searchUrl;
			}
		}
	} );
}() );
