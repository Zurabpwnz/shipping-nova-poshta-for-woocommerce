import Admin from '../../../src/js/admin/admin.js';

/* global AdminObject */

describe( 'Admin', () => {
	test( 'First test', () => {
		new Admin();

		expect( true ).toBe( true );
	} );
} );
