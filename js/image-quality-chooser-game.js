/**
 * Main Javascript to run the image quality chooser game
 */
( function () {

	// When the user clicks an img or button element, send the selection data
	// to the REST endpoint.
	var voted = false;

	/**
	 * Listen for key presses:
	 * - `1` - show image 1.
	 * - `2` - show image 2.
	 * - `enter` - submit the choice.
	 */

	// Get the two images.
	var imageOne = document.getElementById( 'image-quality-chooser-image-1' );
	var imageTwo = document.getElementById( 'image-quality-chooser-image-2' );

	// The game area.
	var gameDiv = document.getElementById( 'image-quality-chooser-game' );

	document.addEventListener( 'keypress', function ( event ) {
		var target = event.target;
		if ( voted ) {
			return;
		}
		var keyCode = event.keyCode;
		// 49 is the key code for the number `1`. Show image 1.
		if ( 49 === keyCode ) {
			// Show image 1.
			imageOne.style.display = 'inline';
			imageTwo.style.display = 'none';
		}

		// 50 is the key code for the number `2`. Show image 2
		if ( 50 === keyCode ) {
			// Show image 2.
			imageOne.style.display = 'none';
			imageTwo.style.display = 'inline';
		}

		// When users press `z`, zoom the images.
		if ( 122 === keyCode ) {
			// Toggle the zoom class on gameDiv.
			gameDiv.classList.toggle( 'zoom' );
		}

		// 13 is the key code for the `enter` key which submits the selected image.
		if ( 13 === keyCode ) {
			voted = true;
			// Submit the choice.
			// Send an ajax request with the action.
			var xhr = new XMLHttpRequest();
			xhr.open( 'POST', '/wp-json/image-quality-chooser-game/v1/settings', true );

			xhr.setRequestHeader( 'Content-Type', 'application/json' );

			// Get the nonce from the target's parent's `data-wp-nonce` attribute.
			var wpNonce = target.parentNode.getAttribute( 'data-wp-nonce' );

			xhr.setRequestHeader( 'X-WP-Nonce', wpNonce );
			xhr.send( JSON.stringify( { 'action': action } ) );

			// Handle success and failure.
			xhr.onload = function () {
			}
		}

	} );
} )();