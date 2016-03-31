module.exports = (grunt) ->

  react:
    options:
      transform: [['babelify', {compact: false, presets: ['es2015', 'react']}]]
    src: ['<%= config.react %>/app.js']
    dest: '<%= config.dest %>/jssrc/react_app.js'
