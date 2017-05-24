var gulp = require('gulp');
var minify = require('gulp-minify');
var phpcs = require('gulp-phpcs');
var sass = require('gulp-sass');
var watch = require('gulp-watch');
var rename = require('gulp-rename');

/**
 * Take care of CSS/SASS.
 */
var sass_files = ['assets/css/*.scss'];
gulp.task('sass', function() {
	gulp.src(sass_files)
		.pipe(sass({outputStyle:'compressed'}))
		.pipe(rename({ suffix: '.min' }))
		.pipe(gulp.dest('assets/css'));
});

/**
 * Take care of JS.
 */
var js_files = ['assets/js/*.js','!assets/js/*.min.js','!assets/js/*-min.js'];
gulp.task('js', function() {
	gulp.src(js_files)
		.pipe(minify({
			ext: '.min.js'
		}))
		.pipe(gulp.dest('assets/js'))
});

/**
 * "Sniff" the PHP to check
 * against WordPress coding standards.
 */
var php_files = ['**/*.php','!library/**','!vendor/**','!node_modules/**'];
gulp.task('php', function () {
	return gulp.src(php_files)
		.pipe(phpcs({
			bin: './vendor/bin/phpcs',
			standard: 'WordPress-Core'
		}))
		// Log all problems that was found
		.pipe(phpcs.reporter('log'));
});

/**
 * Holds all the test tasks.
 */
gulp.task('test',['php']);

/**
 * "Watch" all the things.
 */
gulp.task('watch', function() {
	gulp.watch(sass_files,['sass']);
	gulp.watch(js_files,['js']);
	gulp.watch(php_files, ['php']);
});

/**
 * Runs the default tasks.
 */
gulp.task('default', ['sass','js','test']);