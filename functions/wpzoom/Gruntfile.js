/*jshint esversion: 6 */

const autoprefixer = require("autoprefixer");
const flexibility = require("postcss-flexibility");
const sass = require("node-sass");

/*global module:false*/
module.exports = function (grunt) {
    "use strict";

    grunt.initConfig({
        jshint: {
            files: ["Gruntfile.js"],
            options: {
                globals: {
                    jQuery: true,
                    console: true,
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
                eqnull: true,
                node: true,
            },
        },

        sass: {
            options: {
                implementation: sass,
                sourceMap: false,
                outputStyle: "expanded",
                linefeed: "lf",
                indentWidth: 4,
            },
            dist: {
                files: [
                    {
                    	'components/demo-importer/assets/css/admin.css': 'components/demo-importer/assets/scss/admin.scss',
                    	'components/onboarding/assets/css/admin.css': 'components/onboarding/assets/scss/admin.scss',
                        'assets/options.css': 'assets/scss/options.scss',
                    },
                    // {
                    //     expand: true,
                    //     cwd: "./scss",
                    //     src: ["**/*.scss", "!**/node_modules/**", "!vendor/**"],
                    //     dest: "./css",
                    //     ext: ".css",
                    // },
                ],
            },
        },

        postcss: {
            options: {
                map: false,
                processors: [
                    flexibility,
                    autoprefixer({
                        overrideBrowserslist: [
                            "> 1%",
                            "ie >= 11",
                            "last 1 Android versions",
                            "last 1 ChromeAndroid versions",
                            "last 2 Chrome versions",
                            "last 2 Firefox versions",
                            "last 2 Safari versions",
                            "last 2 iOS versions",
                            "last 2 Edge versions",
                            "last 2 Opera versions",
                        ],
                        cascade: false,
                    }),
                ],
            },
            dist: {
                src: ["**/*.css", "!**/node_modules/**", "!vendor/**"],
            },
        },

        watch: {
            gruntfile: {
                files: "Gruntfile.js",
                tasks: ["jshint"],
                options: {
                    reload: true,
                },
            },
            sassStyles: {
                files: ["**/*.scss", "!**/node_modules/**", "!vendor/**"],
                tasks: ["sass", "postcss"],
            },
        },
    });

    grunt.loadNpmTasks("grunt-contrib-watch");
    grunt.loadNpmTasks("grunt-contrib-jshint");
    grunt.loadNpmTasks("grunt-sass");
    grunt.loadNpmTasks("grunt-browserify");
    grunt.loadNpmTasks("@lodder/grunt-postcss");

    grunt.registerTask("default", ["jshint", "sass", "postcss"]);
};
