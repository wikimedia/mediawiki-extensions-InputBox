/**
 * JavaScript functions for greying out InputBox Submit button until input recieved
 * from user
 *
 * @author Tony Thomas
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
( function ( $, mw ) {
	'use strict';
	mw.hook( 'wikipage.content' ).add( function( $content ) {
		var onChange,
			events = 'keyup input change';

		onChange = function() {
			var $textbox = $( this ),
				$submit = $textbox.data( 'form-submit' );

			if ( !$submit ) {
				$submit = $textbox.nextAll( 'input.createboxButton' ).first();
				$textbox.data( 'form-submit', $submit );
			}

			$submit.prop( 'disabled', $textbox.val().length < 1 );
		};

		$content
			.find( '.createboxInput' )
			.on( events, onChange )
			.trigger( 'keyup' )
			.off( events, onChange )
			.on( events, $.debounce( 50, onChange ) );
      } );
}( jQuery, mediaWiki ) );