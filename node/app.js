
/**
 * Module dependencies.
 */

var express = require('express')
  , http = require('http')
  , path = require('path')
  , request = require('request')
  , Sequelize = require('sequelize');

var sequelize = new Sequelize('sample_app', 'username', 'password', {
  host: 'localhost',
  dialect: 'sqlite',

  pool: {
    max: 5,
    min: 0,
    idle: 10000
  },

  storage: './sample_app.sqlite'
});

var User = sequelize.define('user', {
  id: {
    type: Sequelize.STRING,
  },
  email: {
    type: Sequelize.STRING,
  },
  lastLogout: {
    type: Sequelize.DATE,
  },
});

var app = express();

app.configure(function(){
  app.set('port', process.env.PORT || 4000);
  app.set('views', __dirname + '/views');
  app.set('view engine', 'ejs');
  app.use(express.favicon());
  app.use(express.logger('dev'));
  app.use(express.bodyParser());
  app.use(express.methodOverride());
  app.use(express.cookieParser('your secret here'));
  app.use(express.session());
});

app.configure('development', function() {
  app.use(express.errorHandler());
});

var APP_ID = '4f4baa300eae6a7532cc60d06b49e0b9',
    APP_SECRET = 'd0d0ba5ef23dc134305125627c45677c';

app.use(function(req, res, next) {
  if (req.session.user == undefined) { return next(); }

  User.find({where: {id: req.session.user.id}}).then(function(user) {
    if (user.lastLogout == null || user.lastLogout < req.session.user.login) {
      next();
    } else {
      req.session.destroy();
      res.redirect('/');
    }
  })
});

app.get('/', function(req, res) {
  var user = (req.session.user || false)
  res.render('index', {user: user, email: user['email']});
});

app.get('/login', function(req, res) {
  var code = req.param('code');
  var url = 'https://clef.io/api/v1/authorize';
  var form = {app_id:APP_ID, app_secret:APP_SECRET, code:code};

  request.post({url:url, form:form}, function(error, response, body) {
    var token = JSON.parse(body)['access_token'];
    request.get(
      'https://clef.io/api/v1/info?access_token=' + token,
      function(error, response, body) {
        var user = JSON.parse(body)['info'];
        req.session.user = user;

        // Fetch user's data / Check if they exist
        User.find({where: {id: user.id}}).then(function(userData) {
          if (userData == null) {
            // Create user.
            User.create({id: user.id, email: user.email}).then(function() {
              req.session.user.login = Date.now();
              res.redirect('/');
            });
          } else {
            req.session.user.login = Date.now();
            res.redirect('/');
          }
        });
      });
  });
});

app.post('/logout', function(req, res) {
  var token = req.param('logout_token');
  var url = 'https://clef.io/api/v1/logout';
  var form = {app_id:APP_ID, app_secret:APP_SECRET, logout_token: token};

  request.post({url: url, form:form}, function(err, response, body) {
    var body = JSON.parse(body);

    if (body['success']) {
      User.find({where: {id: body.clef_id}}).then(function(user) {
        user.updateAttributes({
          lastLogout: Date.now()
        }).then(function () {
          res.send('bye');
        });
      });
    } else {
      console.log(body['error']);
      res.send('bye');
    }
  });

})

sequelize.sync().then(function () {
  http.createServer(app).listen(app.get('port'), function() {
    console.log("Express server listening on port " + app.get('port'));
  });
})
