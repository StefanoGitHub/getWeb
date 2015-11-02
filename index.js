//index.js

var hapi = require("hapi");

var server = new hapi.Server();
server.connection({ port: 8080 });

server.start(function(){
    console.log('Server running!')
    //console.info(server.info);
});

server.views({
    engines: {
        html: require('handlebars')
    },
    path: "./"
});

var get_blog = require('./get_blog');

server.route([
    //localhost:8000
    { method: "GET", path: "/get_blog", handler: get_blog }
]);

