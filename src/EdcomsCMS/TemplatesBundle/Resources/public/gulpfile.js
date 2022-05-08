var gulp = require('gulp');

// Load all plugins
var plug = require('gulp-load-plugins')({
  pattern: ['gulp-*', 'gulp.*', 'main-bower-files'],
  replaceString: /\bgulp[\-.]/
});

// Post CSS options
var autoprefixerOptions = {
  browsers: ['last 2 versions', '> 5%', 'Firefox ESR']
};

var sassOptions = {
  errLogToConsole: true,
  outputStyle: 'expanded'
};

// Globale variables
var sassInput = './src/assets/sass/**/*.scss',
  cssOutput = './dist/assets/css/',
  jsVendor = [ './src/assets/js/vendor/**/*.js' ],
  jsInput = [ './src/app/**/*module*.js', './src/app/**/*.js' ],
  jsOutput = './dist/assets/js/',
  htmlInput = [ './src/app/**/*.html' ];

// CSS TASKS
gulp.task('sasslint', function () {
  return gulp
    .src(sassInput)
    .pipe(plug.plumber(function (error) {
      console.log('sasslint error: ' + error.message);
      this.emit('end');
    }))
    .pipe(plug.filter(['**', '!**/libs/*.scss']))
    .pipe(plug.sassLint())
    .pipe(plug.sassLint.format())
    .pipe(plug.sassLint.failOnError());
});

gulp.task('sass', ['assets:copy'], function () {
  return gulp
    .src(sassInput)
    .pipe(plug.plumber(function (error) {
      console.log('sass error: ' + error.message);
      this.emit('end');
    }))
    .pipe(plug.sourcemaps.init())
    .pipe(plug.sass(sassOptions).on('error', plug.sass.logError))
    .pipe(plug.sourcemaps.write())
    .pipe(plug.autoprefixer(autoprefixerOptions))
    .pipe(plug.rename('main.css'))
    .pipe(gulp.dest(cssOutput))
    .pipe(plug.livereload());
});

gulp.task('minify', ['sass'], function () {
  return gulp.src(cssOutput + '*.css')
    .pipe(plug.cssnano())
    .pipe(plug.rename('main.css'))
    .pipe(gulp.dest(cssOutput));
});

// JS TASKS
gulp.task('eslint', function () {
  return gulp.src(jsInput)
    .pipe(plug.eslint())
    .pipe(plug.eslint.format())
    .pipe(plug.eslint.failAfterError());
});

gulp.task('jsvendor', function () {
  return gulp.src(plug.mainBowerFiles().concat(jsVendor))
    .pipe(plug.filter('**/*.js'))
    .pipe(plug.order([
      'jquery.js',
      'angular.js',
      'angular-*',
      '**/jquery-ui.min.js',
      '**/jquery.uploadifive.min.js',
      'toastr.js',
      '*'
    ]))
    .pipe(plug.concat('vendor.js'))
    .pipe(plug.plumber(function (error) {
      console.log('jsvendor error: ' + error.message);
      this.emit('end');
    }))
    .pipe(gulp.dest(jsOutput));

})

gulp.task('scripts', ['jsvendor'], function () {
  return gulp.src(jsInput)
    .pipe(plug.babel())
    .pipe(plug.ngAnnotate({
      add: true,
      single_quotes: true
    }))
    .pipe(plug.concat('app.js'))
    .pipe(plug.plumber(function (error) {
      console.log('scripts error: ' + error.message);
      this.emit('end');
    }))
    .pipe(gulp.dest(jsOutput));
});

// TODO: Improve to remove extra files generated
gulp.task('build:js', ['scripts'], function () {
  return gulp.src([jsOutput + 'vendor.js', jsOutput + 'app.js'])
    .pipe(plug.concat('main.js'))
    .pipe(plug.plumber(function (error) {
      console.log('js error ' + error.message);
      this.emit('end');
    }))
    .pipe(gulp.dest(jsOutput))
    .pipe(plug.livereload());
})

gulp.task('uglify', ['build:js'], function () {
  return gulp.src(jsOutput + 'main.js')
    .pipe(plug.uglify())
    .pipe(gulp.dest(jsOutput));
});

gulp.task('html', function () {
  gulp.src(htmlInput)
    .pipe(plug.livereload());
});

// copy assets to dist
gulp.task('assets:copy', function () {
  gulp.src(['./src/assets/fonts/*'])
    .pipe(gulp.dest('./dist/fonts'));
});

// ALL TASKS
gulp.task('watch', ['dev'], function () {
  plug.livereload.listen();
  gulp
    // Watch the input folder for change,
    // and run `concatcss` task when something happens
    .watch(sassInput, ['css'])
    // When there is a change,
    // log a message in the console
    .on('change', function (event) {
      console.log('File ' + event.path + ' was ' + event.type + ', running tasks...');
    });
  gulp.watch([jsInput, './test/**/*.js'], ['test'])
    .on('change', function (event) {
      console.log('File ' + event.path + ' was ' + event.type + ', running tasks...');
    });

  gulp.watch(htmlInput, ['html'])
    .on('change', function (event) {
      console.log('File ' + event.path + ' was ' + event.type + ', running tasks...');
    });

});

gulp.task('test', ['js'], function (done) {
  var Server = require('karma').Server;

  return new Server({
    configFile: __dirname + '/karma.conf.js',
    singleRun: true
  }, done).start();
});

// ALL TASKS
gulp.task('clean', function () {
  return gulp.src([jsOutput + '*.js', cssOutput + '*.css'])
    .pipe(plug.clean({
      force: true
    }));
});

gulp.task('css', ['sasslint', 'sass']);
gulp.task('cssprod', ['minify']);
gulp.task('js', ['eslint', 'build:js']);
gulp.task('jsprod', ['uglify']);
gulp.task('dev', ['css', 'js']);
gulp.task('prod', ['clean', 'cssprod', 'jsprod']);
gulp.task('default', ['dev']);
