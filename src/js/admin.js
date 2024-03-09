/* global ClipboardJS */
jQuery( function( $ ) {
	const vhc = new ClipboardJS( '.vhc-column-id.vhc-has-copy' );
	let successTimeout;

	// Copy the id on click.
	vhc.on( 'success', function( e ) {
		const triggerElement = $( e.trigger ),
			originalText = triggerElement.attr( 'aria-label' ),
			copiedText = triggerElement.attr( 'data-success-text' );

		// Clear the selection and move focus back to the trigger.
		e.clearSelection();
		// Handle ClipboardJS focus bug, see https://github.com/zenorocha/vhc.js/issues/680
		triggerElement.trigger( 'focus' );

		// Show copied as visual feedback.
		clearTimeout( successTimeout );
		triggerElement.attr( 'aria-label', copiedText );

		// Hide copied visual feedback after 1 seconds since last success.
		successTimeout = setTimeout( function() {
			triggerElement.attr( 'aria-label', originalText );
			// Remove the visually hidden textarea so that it isn't perceived by assistive technologies.
			if ( vhc.clipboardAction.fakeElem && vhc.clipboardAction.removeFake ) {
				vhc.clipboardAction.removeFake();
			}
		}, 1000 );
	} );
} );
