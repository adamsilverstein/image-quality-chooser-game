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

	// Game elements..
	var imageOne  = document.getElementById( 'image-quality-chooser-image-1' );
	var imageTwo  = document.getElementById( 'image-quality-chooser-image-2' );
	var gameData  = document.getElementById( 'image-quality-chooser-game-data' );
	var overlay   = document.getElementById( 'image-quality-chooser-overlay' );
	var imgTagOne = imageOne.getElementsByTagName( 'img' )[ 0 ];
	var imgTagTwo = imageTwo.getElementsByTagName( 'img' )[ 0 ];

	// The game area.
	var gameDiv = document.getElementById( 'image-quality-chooser-game' );
	var selectedImage = 1; // 1 showing by default.

	var highlightGame = function () {
		gameDiv.classList.add( 'image-quality-chooser-game__highlight' );
		setTimeout( function () {
			gameDiv.classList.remove( 'image-quality-chooser-game__highlight' );
		}, 250 );
	};

	/**
	 * Handle the vote submission.
	 */
	var submitVote = function () {
		voted = true;
		// Submit the choice.
		// Send an ajax request with the action.
		var xhr = new XMLHttpRequest();
		xhr.open( 'POST', '/wp-json/image-quality-chooser-game/v1/choose', true );

		// Highlight the button selection.
		var selectedButtonID = 'image-quality-chooser-image-' + selectedImage.toLowerCase() + '-button';
		var selectedButton = document.getElementById( selectedButtonID );
		selectedButton.classList.add( 'image-quality-chooser-game__button_selected' );

		// Disable all of the buttons.
		imageOneButton.disabled = true;
		imageTwoButton.disabled = true;
		neitherButton.disabled = true;

		// Assemble the selection data.
		var submissionData = {
			'selection': selectedImage,
			'comparison-data': {
				'image-1': imgTagOne.getAttribute( 'data-image' ),
				'image-2': imgTagTwo.getAttribute( 'data-image' ),
				'image-1-quality': imgTagOne.getAttribute( 'data-quality' ),
				'image-2-quality': imgTagTwo.getAttribute( 'data-quality' ),
				'image-1-engine': imgTagOne.getAttribute( 'data-engine' ),
				'image-2-engine': imgTagTwo.getAttribute( 'data-engine' ),
				'image-1-size': imgTagOne.getAttribute( 'data-size' ),
				'image-2-size': imgTagTwo.getAttribute( 'data-size' ),
				'image-1-mime': imgTagOne.getAttribute( 'data-mime' ),
				'image-2-mime': imgTagTwo.getAttribute( 'data-mime' ),
				'original-filename': gameData.getAttribute( 'data-game-filename' ),
				'original-filesize': gameData.getAttribute( 'data-original-filesize' ),
				'game-size': gameData.getAttribute( 'data-game-size' ),
			},
		};

		xhr.setRequestHeader( 'Content-Type', 'application/json' );

		// Get the nonce,
		var wpNonce = gameData.getAttribute( 'data-wp-nonce' );

		xhr.setRequestHeader( 'X-WP-Nonce', wpNonce );

		// Show the overlay during the submission.
		overlay.style.display = 'block';

		xhr.send( JSON.stringify( { 'data': submissionData } ) );

		// Handle success and failure.
		xhr.onload = function () {
			// Hide the overlay
			overlay.style.display = 'none';

			// Add the image-quality-chooser-game__complete class to the fame div.
			gameDiv.classList.add( 'image-quality-chooser-game__complete' );

			// Show the all of the image-quality-chooser-game__results divs.
			var results = document.getElementsByClassName( 'image-quality-chooser-game__results' );
			for ( var i = 0; i < results.length; i++ ) {
				results[ i ].style.display = 'block';
			}

			// Hide the image headers.
			var imageHeaders = document.getElementsByClassName( 'image-quality-chooser-game__image_header' );
			for ( var i = 0; i < imageHeaders.length; i++ ) {
				imageHeaders[ i ].style.display = 'none';
			}

			// Add the image-quality-chooser-game__winner class to the winning image.
			var winningImage = document.getElementById( 'image-quality-chooser-image-' + selectedImage );
			winningImage.classList.add( 'image-quality-chooser-game__winner' );

			// Trigger a reload in 45 seconds.
			setTimeout( function () {
				window.location.reload();
			}, 45000 );

			// Show a reload timer countdown in the upper right corner.
			var reloadTimer = document.getElementById( 'image-quality-chooser-reload-timer' );
			var reloadTimerCount = 45;
			setInterval( function () {
				reloadTimerCount--;
				reloadTimer.innerHTML = "Reloading in " + reloadTimerCount + " seconds...";
			}, 1000 );

		};

	};

	document.addEventListener( 'keypress', function ( event ) {
		keyCode = event.keyCode;
		// 49 is the key code for the number `1`. Show image 1.
		if ( 49 === keyCode ) {
			// Show image 1.
			imageOne.style.display = 'inline';
			imageTwo.style.display = 'none';
			selectedImage = "1";
			highlightGame();
		}

		// 50 is the key code for the number `2`. Show image 2
		if ( 50 === keyCode ) {
			// Show image 2.
			imageOne.style.display = 'none';
			imageTwo.style.display = 'inline';
			selectedImage = "2";
			highlightGame();
		}

		// When users press `z`, zoom the images.
		if ( 122 === keyCode ) {
			// Toggle the zoom class on gameDiv.
			gameDiv.classList.toggle( 'zoom' );
		}

		// 13 is the key code for the `enter` key which submits the selected image.
		if ( 13 === keyCode ) {
			submitVote();
		}
	} );

	// Also listen for button click events for image1, image2 or neither buttons.
	var imageOneButton = document.getElementById( 'image-quality-chooser-image-1-button' );
	var imageTwoButton = document.getElementById( 'image-quality-chooser-image-2-button' );
	var neitherButton = document.getElementById( 'image-quality-chooser-image-neither-button' );

	var handleButtonClick = function ( target ) {
		if ( voted ) {
			return;
		}
		if ( 'BUTTON' !== target.nodeName ) {
			return;
		}

		// Set the selected image based on the button clicked.
		clicked = event.target;
		selectedImage = clicked.getAttribute( 'data-selection' );

		submitVote();
	}

	imageOneButton.addEventListener( 'click', function ( event ) {
		event.preventDefault();
		handleButtonClick( event.target );
	} );

	imageTwoButton.addEventListener( 'click', function ( event ) {
		event.preventDefault();
		handleButtonClick( event.target );
	} );
	neitherButton.addEventListener( 'click', function ( event ) {
		event.preventDefault();
		handleButtonClick( event.target );
	} );;

} )();