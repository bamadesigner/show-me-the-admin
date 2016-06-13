var gulp = require('gulp');
var minify = require('gulp-minify');
var sass = require('gulp-sass');
var watch = require('gulp-watch');
var rename = require('gulp-rename');

gulp.task('sass', function() {
	gulp.src('assets/css/*.scss')
		.pipe(sass({outputStyle:'compressed'}))
		.pipe(rename({ suffix: '.min' }))
		.pipe(gulp.dest('assets/css'));
});

gulp.task('compress', function() {
	gulp.src(['assets/js/*.js','!assets/js/*.min.js'])
		.pipe(minify({
			ext:{
            	min:'.js'
            }
		}))
		.pipe(rename({ suffix: '.min' }))
		.pipe(gulp.dest('assets/js'))
});

gulp.task('default', ['sass','compress'], function() {
	gulp.watch('assets/css/*.scss',['sass']);
	gulp.watch(['assets/js/*.js','!assets/js/*.min.js'],['compress']);
});