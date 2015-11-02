//get_blog.js

var get_blog = function(req, reply) {

    var request = require('request'); //library for downloading web pages by URL
    var cheerio = require('cheerio'); //jQuery implementation for the server
    var async = require("async"); //async execution

    var rootPage = "http://linus.blog.deejay.it";

    var posts_arr = [];

/*
    var months = [
        'Gennaio', 'Febbraio', 'Marzo',
        'Aprile', 'Maggio', 'Giugno',
        'Luglio', 'Agosto', 'Settembre',
        'Ottobre', 'Novembre', 'Dicembre'
    ];
*/

    var year = 2015;
    var months = [ 1 ]; //, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 ];
    var days = [ 1, 2, 3, 4, 5 ]; //, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16,
        //17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31 ];

    //process each month
    async.eachSeries(months, function(month, cb_month) {
        var m = (month > 9) ? month : ('0' + month);
        //process each day
        async.eachSeries(days, function(day, cb_day) {
            var d = (day > 9) ? day : ('0' + day);
            var archivePage = rootPage + '/' + year + '/' + m + '/' + d + '/';
            //process the archivePage to get the link to the post
            request(archivePage, function (error, response, body) {
                if (error) {
                    console.error(error);
                }
                console.log('archivePage', archivePage);
                //load the page into a cheerio obj
                var $ = cheerio.load(body);
                if ($('div.title h3 a').attr('href')) {
                    console.log($('div.title h3 a').attr('href'));
                    //var postPage = $('div.title h3 a').attr('href'); // "http://linus.blog.deejay.it/2015/01/05/ ... "
                    //request(postPage, function (err, response, body) {
                    //    console.log('postPage', postPage);
                    //    if (error) {
                    //        console.error(err);
                    //    }
                    //    var $ = cheerio.load(body);
                    //    //console.log($('article').html());
                    //    if ($('div.article-wrapper article').html()) {
                    //        var article = $('div.article-wrapper article').html();
                    //        articles_arr.push(article);
                    //        console.log(articles_arr);
                    //        console.log('article: OK');
                    //
                    //    }
                    //    cb_day();
                    //});
                    var postPage = $('div.title h3 a').attr('href'); // "http://linus.blog.deejay.it/2015/01/05/ ... "
                    posts_arr.push(postPage);
                    console.log(posts_arr);
                    console.log('article: OK');
                    cb_day();
                } else {
                    cb_day();
                }
            });
        });
        cb_month();
    }, function() {
        //once got all the links, show them in the html page
        reply.view("index.html", {
            posts: posts_arr
            //articles: [1,2,3,4,5]
        });
    });

};

module.exports = get_blog;