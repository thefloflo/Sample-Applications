
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

var APP_ID = '8db34c6435ba25840ed2b1c2cc730d94',
    APP_SECRET = '6bc4b235c7b374e6113f0132caa95f84';

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
