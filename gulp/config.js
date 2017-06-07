const assets_dir = './app/static_src'
const compiled_assets_dir = './app/webroot'
const node_modules_dir = './node_modules'
const config = {
  assets_dir,
  compiled_assets_dir,
  compiled_js_dir: compiled_assets_dir + '/js',
  dest: assets_dir + '/dest',
  js: {
    pages: {
        home: [
            assets_dir + '/js/lib/forms.js',
            assets_dir + '/js/lib/notify.js',
            assets_dir + '/js/lib/comments.js',
            assets_dir + '/js/lib/actions.js',
            assets_dir + '/js/lib/feed.js',
            assets_dir + '/js/lib/posts.js'
        ],
        goals: [
            assets_dir + '/js/lib/goals.js',
        ]
    },
    src: [
      assets_dir + '/js/dropzone_setting.js',
      assets_dir + '/js/lib/global.js',
      assets_dir + '/js/lib/header.js',
      assets_dir + '/js/lib/select2.js',
      assets_dir + '/js/lib/intercom.js',
      assets_dir + '/js/gl_basic.js',
      assets_dir + '/js/view/**/*.js'
    ],
    output: {
      file_name: 'goalous',
      home_script_name: 'goalous_home',
      goals_script_name: 'goalous_goal',
      path: compiled_assets_dir + '/js'
    },
    watch_files: [
      assets_dir + '/js/dropzone_setting.js',
      assets_dir + '/js/gl_basic.js',
      assets_dir + '/js/lib/**/*.js',
      assets_dir + '/js/view/**/*.js'
    ]
  },
  js_prerender: {
    src: [
      node_modules_dir + '/jquery/dist/jquery.js'
    ],
    output: {
      file_name: 'goalous.prerender',
      path: compiled_assets_dir + '/js'
    }
  },
  js_vendor: {
    src: [
      node_modules_dir + '/dropzone/dist/min/dropzone.min.js',
      node_modules_dir + '/jquery-lazy/jquery.lazy.js',
      node_modules_dir + '/raven-js/dist/raven.js',
      node_modules_dir + '/bootstrap/dist/js/bootstrap.min.js',
      node_modules_dir + '/jasny-bootstrap/dist/js/jasny-bootstrap.min.js',
      assets_dir + '/js/vendor/bootstrapValidator.js',
      node_modules_dir + '/bootstrap-switch/dist/js/bootstrap-switch.js',
      assets_dir + '/js/vendor/bvAddition.js',
      node_modules_dir + '/noty/lib/noty.js',
      assets_dir + '/js/vendor/jquery.nailthumb.1.1.js',
      node_modules_dir + '/autosize/dist/autosize.js',
      assets_dir + '/js/vendor/lightbox-custom.js',
      assets_dir + '/js/vendor/jquery.showmore.src.js',
      assets_dir + '/js/vendor/customRadioCheck.js',
      assets_dir + '/js/vendor/select2.js',
      node_modules_dir + '/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js',
      assets_dir + '/js/vendor/locales/bootstrap-datepicker.ja.js',
      node_modules_dir + '/moment/min/moment.min.js',
      node_modules_dir + '/pusher-js/dist/web/pusher.min.js',
      assets_dir + '/js/vendor/jquery.flot.js',
      assets_dir + '/js/vendor/jquery.balanced-gallery.js',
      node_modules_dir + '/imagesloaded/imagesloaded.pkgd.min.js',
      assets_dir + '/js/vendor/bootstrap.youtubepopup.js',
      node_modules_dir + '/fastclick/lib/fastClick.js',
      node_modules_dir + '/select2/select2.js',
      node_modules_dir + '/requirejs/require.js',
      node_modules_dir + '/exif-js/exif.js'
    ],
    output: {
      file_name: 'vendors',
      path: compiled_assets_dir + '/js'
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
      path: compiled_assets_dir + '/js'
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
      assets_dir + '/js/vendor/angular/angular-sanitize.js',
      assets_dir + '/js/vendor/angular/pusher-angular.min.js',
      assets_dir + '/js/vendor/angular/ng-infinite-scroll.min.js'
    ],
    output: {
      file_name: 'ng_vendors',
      path: compiled_assets_dir + '/js'
    },
    watch_files: [
      assets_dir + '/js/vendor/angular/**/*.js'
    ]
  },
  react: [
    'setup_guide',
    'signup',
    'goal_create',
    'goal_edit',
    'goal_approval',
    'goal_search',
    'kr_column',
    'message'
  ],
  css: {
    src: [
      assets_dir + '/css/goalstrap.css',
      assets_dir + '/css/jasny-bootstrap.css',
      assets_dir + '/css/font-awesome.css',
      assets_dir + '/css/jquery.nailthumb.1.1.css',
      assets_dir + '/css/bootstrapValidator.css',
      assets_dir + '/css/bootstrap-switch.css',
      node_modules_dir + '/noty/lib/noty.css',
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
    watch_files: [assets_dir + '/css/**/*.css', '!' + assets_dir + '/css/goalous.min.css'],
    output: {
      file_name: 'goalous',
      path: compiled_assets_dir + '/css'
    }
  },
  less: {
    src: [assets_dir + '/less/goalous.less'],
    watch_files: [assets_dir + '/less/**/*.less']
  }
}

const react_apps_contain_undefined = Object.keys(config).map((alias_name) => {
  // react以外のkeyはundefinedとして格納される
  if (alias_name.indexOf('react_') !== -1) {
    return alias_name
  }
})

config.react_apps = react_apps_contain_undefined.filter((alias_name) => {
  return typeof alias_name !== 'undefined'
});
export default config
