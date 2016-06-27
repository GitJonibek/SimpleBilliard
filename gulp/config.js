const assets_dir = './app/webroot'
const config =  {
  dest: assets_dir + '/dest',
  js: {
    src: [assets_dir + '/js/gl_basic.js'],
    output: {
      file_name: 'goalous',
      path: assets_dir + '/js',
    },
    watch_files: [assets_dir + '/js/gl_basic.js']
  },
  js_vendor: {
    src: [
      assets_dir + '/js/vendor/jquery-1.11.1.min.js',
      assets_dir + '/js/vendor/bootstrap.js',
      assets_dir + '/js/vendor/jasny-bootstrap.js',
      assets_dir + '/js/vendor/bootstrapValidator.js',
      assets_dir + '/js/vendor/bootstrap-switch.js',
      assets_dir + '/js/vendor/bvAddition.js',
      assets_dir + '/js/vendor/pnotify.custom.js',
      assets_dir + '/js/vendor/jquery.nailthumb.1.1.js',
      assets_dir + '/js/vendor/jquery.autosize.js',
      assets_dir + '/js/vendor/jquery.lazy.js',
      assets_dir + '/js/vendor/lightbox-custom.js',
      assets_dir + '/js/vendor/jquery.showmore.src.js',
      assets_dir + '/js/vendor/placeholders.js',
      assets_dir + '/js/vendor/customRadioCheck.js',
      assets_dir + '/js/vendor/select2.js',
      assets_dir + '/js/vendor/bootstrap-datepicker.js',
      assets_dir + '/js/vendor/locales/bootstrap-datepicker.ja.js',
      assets_dir + '/js/vendor/moment.js',
      assets_dir + '/js/vendor/pusher.js',
      assets_dir + '/js/vendor/dropzone.js',
      assets_dir + '/js/vendor/jquery.flot.js',
      assets_dir + '/js/vendor/jquery.balanced-gallery.js',
      assets_dir + '/js/vendor/imagesloaded.pkgd.js',
      assets_dir + '/js/vendor/bootstrap.youtubepopup.js',
      assets_dir + '/js/vendor/fastClick.js',
      assets_dir + '/js/vendor/require.js',
      assets_dir + '/js/vendor/exif.js'
    ],
    output: {
      file_name: 'vendors',
      path: assets_dir + '/js'
    },
    watch_files: [assets_dir + '/js/vendor/*.js']
  },
  coffee: {
    src: [assets_dir + '/coffee/**/*.coffee'],
    watch_files: [assets_dir + '/coffee/**/*.coffee']
  },
  angular_app: {
    src: [
      assets_dir + '/js/angular/app/**/*.js',
      assets_dir + '/js/angular/controller/**/*.js'
    ],
    output: {
      file_name: 'ng_app',
      path: assets_dir + '/js'
    },
    watch_files: [
      assets_dir + '/js/vendor/angular/**/*.js'
    ]
  },
  angular_vendor: {
    src: [
      assets_dir + '/js/vendor/angular/angular.js',
      assets_dir + '/js/vendor/angular/angular-ui-router.js',
      assets_dir + '/js/vendor/angular/angular-route.js',
      assets_dir + '/js/vendor/angular/angular-translate.js',
      assets_dir + '/js/vendor/angular/angular-translate-loader-static-files.js',
      assets_dir + '/js/vendor/angular/ui-bootstrap-tpls-0.13.0.js',
      assets_dir + '/js/vendor/angular/angular-pnotify.js',
      assets_dir + '/js/vendor/angular/angular-sanitize.js'
    ],
    output: {
      file_name: 'ng_vendors',
      path: assets_dir + '/js'
    },
    watch_files: [
      assets_dir + '/js/vendor/angular/**/*.js'
    ]
  },
  react_setup: {
    src: assets_dir + '/js/react/setup_guide/app.js',
    output: {
      file_name: 'react_app',
      path: assets_dir + '/js'
    },
    watch_files: assets_dir + '/js/react/setup_guide/**/*.js'
  },
  css: {
    src: [
      assets_dir + '/css/goalstrap.css',
      assets_dir + '/css/jasny-bootstrap.css',
      assets_dir + '/css/font-awesome.css',
      assets_dir + '/css/jquery.nailthumb.1.1.css',
      assets_dir + '/css/bootstrapValidator.css',
      assets_dir + '/css/bootstrap-switch.css',
      assets_dir + '/css/pnotify.custom.css',
      assets_dir + '/css/lightbox.css',
      assets_dir + '/css/showmore.css',
      assets_dir + '/css/bootstrap-ext-col.css',
      assets_dir + '/css/customRadioCheck.css',
      assets_dir + '/css/select2.css',
      assets_dir + '/css/select2-bootstrap.css',
      assets_dir + '/css/datepicker3.css',
      assets_dir + '/css/style.css',
      assets_dir + '/css/nav.css',
      assets_dir + '/css/nav_media.css'
    ],
    watch_files: [assets_dir + '/css/**/*.css', assets_dir + '!' + assets_dir + '/css/goalous.min.js'],
    output: {
      file_name: 'goalous',
      path: assets_dir + '/css'
    }
  },
  less: {
    src: [assets_dir + '/less/goalous.less'],
    watch_files: [assets_dir + '/less/**/*.less']
  }
}

export default config
