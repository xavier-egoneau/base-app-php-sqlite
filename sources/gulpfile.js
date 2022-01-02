/* File: gulpfile.js */
const { gulp, watch, series,src,dest,task } = require('gulp');


const minifyCSS = require('gulp-csso');
const scsslint = require('gulp-scss-lint');

const sass = require('gulp-sass');
const sassGlob = require('gulp-sass-glob');
const notify = require("gulp-notify");
const rename = require("gulp-rename");

const jshint = require('gulp-jshint');
const jshintstylish = require("jshint-stylish");
const uglify = require('gulp-uglify');
const gutil = require('gulp-util');
const pump = require('pump');
const babel = require('gulp-babel');
const sourcemaps = require('gulp-sourcemaps');
const imageop = require('gulp-image-optimization');
const imageResize = require('gulp-image-resize');
const gm = require('gulp-gm');
const order = require("gulp-order");
const concat = require('gulp-concat');
const open = require('gulp-open');
const  ftp = require('vinyl-ftp' );

const fs = require('fs');
const  php = require('gulp-connect-php');
const browserSync = require('browser-sync').create();

var opened = false;

/*
#############################################
paths
#############################################
*/
var paths = {
  base : "../www/",	
  cssBuilder: ['builder/sass/builder.scss'],
  jsBuilder: ["builder/js/scripts/*"],
  jsBuilderVendors: ["builder/js/*","builder/js/libs/*"],

  cssBack: ['backend/sass/vendor.scss','backend/sass/app.scss'],
  jsBack: ["backend/js/*","backend/js/libs/*","backend/js/scripts/*"],
  jsBackVendor: ['backend/js/*','backend/js/libs/*.js'],

  cssFront: ["front/sass/vendor.scss",'front/sass/app.scss'],
  jsVendorFront: ['front/js/*','front/js/libs/*'],
  jsFront: ["front/js/scripts/*"],
  imgFront: ['front/img/**/*']
};




// patterns + 1 with custom callback
// since 2.6.0
task('browser-sync', function() {
    browserSync.init({
        server: {
            baseDir: paths.base
        }
    });
});




/*
#########################################
concat / min all-min.js from app.js + lint
#########################################
*/
function jsBuilder(done) {
  return pump([
        src(paths.jsBuilder),
		order(paths.jsBuilder),
		sourcemaps.init({loadMaps: true}),
		
		babel({            
			presets: ['@babel/env']
		}),
			
		uglify(),
		concat('main.js'),
		
		
		sourcemaps.write(),
        dest(paths.base+'builder'),
		notify("app jsBuilder refresh."),
		browserSync.stream()
		
      ]);
 	done();	
 
};

/*
#########################################
concat / min all-min.js from app.js + lint
#########################################
*/
function jsBuilderVendors(done) {
  return pump([
        src(paths.jsBuilderVendors),
		order(paths.jsBuilder),
		sourcemaps.init({loadMaps: true}),
		
		babel({            
			presets: ['@babel/env']
		}),
			
		uglify(),
		concat('vendors.js'),
		
		
		sourcemaps.write(),
        dest(paths.base+'builder'),
		notify("app jsBuilderVendors refresh."),
		browserSync.stream()
		
      ]);
 	done();	
 
};


function cssBuilder(done){

	
	return pump([
			src(paths.cssBuilder, { sourcemaps: true }),
			sourcemaps.init(),
			sassGlob(),
			sass().on("error", sass.logError),

			concat('style.css'),
			minifyCSS(),
			/*rename({              //renames the concatenated CSS file
			      basename : 'style',       //the base name of the renamed CSS file
			      extname : '.css'      //the extension fo the renamed CSS file
			}),*/
			sourcemaps.write(),
			dest(paths.base+'builder'),
			notify("app cssBuilder refresh."),
			browserSync.stream()
		
	]);
 	done(); 
 };






/*
#########################################
concat / min style.min.css from app.scss
#########################################
*/
function cssFront(done){
	
	return pump([
			
			
			src(paths.cssFront),
			//order(paths.cssFront),
			sourcemaps.init(),
			sassGlob(),
			sass().on("error", sass.logError),
			concat('style.css'),
			minifyCSS(),
			/*rename({              //renames the concatenated CSS file
			      basename : 'style',       //the base name of the renamed CSS file
			      extname : '.css'      //the extension fo the renamed CSS file
			}),*/
			sourcemaps.write(),
			dest(paths.base+'assets/css'),
			
			notify("app cssFront refresh."),
			browserSync.stream()
		
	]);
 	done();	
   
};


/*
#############################################
concat / min vendor-min.js from vendor folder
#############################################
*/
function jsVendorFront(done) {
  
  	return pump([
  	      src(paths.jsVendorFront),
  		  	
  			sourcemaps.init(),
  			order(paths.jsVendorFront),
  			babel({            
				presets: ['@babel/env']
			}),
  			uglify(),
  			concat('vendors.js'),
  			sourcemaps.write(),
  			dest(paths.base + 'assets/js'),
  			notify("app js vendor refresh."),
  			browserSync.stream()
  			
  	    ]);
 	done();
};

/*
#########################################
concat / min all-min.js from app.js + lint
#########################################
*/
function jsFront(done) {
  return pump([
        src(paths.jsFront),
		order(paths.jsFront),
		sourcemaps.init({loadMaps: true}),
		
		babel({            
			presets: ['@babel/env']
		}),
			
		uglify(),
		concat('main.js'),
		
		
		sourcemaps.write(),
        dest(paths.base+'assets/js'),
		notify("app js refresh."),
		browserSync.stream()
		
      ]);
 	done();	
 
};



/*
#########################################
Backend
#########################################
*/
function cssBack(done){
	
	return pump([
			src(paths.cssBack, { sourcemaps: true }),
			order(paths.cssBack),
			sourcemaps.init(),
			
			sassGlob(),
			sass().on("error", sass.logError),
			concat('style.css'),
			minifyCSS(),
			/*rename({              //renames the concatenated CSS file
			      basename : 'style',       //the base name of the renamed CSS file
			      extname : '.css'      //the extension fo the renamed CSS file
			}),*/
			sourcemaps.write(),
			dest(paths.base+'backend/assets/css'),
			notify("app cssBack refresh."),
			browserSync.stream()
		
	]);
 	done();	
   
};

/*
#########################################
concat / min all-min.js from app.js + lint
#########################################
*/
function jsBack(done) {
  return pump([
        src(paths.jsBack),
		order(paths.jsBack),
		sourcemaps.init({loadMaps: true}),
		
		babel({            
			presets: ['@babel/env']
		}),
			
		uglify(),
		concat('main.js'),
		
		
		sourcemaps.write(),
        dest(paths.base+'backend/assets/js'),
		notify("app jsBack refresh."),
		browserSync.stream()
		
      ]);
 	done();	
 
};


/*
#############################################
concat / min vendor-min.js from vendor folder
#############################################
*/
function jsBackVendor(done) {
  
  	return pump([
  	      src(paths.jsBackVendor),
  		  	
  			sourcemaps.init(),
  			order(paths.jsBackVendor),
  			babel({            
				presets: ['@babel/env']
			}),
  			uglify(),
  			concat('vendors.js'),
  			sourcemaps.write(),
  			dest(paths.base+'backend/assets/js'),
  			notify("app jsVendorBack refresh."),
  			browserSync.stream()
  			
  	    ]);
 	done();
};


/*
#########################################
images resize
#########################################
*/
function imgFront(done) {
  
  
	return pump([
			src(paths.imgFront),
			gm(function (gmfile) {
			
			     return gmfile.resize(1800);
			
			}),
			dest(paths.base+'assets/img/'),
			notify("img ended")
	]);
    
 	done();
};


function logs(done) {
  
  	var logpath = paths.base+"logs/logs.txt";
  	var html = fs.readFileSync(logpath, 'utf8');
	pump([
			src(logpath),
      		notify({ message: html, wait: true })
	]);

	if(opened==false){
		pump([
			
			src(logpath),
			open()
		]);
		opened = true;
	}
    
 	done();
};

/*
#########################################
group tasks
#########################################
*/


exports.default = function() {

	var paths = {
	base : "../www/",	
  /*cssBuilder: ['builder/sass/builder.scss'],
  jsBuilder: ["builder/js/scripts/*"],
  jsBuilderVendors: ["builder/js/*","builder/js/libs/*"],

  cssBack: ['backend/sass/vendor.scss','backend/sass/app.scss'],
  jsBack: ["backend/js/*","backend/js/libs/*","backend/js/scripts/*"],
  jsBackVendor: ['backend/js/*','backend/js/libs/*.js'],
*/
  cssFront: ["front/sass/**/*"],
  jsVendorFront: ['front/js/*','front/js/libs/*','front/js/libs/**/*'],
  jsFront: ["front/js/scripts/*"],
  imgFront: ['front/img/**/*'],
  tpls: ['../app/project/root/tpl/**/*','../app/project/root/tpl/*']
	};

	php.server({base: paths.base, port:8010, keepalive:true});

	browserSync.init({
        proxy: "localhost:8010",
        open:true,
        notify:true
    });


  /*
  watch(paths.cssBuilder,cssBuilder ).on('change', browserSync.reload);
  watch(paths.jsBuilder, jsBuilder).on('change', browserSync.reload);
  watch(paths.jsBuilderVendors, jsBuilderVendors).on('change', browserSync.reload);
*/
/*
  watch(paths.cssBack, cssBack).on('change', browserSync.reload);
  watch(paths.jsBack, jsBack).on('change', browserSync.reload);
  watch(paths.jsBackVendor, jsBackVendor).on('change', browserSync.reload);
*/
  watch(paths.cssFront, cssFront).on('change', browserSync.reload);
  watch(paths.jsVendorFront, jsVendorFront).on('change', browserSync.reload);
  watch(paths.jsFront, jsFront).on('change', browserSync.reload);
  watch(paths.imgFront, imgFront).on('change', browserSync.reload);
  watch(paths.tpls).on('change', browserSync.reload);

  watch("../../logs/logs.txt", logs);


  
};

exports.back = function() {

	var paths = {
		base : "../www/",	
		cssBack: ['backend/sass/vendor.scss','backend/sass/app.scss','backend/sass/**/*'],
  		jsBack: ["backend/js/*","backend/js/libs/*","backend/js/scripts/*"],
  		jsBackVendor: ['backend/js/*','backend/js/libs/*.js'],
  		tplsback: ['../app/project/backend/tpl/**/*','../app/project/backend/tpl/*']
	};

	php.server({base: paths.base, port:8010, keepalive:true});

	browserSync.init({
        proxy: "localhost:8010",
        open:true,
        notify:true
    });

  	watch(paths.cssBack, cssBack).on('change', browserSync.reload);
  	watch(paths.jsBack, jsBack).on('change', browserSync.reload);
  	watch(paths.jsBackVendor, jsBackVendor).on('change', browserSync.reload);
  	watch(paths.tplsback).on('change', browserSync.reload);
  	watch("../../logs/logs.txt", logs);


  
};

exports.builder = function() {

	var paths = {
		base : "../www/",	
  		cssBuilder: ['builder/sass/builder.scss'],
  		jsBuilder: ["builder/js/scripts/*"],
  		jsBuilderVendors: ["builder/js/*","builder/js/libs/*"]
	};

	php.server({base: paths.base, port:8010, keepalive:true});

	browserSync.init({
        proxy: "localhost:8010",
        open:true,
        notify:true
    });


  
  watch(paths.cssBuilder,cssBuilder ).on('change', browserSync.reload);
  watch(paths.jsBuilder, jsBuilder).on('change', browserSync.reload);
  watch(paths.jsBuilderVendors, jsBuilderVendors).on('change', browserSync.reload);

  watch("../../logs/logs.txt", logs);


  
};



exports.go = series( cssBuilder, jsBuilder,cssBack,jsBack,jsBackVendor,cssFront,jsVendorFront,jsFront,imgFront);
exports.cssBuilder = series( cssBuilder);
exports.cssBack = series(cssBack);
//exports.lintcss = series(lintcss);
exports.cssFront = series(cssFront);





/*task( 'debug', function () {
	//jslint();
	jslint();
	//lintcss();
} );*/



task( 'deploy', function () {
	

	var conn = ftp.create( {

	// Entrez vos informations ici 
	host:     '', 
	user:     '',
	password: '',
	port:21,
	parallel: 10,
	log:      gutil.log,
	reload: true
	} );


	var globs = [
	'../**',
	'!../sources',
	'!../sources/**',
	'!../sources/*',
	'!gulpfile.js',
    '!../node_modules',
    '!../node_modules/**',
    '!./**',
    '!../storage/**'
	];




	return src( globs, { cwd: '.', buffer: false }  )
	.pipe( conn.newer( '/lab3' ) ) // folder sur le FTP
	.pipe( conn.dest( '/lab3' ) ); // folder sur le FTP

});



