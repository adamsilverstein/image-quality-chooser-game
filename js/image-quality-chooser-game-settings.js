/**
 * JavaScript for the settings page of the Image Quality Chooser game.
 */
( function () {
	console.log( 'admin' );
	// Listen for clicks on the three buttons: export, setup and reset.
	document.addEventListener( 'click', function ( event ) {
		var target = event.target;
		if ( 'BUTTON' === target.tagName ) {
			var action = target.getAttribute( 'data-action' );

			// Send an ajax request with the action.
			var xhr = new XMLHttpRequest();
			xhr.open( 'POST', 'wp-json/image-quality-chooser-game/v1/settings', true );

			xhr.setRequestHeader( 'Content-Type', 'application/json' );

			// Get the nonce from the target's parent's `data-wp-nonce` attribute.
			var wpNonce = target.parentNode.getAttribute( 'data-wp-nonce' );

			xhr.setRequestHeader( 'X-WP-Nonce', wpNonce );
			xhr.send( JSON.stringify( { 'action': action } ) );

			// Handle success and failure.
			xhr.onload = function () {
			}
		}
	} )
} )();
