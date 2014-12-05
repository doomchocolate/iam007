module.exports = function( grunt ) {

	// Unobtrusive JSON reader.
	function readJSON( filepath ) {
		var data = {};
		try {
			data = grunt.file.readJSON( filepath );
		} catch (e) {}
		return data;
	}

	// HTML minifier algorithm.
	function minifyHTML( src ) {
		return src
			.replace( /(^\s+|\s+$)/gm, '' )
			.replace( /<!--.+-->/g, '' )
			.replace( /([a-z]+)="([a-z0-9-]+)"/igm, '$1=$2' )
			.replace( /\n+/g, ' ' );
	}

	var sourceDir = '../_source/',
		tempDir = '../_temp/',
		distDir = '../_release/',
		testDir = '../_test/',
		hintOptions = readJSON('.jshintrc');

	grunt.initConfig({
		path: {
			src: sourceDir,
			temp: tempDir,
			dist: distDir,
			test: testDir
		},
		bowercopy: {
			options: {
				clean: true,
				runbower: true
			},
			toolkit: {
				files: {
					'json.min.js': 'json2/json2.js',
					'underscore.min.js': 'underscore/underscore-min.js',
					'backbone.min.js': 'backbone/backbone-min.js',
					'require.min.js': 'requirejs/require.js',
				}
			},
			testkit: {
				options: {
					destPrefix: '<%= path.test %>lib'
				},
				files: {
					'chai/chai.js': 'chai/chai.js',
					'mocha/mocha.css': 'mocha/mocha.css',
					'mocha/mocha.js': 'mocha/mocha.js',
					'sinon': 'sinon/lib/*.js',
					'sinon/sinon': 'sinon/lib/sinon/*.js'
				}
			}
		},
		cleanup: {
			toolkit: {
				src: [
					'*.min.js',
					'toolkit.js'
				]
			}
		},
		concat: {
			toolkit: {
				src: [
					'json.min.js',
					'underscore.min.js',
					'backbone.min.js',
					'require.min.js'
				],
				dest: 'toolkit.js'
			}
		},
		copy: {
			toolkit: {
				files: [
					{ src: 'toolkit.js', dest: '<%= path.src %>js/toolkit.js' }
				]
			},
			template: {
				files: [
					{ src: '<%= path.src %>js/templates/jst.js', dest: '<%= path.temp %>js/templates/jst.js' },
				]
			},
			dist: {
				files: [
					{ src: '<%= path.src %>js/loader.js', dest: '<%= path.temp %>js/loader.js' },
					{ src: '<%= path.src %>js/toolkit.js', dest: '<%= path.temp %>js/toolkit.js' }
				]
			}
		},
		requirejs: {
			app: {
				options: {
					dir: tempDir,
					appDir: sourceDir,
					baseUrl: './js/',
					mainConfigFile: sourceDir + 'js/bundle.js',
					name: 'bundle',
					optimizeCss: 'standard',
					generateSourceMaps: true,
					preserveLicenseComments: false,
					fileExclusionRegExp: /^(css|html|img|templates|loader.js|toolkit.js|\..+)$/,
					removeCombined: true,
					logLevel: 2,
					optimize: 'uglify2'
				}
			}
		},
		jshint: {
			scripts: {
				src: [
					'Gruntfile.js',
					'<%= path.src %>js/**/*.js',
					'!<%= path.src %>js/loader.js',
					'!<%= path.src %>js/toolkit.js',
					'!<%= path.src %>js/templates/**/*.js'
				],
				options: hintOptions
			}
		},
		jst: {
			options: {
				separator: '\n',
				namespace: 'joms.jst',
				prettify: true,
				processContent: minifyHTML,
				processName: function( filename ) {
					return filename.replace( sourceDir, '' )
						.replace( /\.html$/, '' );
				},
				templateSettings: {
					variable: 'data'
				}
			},
			compile: {
				src: [ '<%= path.src %>html/**/*.html' ],
				dest: '<%= path.src %>js/templates/jst.js'
			}
		},
		mocha: {
			test: {
				src: [ '<%= path.test %>index.html' ],
				options: {
					run: true
				}
			}
		},
		rsync: {
			dist: {
				resources: [{
					from: tempDir,
					to: distDir
				}]
			}
		},
		uglify: {
			toolkit: {
				files: {
					'json.min.js': [ 'json.min.js' ],
					'require.min.js': [ 'require.min.js' ]
				}
			}
		},
		watch: {
			templates: {
				files: [ '<%= jst.compile.src %>' ],
				tasks: [ 'jst:compile', 'copy:template' ]
			}
		}
	});

	// grunt.loadNpmTasks('grunt-bowercopy');
	// grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-jst');
	grunt.loadNpmTasks('grunt-contrib-requirejs');
	// grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');
	// grunt.loadNpmTasks('grunt-mocha');

	// Since toolkit needs to be manually updated, we don't need to have toolkit builder for now.
	// grunt.registerTask( 'prebuild', [
	// 	'bowercopy',
	// 	'uglify:toolkit',
	// 	'concat:toolkit',
	// 	'copy:toolkit',
	// 	'cleanup:toolkit'
	// ]);

	// No mocha testing for now, probably in future release :)
	grunt.registerTask( 'test', [
		'jshint',
		// 'mocha'
	]);

	grunt.registerTask( 'build', [
		'test',
		'jst',
		'requirejs',
		'copy:template',
		'copy:dist',
		'rsync'
	]);

	// Custom tasks.
	// -------------------------------------------------------------------------

	// Remove unnecessary files.
	grunt.registerMultiTask( 'cleanup', function() {
		var files = this.files[ 0 ].src;
		for ( var i = 0; i < files.length; i++ ) {
			grunt.file.delete( files[ i ] );
		}
	});

	// Copy resources without changing modified time.
	grunt.registerMultiTask( 'rsync', function() {
		var exec = require('child_process').exec,
			done = this.async(),
			res = this.data.resources,
			cmd = '';

		if ( res && res.length ) {
			for ( var i = 0; i < res.length; i++ ) {
				cmd += 'rsync -racvi ' + res[ i ].from + ' ' + res[ i ].to + ';';
			}
		}

		cmd || done();
		cmd && exec( cmd, function( err, stdout, stderr ) {
			err && grunt.fail.fatal( 'Problem with rsync: ' + err + ' ' + stderr );
			grunt.log.writeln( stdout );
			done();
		});
	});

};
