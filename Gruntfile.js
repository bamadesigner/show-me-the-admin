module.exports = function(grunt) {

    grunt.initConfig({
        uglify: {
            options: {
                mangle: false,
                compress: false
            },
            minified: {
                files: [{
                    expand: true,
                    src: [ '**/*.js', '!**/*.min.js' ],
                    cwd: 'js',
                    dest: 'js',
                    ext: '.min.js'
                }]
            }
        },
        watch: {
            js: {
                files: [ 'js/**/*.js', '!js/**/*.min.js' ],
                tasks: [ 'uglify:minified' ]
            }
        }
    });

    // Load our dependencies
    grunt.loadNpmTasks( 'grunt-contrib-uglify' );
    grunt.loadNpmTasks( 'grunt-contrib-watch' );
    grunt.loadNpmTasks( 'grunt-newer' );

    // Register our tasks
    grunt.registerTask( 'default', [ 'newer:uglify', 'watch' ] );

    // Register a watch function
    grunt.event.on( 'watch', function( action, filepath, target ) {
        grunt.log.writeln( target + ': ' + filepath + ' has ' + action );
    });

};