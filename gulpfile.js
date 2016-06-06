var gulp = require('gulp');
var rename = require('gulp-rename');
var browserify = require('browserify');
var watchify = require('watchify');
var source = require('vinyl-source-stream')
var less = require('gulp-less');
var pkg = require('./package.json');

function getBrowserify() {
    // Konfigurējam browserify
    return browserify({
        entries: ['./assets/js/app.js'],
        // These params are for watchify
        cache: {}, 
        packageCache: {}
    })
}

function bundle(browserify) {
    browserify
        .bundle()
        .on('error', function(er){
            console.log(er.message);
        })
        .pipe(source('app.js'))
        .pipe(rename('app-'+pkg.version+'.js'))
        .pipe(gulp.dest('./build'));
}

gulp.task('browserify', function(){
    bundle(getBrowserify());
});

gulp.task('watchjs', function(){
    var w = watchify(getBrowserify());
    
    w.on('update', function(){
        bundle(w);
        console.log('js files updated');
    });

    w.bundle().on('data', function() {});
});

gulp.task('less', function(){
    gulp.src('./assets/less/app.less')
        .pipe(
            less()
                .on('error', function(er){
                    console.log(er.type+': '+er.message);
                    console.log(er.filename+':'+er.line);
                })
        )
        .pipe(rename('app-'+pkg.version+'.css'))
        .pipe(gulp.dest('./build'));
});

gulp.task('watchless', function(){
    gulp.watch(['./assets/less/**/*.less'], ['less'])
});

gulp.task('default', ['watchjs', 'watchless']);
gulp.task('dist', ['browserify', 'less']);