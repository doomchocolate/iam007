// -----------------------------------------------------------------------------
// test for: utils/imgcrop
// -----------------------------------------------------------------------------

describe( 'utils/imgcrop', function() {
	var expect = chai.expect;

	it( 'Should be able to create image cropper.' );
	it( 'Should be able to create persistent image cropper.' );
	it( 'Should be able to create rectangular (1:1) image cropper.' );
	it( 'Should be able to GET image cropper position and dimension (x1, y1, x2, y2).' );
	it( 'Should be able to SET image cropper position and dimension (x1, y1, x2, y2).' );
	it( 'Shouldn\'t be able to resize image cropper smaller than permitted minimum size.' );
	it( 'Shouldn\'t be able to resize image cropper larger than permitted maximum size.' );
	it( 'Should be able to move it with mouse events.' );
	it( 'Should be able to move it with touch events.' );

});
