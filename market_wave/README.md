How to setup gulpfile in drupal theme

=> Use the command to install Gulp globally
$ npm install --global gulp-cli

=> now i shall go to theme repo using cd command and will install gulp locally
using given command in theme root directory
$ npm install gulp
$ npm init
package name: (theme repo name) default
version: (1.0.0) default
description: put desc accordingly.
entry point: (index.js) gulpfile.js "default index.js will put gulpfile.js after default
text"
test command: "default empty"
git repository: "default empty"
keywords: "default empty"
author: "default empty"
license: (ISC) default

=> After that Install dependencies
$ npm install gulp gulp-sass gulp-concat gulp-sass-glob gulp-sourcemaps gulp-uglify --save-dev

=> Create a gulpfile.js using command in theme root directory
$ touch gulpfile.js

=> Put this code in gulpfile.js
const { src, dest, watch, series, parallel } = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const sassGlob = require('gulp-sass-glob');
const sourcemaps = require('gulp-sourcemaps');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
var paths = {
sassSrc: 'scss/style.scss',
sassDest: 'css/',
jsSrc: 'js/\*\*/script.js',
jsDest: 'js/',
}

function buildStyles() {
return src(paths.sassSrc)
.pipe(sassGlob())
.pipe(sourcemaps.init())
.pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
.pipe(sourcemaps.write('./'))
.pipe(dest(paths.sassDest));
}

function buildScripts() {
return src(paths.jsSrc)
.pipe(sourcemaps.init())
.pipe(uglify())
.pipe(concat('scripts.js'))
.pipe(sourcemaps.write('./'))
.pipe(dest(paths.jsDest));
}

exports.buildStyles = buildStyles;
exports.buildScripts = buildScripts;
exports.watch = function () {
watch(paths.sassSrc, buildStyles);
watch(paths.jsSrc, buildScripts);
};

exports.default = parallel(buildStyles, buildScripts);

Execute Gulp commands

$ gulp --tasks In case you get "Error: Cannot find module 'sass'" then otherwise
skip 2 next command and start from $gulp command
1 $ npm install sass
2 $ npm install --save-dev gulp-uglify
Check for available tasks
$ gulp --tasks
Execute the default task
$ gulp
Compile SCSS files to CSS
$ gulp buildStyles
Compile js files
$ gulp buildScripts
Watch all changes into SCSS and JS files in real time
$ gulp watch
