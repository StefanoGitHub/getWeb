//Gruntfile.js

//1. First step install global version -> $ (sudo) npm install grunt-cli -g
//2. Install local version (inside project folder) -> $ npm install grunt --save

module.exports = function(grunt) {
/*
    //custom task
    grunt.registerTask("hello", function() {
        console.log("Hello from Grunt!");
        //grunt.file.write("build/test.txt", "This file is written sync");
    });
*/

    //npm install grunt-contrib-watch --save
    grunt.loadNpmTasks("grunt-contrib-watch"); //to run: $ grunt watch
    //$ npm install hapi grunt-concurrent grunt-nodemon --save
    grunt.loadNpmTasks("grunt-concurrent"); //to run: $ grunt concurrent
    grunt.loadNpmTasks("grunt-nodemon"); //to run: $ grunt nodemon

    //config Grunt with an obj
    grunt.initConfig({
        //concurrent runs simultaneous tasks
        concurrent: {
            dev: {
                //tasks to be executed simultaneously
                tasks: ["nodemon", "watch"],
                options: { logConcurrentOutput: true } // logs error from both tasks
            }
        },
        nodemon: {
            //nodemon starts the server and restart it after every change
            dev: { script: "index.js" }
        },        
        //property obj for the watch task
        watch: {
            //watch performs defined tasks whenever changes are saved in matching files
            options: { livereload: true }, //option valid for all targets
            html: {
                //any html
                files: "*.html",
                //no tasks -> just live reload
                tasks: []
            }
        }
    });
    
    //default task(s), executed when run "$ grunt"
    grunt.registerTask("default", "concurrent");
    
};