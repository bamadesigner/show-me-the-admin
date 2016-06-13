var gulp = require('gulp');
var minify = require('gulp-minify');
var sass = require('gulp-sass');
var watch = require('gulp-watch');
var rename = require('gulp-rename');

gulp.task('sass', function() {
	gulp.src('css/*.scss')
		.pipe(sass({outputStyle:'compressed'}))
		.pipe(rename({ suffix: '.min' }))
		.pipe(gulp.dest('css'));
});

gulp.task('compress', function() {
	gulp.src(['js/*.js','!js/*.min.js'])
		.pipe(minify({
			ext:{
            	min:'.js'
            }
		}))
		.pipe(rename({ suffix: '.min' }))
		.pipe(gulp.dest('js'))
});

gulp.task('default', ['sass','compress'], function() {
	gulp.watch('css/*.scss',['sass']);
	gulp.watch(['js/*.js','!js/*.min.js'],['compress']);
});