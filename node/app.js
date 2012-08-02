
/**
 * Module dependencies.
 */

var express = require('express')
  , http = require('http')
  , path = require('path')
  , request = require('request');

var app = express();

app.configure(function(){
  app.set('port', process.env.PORT || 9292);
  app.set('views', __dirname + '/views');
  app.set('view engine', 'ejs');
  app.use(express.favicon());
  app.use(express.logger('dev'));
  app.use(express.bodyParser());
  app.use(express.methodOverride());
  app.use(express.cookieParser('your secret here'));
  app.use(express.session());
});

app.configure('development', function(){
  app.use(express.errorHandler());
});

var APP_ID = '722fb478f96d817aa0c434d2b256cf7c',
    APP_SECRET = '66958e82b7e36507872899232be1b1ca';

app.get('/', function(req, res) {
  var user = (req.session.user || false)
  res.render('index', {user: user, email: user['email']});
});

app.get('/login', function(req, res) {
  var code = req.param('code');
  var url = ('https://clef.io/api/authorize?code=' + code 
            + '&app_id=' + APP_ID
            + '&app_secret=' + APP_SECRET);
            
  request(url, function(error, response, body) {
    var token = JSON.parse(body)['access_token'];
    request('https://clef.io/api/info?access_token=' + token, function(error, response, body) {
      req.session.user = JSON.parse(body)['info'];
      res.redirect('/');
    });
  });
});

http.createServer(app).listen(app.get('port'), function(){
  console.log("Express server listening on port " + app.get('port'));
});
