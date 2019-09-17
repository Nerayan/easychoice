let gulp = require('gulp'),
    rename = require('gulp-rename'),
    sass = require('gulp-sass'),
    cleanCss = require('gulp-clean-css'),
    autoPrefixer = require('gulp-autoprefixer');

gulp.task('compile-admin-scss', function () {
  return gulp.src('src/scss/admin/entry.scss')
    .pipe(sass().on('error', function (error) {
        console.log('ERROR: ' + error);
    }))
    .pipe(autoPrefixer())
    .pipe(rename('admin.min.css'))
    .pipe(cleanCss())
    .pipe(gulp.dest('assets/css'));
});

gulp.task('compile-scss', function () {
  return gulp.src('src/scss/entry.scss')
    .pipe(sass().on('error', function (error) {
      console.log('ERROR: ' + error);
    }))
    .pipe(autoPrefixer())
    .pipe(rename('style.min.css'))
    .pipe(cleanCss())
    .pipe(gulp.dest('assets/css'));
});

gulp.task('watch-scss', function () {
  gulp.watch('src/scss/**/*.scss', gulp.series('compile-scss', 'compile-admin-scss'));
});