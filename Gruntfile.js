/*global module:false*/
module.exports = function(grunt) {
  'use strict';

  // Project configuration.
  grunt.initConfig({
    jshint: {
      files: ['Gruntfile.js'],
      options: {
        globals: {
          jQuery: true,
          console: true
        },
        browser: true,
        curly: true,
        eqeqeq: true,
        immed: true,
        latedef: true,
        newcap: true,
        noarg: true,
        sub: true,
        undef: true,
        boss: true,
        eqnull: true
      }
    },


    sass: {
      options: {
        sourceMap: false
      },
      dist: {
        files: [
            {
              'style.css' : 'scss/style.scss',
            },
            {
              expand: true,
              cwd: 'scss/schemes',
              src: ['*.scss'],
              dest: 'styles',
              ext: '.css',
            }
        ]
      }
    },

    watch: {
      gruntfile: {
        files: 'Gruntfile.js',
        tasks: ['jshint'],
        options: {
          reload: true
        }
      },

      sassStyles: {
        files: ['scss/**/*.scss'],
        tasks: ['sass'],
      },
      livereload: {
        files: ['style.css'],
        options: { livereload: true }
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-sass');

  // Default task.
  grunt.registerTask('default', ['jshint', 'sass']);

};