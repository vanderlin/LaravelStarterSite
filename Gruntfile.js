//Gruntfile
module.exports = function(grunt) {

//Initializing the configuration object
grunt.initConfig({

    // Paths variables
    paths: {


      // Development where put LESS files, etc
      assets: {
        less: 'public/assets/less/',
        css: 'public/assets/css/',
        js: 'public/assets/js/src/',
        vendor: 'public/assets/vendor/'
      },

      // Production where Grunt output the files      
      css: 'public/assets/css/',
      js: 'public/assets/js/'

    },

    // Task configuration
    concat: {  
      options: {
        separator: ';',
      },
      js_frontend: {
        src: [
          '<%= paths.assets.vendor %>jquery/dist/jquery.js',
          '<%= paths.assets.vendor %>jquery-ui/jquery-ui.js',
          '<%= paths.assets.vendor %>bootstrap/dist/js/bootstrap.js',
          '<%= paths.assets.js %>frontend.js'
        ],
        dest: '<%= paths.js %>frontend.js',
      },
      js_backend: {
        src: [
          '<%= paths.assets.vendor %>jquery/dist/jquery.js',
          '<%= paths.assets.vendor %>jquery-ui/jquery-ui.js',
          '<%= paths.assets.vendor %>bootstrap/dist/js/bootstrap.js',
          '<%= paths.assets.js %>backend.js'
        ],
        dest: '<%= paths.js %>backend.js',
      }
    },  
    less: {
        production: {
          options: {
            paths: ["<%= paths.css %>"],
            cleancss: true,
            modifyVars: {
            }
          },
          files: {
            "<%= paths.css %>frontend/frontend.css": "<%= paths.assets.less %>frontend.less",
            "<%= paths.css %>backend/backend.css": "<%= paths.assets.less %>backend.less"
          }
        }
    },  
    uglify: {
      options: {
        mangle: false  // Use if you want the names of your functions and variables unchanged
      },
      frontend: {
        files: {
          '<%= paths.js %>frontend.min.js': '<%= paths.js %>frontend.js',
        }
      },
      backend: {
        files: {
          '<%= paths.js %>backend.min.js': '<%= paths.js %>backend.js',
        }
      },
    },  
    phpunit: {
      //...
    },  
    watch: {
      less: {
        files: ['<%= paths.assets.less %>*.less'],  //watched files
        tasks: ['less'],                            //tasks to run
        options: {
          livereload: true                          //reloads the browser
        }
      },
      js_frontend: {
        files: [
          //watched files
          '<%= paths.assets.vendor %>jquery-ui/jquery-ui.js',
          '<%= paths.assets.vendor %>jquery/jquery.js',
          '<%= paths.assets.vendor %>bootstrap/dist/js/bootstrap.js',
          '<%= paths.assets.js %>frontend.js'
          ], 
        tasks: ['concat:js_frontend', 'uglify:frontend'],
        options: {
          livereload: true                        //reloads the browser
        }
      },
      js_backend: {
        files: [
          //watched files
          '<%= paths.assets.vendor %>jquery-ui/jquery-ui.js',
          '<%= paths.assets.vendor %>jquery/jquery.js',
          '<%= paths.assets.vendor %>bootstrap/dist/js/bootstrap.js',
          '<%= paths.assets.js %>backend.js'
        ],
        tasks: ['concat:js_backend', 'uglify:backend'],   
        options: {
          livereload: true                        //reloads the browser
        }
      }
      
    }  
});

  // Plugin loading
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-uglify');

  // Task definition
  grunt.registerTask('default', ['watch']);
  grunt.registerTask('setup', ['less', 'concat:js_frontend', 'concat:js_backend', 'uglify:frontend', 'uglify:backend']);

};