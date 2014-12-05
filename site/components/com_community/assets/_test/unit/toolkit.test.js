// -----------------------------------------------------------------------------
// test for: toolkit
// -----------------------------------------------------------------------------

describe( 'application toolkit', function() {
	var expect = chai.expect;

	describe( 'dynamic and non-blocking script loading', function() {
		it( 'should have LabJS', function() {
			expect( $LAB ).to.exist;
		});

		it( 'should be able to load script dynamically and non-blocking', function( done ) {
			var foo = 0;

			$LAB.script('data/fooscript.js').wait(function() {
				expect( Foobar ).to.exist;
				delete window.Foobar;
				expect( foo ).to.equal( 1 );
				done();
			});

			foo++;
		});
	});

	describe( 'Asynchronous Module Definition (AMD)', function() {
		it( 'should have RequireJS', function() {
			expect( define ).to.exist;
			expect( require ).to.exist;
			expect( requirejs ).to.exist;
		});

		it( 'should be able to load AMD-type script', function( done ) {
			require([ 'data/fooscript.amd.js' ], function( Foobar ) {
				expect( Foobar ).to.exist;
				done();
			});
		});
	});

});
