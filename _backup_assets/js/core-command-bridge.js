( function() {
	if ( ! window.wp || ! wp.commands || ! wp.commands.registerCommand ) {
		return;
	}

	const { registerCommand } = wp.commands;
	const commands = ( window.wpshadowCoreCommands && window.wpshadowCoreCommands.commands ) || [];

	// Debug: log command registration summary to help diagnose visibility
	if ( window.console && Array.isArray( commands ) ) {
		console.debug( '[WPShadow] Registering commands:', commands.map( c => c.label || c.id ) );
	}

	commands.forEach( ( command ) => {
		// Skip invalid payloads.
		if ( ! command || ! command.id || ! command.label || ! command.url ) {
			return;
		}

		registerCommand( {
			name: command.id,
			label: command.label,
			icon: command.icon || 'admin-generic',
			keywords: command.keywords || [],
			context: 'global',
			callback: () => {
				try {
					const target = new URL( command.url, window.location.href );
					const relative = target.pathname + ( target.search || '' ) + ( target.hash || '' );
					window.location.href = relative; // navigate within current origin
				} catch (e) {
					// Fallback to absolute if URL parsing fails
					window.location.href = command.url;
				}
			},
			hint: ( command.hint || command.description || '' ),
		} );
	} );
} )();
