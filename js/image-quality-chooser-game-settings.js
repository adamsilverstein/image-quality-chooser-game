/**
 * JavaScript for the settings page of the Image Quality Chooser game.
 */
( function () {

	// Listen for clicks on the three buttons: export, setup and reset.
	document.addEventListener( 'click', function ( event ) {
		var target = event.target;
		if ( 'BUTTON' === target.tagName ) {
			var action = target.getAttribute( 'data-action' );

			// Send an ajax request with the action.
			var xhr = new XMLHttpRequest();
			xhr.open( 'POST', 'wp-json/image-quality-chooser-game/v1/settings', true );
			xhr.setRequestHeader( 'Content-Type', 'application/json' );
			xhr.setRequestHeader( 'X-WP-Nonce', target.getAttribute( 'data-wp-nonce' ) );
			xhr.send( JSON.stringify( { 'action': action } ) );

			// Handle success and failure.
			xhr.onload = function () {
			}
		}
	}
} )();
