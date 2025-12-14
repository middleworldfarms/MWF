/**
 * in vagrant ssh, launch:
 * - npm install
 * - grunt (or use npm scripts in package.json)
 */

const potInfo = {
	languageFolderPath: './languages/',
	filename: 'yith-plugin-upgrade-fw.pot',
	potHeaders: {
		poedit: true, // Includes common Poedit headers.
		'x-poedit-keywordslist': true, // Include a list of all possible gettext functions.
		'report-msgid-bugs-to': 'YITH <plugins@yithemes.com>',
		'language-team': 'YITH <info@yithemes.com>'
	},
};

const dirs = {
	css: 'assets/css',
	scss: 'assets/scss',
	js: 'assets/js'
};

const sassFiles = [
	{
		expand: true,
		cwd: '<%= dirs.scss %>/',
		src: ['**/*.scss'],
		dest: '<%= dirs.css %>/',
		ext: '.css',
	}
];

module.exports = function (grunt) {
	'use strict';

	grunt.initConfig({
		dirs: dirs,
		terser: {
			options: {},
			dist: {
				files: [{
					expand: true,
					cwd: '<%= dirs.js %>/',
					src: ["*.js", "!*.min.js"],
					dest: '<%= dirs.js %>/',
					ext: '.min.js',
				}]
			}
		},
		sass : {
			dist: {
				files  : sassFiles,
				options: {
					style    : 'compressed',
					sourceMap: false
				}
			},
			dev : {
				files  : sassFiles,
				options: {
					style: 'expanded',
					sourceMap: false
				}
			}
		},
		watch: {
			css: {
				files: ['assets/scss/**/*.scss'],
				tasks: ['sass:dev']
			}
		},
		makepot: {
			options: {
				type: 'wp-plugin',
				domainPath: 'languages',
				domain: 'yith-plugin-upgrade-fw',
				potHeaders: potInfo.potHeaders,
				updatePoFiles: false
			},
			dist: {
				options: {
					potFilename: potInfo.filename,
					exclude: [
						'bin/.*',
						'dist/.*',
						'node_modules/.*',
						'tests/.*',
						'tmp/.*',
						'vendor/.*'
					]
				}
			}
		},
		update_po: {
			options: {
				template: potInfo.languageFolderPath + potInfo.filename
			},
			build: {
				src: potInfo.languageFolderPath + '*.po'
			}
		},
	});

	grunt.registerMultiTask('update_po', 'This task update .po strings by .pot', function () {
		grunt.log.writeln('Updating .po files.');

		var done = this.async(),
			options = this.options(),
			template = options.template;
		this.files.forEach(function (file) {
			if ( file.src.length ) {
				var counter = file.src.length;

				grunt.log.writeln('Processing ' + file.src.length + ' files.');

				file.src.forEach(function (fileSrc) {
					grunt.util.spawn({
						cmd: 'msgmerge',
						args: ['-U', fileSrc, template]
					}, function (error, result, code) {
						const output = fileSrc.replace('.po', '.mo');
						grunt.log.writeln('Updating: ' + fileSrc + ' ...');

						if ( error ) {
							grunt.verbose.error();
						} else {
							grunt.verbose.ok();
						}

						// Updating also the .mo files
						grunt.util.spawn({
							cmd: 'msgfmt',
							args: [fileSrc, '-o', output]
						}, function (moError, moResult, moCode) {
							grunt.log.writeln('Updating MO for: ' + fileSrc + ' ...');
							counter--;
							if ( moError || counter === 0 ) {
								done(moError);
							}
						});
						if ( error ) {
							done(error);
						}
					});
				});
			} else {
				grunt.log.writeln('No file to process.');
			}
		});
	});

	// Load NPM tasks to be used here.
	grunt.loadNpmTasks('grunt-wp-i18n');
	grunt.loadNpmTasks('grunt-terser');
	grunt.loadNpmTasks( 'grunt-contrib-sass' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );

	grunt.registerTask( 'css', ['sass:dist'] );
	grunt.registerTask('js', ['terser']);
	grunt.registerTask('i18n', ['makepot']);
	grunt.registerTask('default', ['i18n', 'js']);
};
