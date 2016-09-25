var https = require('https');
var fs = require('fs');
var app = require('./app');

// SSL Configuration
var ca_names = ['EECCRCA', 'ESTEID2011', 'ESTEID2015'];
var options = {
    key: fs.readFileSync('/eid/server.key'),
    cert: fs.readFileSync('/eid/server.crt'),
    ca: ca_names.map(function(n) { return fs.readFileSync('/eid/ca/' + n + '.crt'); }),
    //crl: ca_names.map(function(n) { return fs.readFileSync('/eid/ca/' + n + '.crl'); }),
    requestCert: false,
    rejectUnauthorized: false
};

var server = https.createServer(options, app);
server.listen(process.argv[2] || 443);

