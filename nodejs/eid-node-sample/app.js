// Sample Express-based App
var express = require('express');
var session = require('express-session');

var app = express();

// Template engine
app.set('view engine', 'jade');
app.set('views', './views');

// Cookie sessions
app.use(session({
    secret: 'super secret server key',
    cookie: { secure: true },
    resave: false,
    saveUninitialized: false
}));

app.get('/', function(req, res) {
    var userName = req.session.user || 'anonymous';
    var flash = req.session.flash;
    if (req.session.flash) delete req.session.flash;
    res.render('index', {user: userName, flash: flash});
});

app.get('/login', function(req, res) {
    req.connection.renegotiate({
        requestCert: true,
        rejectUnauthorized: true
    },
    function(err) {
        if (!err) {
            var cert = req.connection.getPeerCertificate();
            if (cert && cert.subject !== undefined) req.session.user = cert.subject.CN;
            res.redirect("/");
        }
        else {
            console.log(err.message);
            res.status(401).send("Authentication failure!");
        }
    });
});

app.get('/logout', function(req, res) {
    delete req.session.user;
    req.session.flash = 'To logout completely you need to restart the browser (and sometimes take your card out as well)';
    res.redirect("/");
});

module.exports = app;

