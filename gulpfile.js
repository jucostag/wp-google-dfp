var gulp = require("gulp"),
    jshint = require("gulp-jshint"),
    concat = require("gulp-concat"),
    uglify = require("gulp-uglify"),
    rename = require("gulp-rename"),
    csso = require('gulp-csso'),
    project = "googleDFP",
    directories = {
        root: "assets",
        js: "scripts",
        css: "css",
        min: "dist",
        admin: "admin"
    },
    src = {
        js: {
            front: directories.root + "/" + directories.js + "/*.js",
            admin: directories.root + "/" + directories.admin + "/adminPanel.js",
        },
        css: {
            front: directories.root + "/" + directories.css + "/*.css",
            admin: directories.root + "/" + directories.admin + "/adminPanel.css"
        } 
    };

gulp.task("scripts", compressJs);
gulp.task("adminScripts", compressAdminJs);
gulp.task("styles", compressCss);
gulp.task("adminStyles", compressAdminCss);
gulp.task("watch", ["scripts", "adminScripts", "styles", "adminStyles"], watchFiles);
gulp.task("default", ["scripts", "adminScripts", "styles", "adminStyles"]);

function compressJs(){
    gulp.src(src.js.front)
    .pipe(jshint())
    .pipe(jshint.reporter("default"))
    .pipe(concat(project + ".min.js"))
    .pipe(uglify())
    .pipe(gulp.dest(directories.root + "/" + directories.min));
}

function compressAdminJs(){
    gulp.src(src.js.admin)
    .pipe(jshint())
    .pipe(jshint.reporter("default"))
    .pipe(concat("adminPanel.min.js"))
    .pipe(uglify())
    .pipe(gulp.dest(directories.root + "/" + directories.admin));
}

function compressCss(){
    return gulp.src(src.css.front)
        .pipe(csso())
        .pipe(rename({
            basename: project,
            extname: ".min.css"
        }))
        .pipe(gulp.dest(directories.root + "/" + directories.min));
}

function compressAdminCss(){
    return gulp.src(src.css.admin)
        .pipe(csso())
        .pipe(rename({
            extname: ".min.css"
        }))
        .pipe(gulp.dest(directories.root + "/" + directories.admin));
}

function watchFiles(){
    gulp.watch(src.js.front, ["scripts"]);
    gulp.watch(src.js.admin, ["adminScripts"]);
    gulp.watch(src.css.admin, ["adminStyles"]);
}