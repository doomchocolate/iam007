// -----------------------------------------------------------------------------
// utils/image
// -----------------------------------------------------------------------------

// definition
// ----------
define(function() {

	function Preloader( images, callback ) {
		this.images = [];
		this.loaded = [];
		this.processed = 0;
		this.callback = callback;

		if ( !images || !images.length ) {
			images = [];
		}

		// sanitize url
		var rUrl = /^(http:|https:)?\/\/([a-z0-9-]+\.)+[a-z]{2,4}\/.*$/i;
		for ( var i = 0; i < images.length; i++ ) {
			if ( images[i].match( rUrl ) )
				this.images.push( images[i] );
		}

	}

	Preloader.prototype.load = function() {
		if ( !this.images || !this.images.length ) {
			this.callback([]);
			return;
		}

		for ( var i = 0, img; i < this.images.length; i++ ) {
			img = new Image();
			img.onload = this.onload;
			img.onerror = this.onerror;
			img.onabort = this.onabort;
			img.preloader = this;
			img.src = this.images[i];
		}
	};

	Preloader.prototype.onload = function() {
		this.preloader.loaded.push( this.src );
		this.preloader.oncomplete();
	};

	Preloader.prototype.onerror = function() {
		this.preloader.oncomplete();
	};

	Preloader.prototype.onabort = function() {
		this.preloader.oncomplete();
	};

	Preloader.prototype.oncomplete = function() {
		var i, images = [];

		this.processed++;
		if ( this.processed >= this.images.length ) {
			for ( i = 0; i < this.images.length; i++ )
				if ( this.loaded.indexOf( this.images[ i ] ) > -1 )
					images.push( this.images[ i ] );

			this.callback( images );
		}
	};

	function preload( images, callback ) {
		var pre = new Preloader( images, callback );
		pre.load();
	}

	return {
		preload: preload
	};

});
