/**
 * Gruntfile for compiling theme_squared .less files.
 *
 * This file configures tasks to be run by Grunt
 * http://gruntjs.com/ for the current theme.
 *
 *
 * Requirements:
 * -------------
 * nodejs, npm, grunt-cli.
 *
 * Installation:
 * -------------
 * node and npm: instructions at http://nodejs.org/
 *
 * grunt-cli: `[sudo] npm install -g grunt-cli`
 *
 * node dependencies: run `npm install` in the root directory.
 *
 *
 * Usage:
 * ------
 * Call tasks from the theme root directory. Default behaviour
 * (calling only `grunt`) is to run the watch task detailed below.
 *
 *
 * Porcelain tasks:
 * ----------------
 * The nice user interface intended for everyday use. Provide a
 * high level of automation and convenience for specific use-cases.
 *
 * grunt watch   Watch the less directory (and all subdirectories)
 *               for changes to *.less files then on detection
 *               run 'grunt compile'
 *
 *               Options:
 *
 *               --dirroot=<path>  Optional. Explicitly define the
 *                                 path to your Moodle root directory
 *                                 when your theme is not in the
 *                                 standard location.
 * Options:
 *
 *               --dirroot=<path>  Optional. Explicitly define the
 *                                 path to your Moodle root directory
 *                                 when your theme is not in the
 *                                 standard location.
 *
 * grunt amd     Create the Asynchronous Module Definition JavaScript files.  See: MDL-49046.
 *               Done here as core Gruntfile.js currently *nix only.
 *
 * @package theme
 * @subpackage squared
 * @author G J Barnard - {@link http://moodle.org/user/profile.php?id=442195}
 * @author Based on code originally written by Joby Harding, Bas Brands, David Scotson and many other contributors.
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

module.exports = function(grunt) {

    // Import modules.
    var path = require('path');

    // Theme Bootstrap constants.
    var THEMEDIR        = path.basename(path.resolve('.'));

    // PHP strings for exec task.
    var moodleroot = path.dirname(path.dirname(__dirname)),
        configfile = '',
        decachephp = '',
        dirrootopt = grunt.option('dirroot') || process.env.MOODLE_DIR || '';

    // Allow user to explicitly define Moodle root dir.
    if ('' !== dirrootopt) {
        moodleroot = path.resolve(dirrootopt);
    }

    configfile = path.join(moodleroot, 'config.php');

    var PWD = process.cwd(); // jshint ignore:line

    decachephp += 'define(\'CLI_SCRIPT\', true);';
    decachephp += 'require(\'' + configfile + '\');';
    decachephp += 'theme_reset_all_caches();';

    grunt.initConfig({
        exec: {
            decache: {
                cmd: 'php -r "' + decachephp + '"',
                callback: function(error, stdout, stderror) {
                    // exec will output error messages
                    // just add one to confirm success.
                    if (!error) {
                        grunt.log.writeln("Moodle theme cache reset.");
                    }
                }
            }
        },
        watch: {
            // Watch for any changes to AMD JS files and compile.
            files: ["amd/src/**/*.js"],
            tasks: ["amd"],
            options: {
                spawn: false,
                livereload: true
            }
        },
        jshint: {
            options: {jshintrc: moodleroot + '/.jshintrc'},
            files: ['**/amd/src/*.js']
        },
        uglify: {
            options: {
                preserveComments: 'some'
            },
            dynamic_mappings: {
                files: grunt.file.expandMapping(
                    ['**/src/*.js', '!**/node_modules/**'],
                    '',
                    {
                        cwd: PWD,
                        rename: function(destBase, destPath) {
                            destPath = destPath.replace('src', 'build');
                            destPath = destPath.replace('.js', '.min.js');
                            destPath = path.resolve(PWD, destPath);
                            return destPath;
                        }
                    }
                )
            }
        }
    });

    // Load contrib tasks.
    grunt.loadNpmTasks("grunt-contrib-watch");
    grunt.loadNpmTasks("grunt-exec");

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-jshint');

    // Register tasks.
    grunt.registerTask("default", ["amd"]);
    grunt.registerTask("decache", ["exec:decache"]);

    grunt.registerTask("amd", ["jshint", "uglify", "decache"]);
};
