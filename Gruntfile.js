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
        js: 'public/assets/js/',
        vendor: 'public/assets/vendor/'
      },

      // Production where Grunt output the files      
      css: 'public/assets/css/',
      js: 'public/assets/js/'

    },

    // Task configuration
    concat: {  
      //...
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
      //...
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
    }  
});

  // Plugin loading
  // grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-less');
  // grunt.loadNpmTasks('grunt-contrib-uglify');
  // grunt.loadNpmTasks('grunt-phpunit');

  // Task definition
  grunt.registerTask('default', ['watch']);

};