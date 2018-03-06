const autoprefixer = require('gulp-autoprefixer');
const cleanCSS = require('gulp-clean-css');
const gulp = require('gulp');
const mergeMediaQueries = require('gulp-merge-media-queries');
const notify = require('gulp-notify');
const rename = require('gulp-rename');
const sass = require('gulp-sass');
const shell = require('gulp-shell');
const uglify = require('gulp-uglify');

// Define the source paths for each file type.
const src = {
	js: ['assets/js/show-me-the-admin.js','assets/js/show-me-the-admin-settings.js','assets/js/show-me-the-admin-user-notice.js'],
	php: ['**/*.php','!vendor/**','!node_modules/**'],
	sass: ['assets/sass/**/*']
};

// Define the destination paths for each file type.
const dest = {
	js: 'assets/js',
	sass: 'assets/css'
};

// Take care of JS.
gulp.task('js', function() {
	gulp.src(src.js)
		.pipe(uglify({
			mangle: false
		}))
		.pipe(rename({
			suffix: '.min'
		}))
		.pipe(gulp.dest(dest.js))
		.pipe(notify('WPC Resources JS compiled'));
});

// Take care of SASS.
gulp.task('sass', function() {
	return gulp.src(src.sass)
		.pipe(sass({
			outputStyle: 'expanded'
		}).on('error', sass.logError))
		.pipe(mergeMediaQueries())
		.pipe(autoprefixer({
			browsers: ['last 2 versions'],
			cascade: false
		}))
		.pipe(cleanCSS({
			compatibility: 'ie8'
		}))
		.pipe(rename({
			suffix: '.min'
		}))
		.pipe(gulp.dest(dest.sass))
		.pipe(notify('WPC Online SASS compiled'));
});

// "Sniff" our PHP.
gulp.task('php', function() {
	// TODO: Clean up. Want to run command and show notify for sniff errors.
	return gulp.src('show-me-the-admin.php', {read: false})
		.pipe(shell(['composer sniff'], {
			ignoreErrors: true,
			verbose: false
		}))
		.pipe(notify('WPC Online PHP sniffed'), {
			onLast: true,
			emitError: true
		});
});

// Test our files.
gulp.task('test',['php']);

// Compile assets.
gulp.task('compile',['js','sass']);

// I've got my eyes on you(r file changes).
gulp.task('watch',function() {
	gulp.watch(src.js,['js']);
	gulp.watch(src.php,['php']);
	gulp.watch(src.sass,['sass']);
});

// Let's get this party started.
gulp.task('default',['compile','test']);
