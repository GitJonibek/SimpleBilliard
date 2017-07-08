import gulp from "gulp";
import rename from "gulp-rename";
import uglify from "gulp-uglify";
import duration from "gulp-duration";
import config from "../config.js";

// production環境のみ圧縮する

gulp.task("js:uglify", () => {
  let obj = gulp.src(config.dest + "/js_cat/" + config.js.output.file_name + '.js');
  if (process.env.NODE_ENV === "production") {
    obj = obj.pipe(uglify());
  }

  return obj.pipe(rename({
      suffix: '.min'
    }))
    .pipe(gulp.dest(config.js.output.path))
    .pipe(duration('js:uglify'));
})

gulp.task("js_home:uglify", () => {
    let obj = gulp.src(config.dest + "/jshome_cat/" + config.js.output.home_script_name + '.js');
    if (process.env.NODE_ENV === "production") {
        obj = obj.pipe(uglify());
    }

    return obj.pipe(rename({
        suffix: '.min'
    }))
        .pipe(gulp.dest(config.js.output.path))
        .pipe(duration('js_home:uglify'));
});

gulp.task("js_goals:uglify", () => {
    let obj = gulp.src(config.dest + "/jsgoals_cat/" + config.js.output.goals_script_name + '.js');
    if (process.env.NODE_ENV === "production") {
        obj = obj.pipe(uglify());
    }

    return obj.pipe(rename({
        suffix: '.min'
    }))
        .pipe(gulp.dest(config.js.output.path))
        .pipe(duration('js_goals:uglify'));
});

gulp.task("js_team:uglify", () => {
    let obj = gulp.src(config.dest + "/jsteam_cat/" + config.js.output.team_script_name + '.js');
    if (process.env.NODE_ENV === "production") {
        obj = obj.pipe(uglify());
    }

    return obj.pipe(rename({
        suffix: '.min'
    }))
        .pipe(gulp.dest(config.js.output.path))
        .pipe(duration('js_team:uglify'));
});

gulp.task("js_user:uglify", () => {
    let obj = gulp.src(config.dest + "/jsuser_cat/" + config.js.output.user_script_name + '.js');
    if (process.env.NODE_ENV === "production") {
        obj = obj.pipe(uglify());
    }

    return obj.pipe(rename({
        suffix: '.min'
    }))
        .pipe(gulp.dest(config.js.output.path))
        .pipe(duration('js_user:uglify'));
});

gulp.task("js_evaluation:uglify", () => {
    let obj = gulp.src(config.dest + "/jseval_cat/" + config.js.output.evaluation_script_name + '.js');
    if (process.env.NODE_ENV === "production") {
        obj = obj.pipe(uglify());
    }

    return obj.pipe(rename({
        suffix: '.min'
    }))
        .pipe(gulp.dest(config.js.output.path))
        .pipe(duration('js_evaluation:uglify'));
});

gulp.task("js_vendor:uglify", () => {
  return gulp.src(config.dest + "/js_vendor_cat/" + config.js_vendor.output.file_name + '.js')
    .pipe(uglify())
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(gulp.dest(config.js_vendor.output.path))
    .pipe(duration('js_vendor:uglify'))
})

gulp.task("js_prerender:uglify", () => {
  return gulp.src(config.dest + "/js_prerender_cat/" + config.js_prerender.output.file_name + '.js')
    .pipe(uglify())
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(gulp.dest(config.js_prerender.output.path))
    .pipe(duration('js_prerender:uglify'))
})

gulp.task("angular_vendor:uglify", () => {
  return gulp.src(config.dest + "/angular_vendor_cat/" + config.angular_vendor.output.file_name + '.js')
    .pipe(uglify())
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(gulp.dest(config.angular_vendor.output.path))
    .pipe(duration('angular_vendor:uglify'))
})

gulp.task("angular_app:uglify", () => {
  let obj = gulp.src(config.dest + "/angular_app_cat/" + config.angular_app.output.file_name + '.js');
  if (process.env.NODE_ENV === "production") {
    obj = obj.pipe(uglify({
      options: {
        beautify: true,
        mangle: true
      }
    }));
  }

  return obj.pipe(rename({
      suffix: '.min'
    }))
    .pipe(gulp.dest(config.angular_app.output.path))
    .pipe(duration('angular_app:uglify'))
})
