# eID Web Authentication Samples

[Estonian ID card](http://eid.eesti.ee/index.php/A_Short_Introduction_to_eID) is the official identification 
document in Estonia. It is a smart card, and as such can be used to authenticate to web services.
This repository contains example implementations of ID-card based authentication for various platforms.

Note that the official documentation [has](http://eid.eesti.ee/index.php/EID_application_guide) [some](https://eid.eesti.ee/index.php/General_information_for_developers) [useful](http://eid.eesti.ee/index.php/Authenticating_in_web_applications) [materials](https://eid.eesti.ee/index.php/Sample_applications) 
on this topic. You are free to refer to them if the documentation here seems insufficient.

## Preliminaries

### Estonian ID card
The ID card (EstEID) is an ISO/IEC 7816 smart card. As such it contains a chip with 
two private keys (one meant for authentication and one for signing) and two corresponding
certificates. A very thorough technical specification of the card is given [here](http://www.id.ee/index.php?id=35772) (reading it
is optional if you only need to set up web-based authentication, however).

### SSL/TLS
[SSL](https://en.wikipedia.org/wiki/Transport_Layer_Security) (also known as TLS or SSL/TLS) is a network communication protocol, 
which uses [asymmetric cryptography](https://en.wikipedia.org/wiki/Public-key_cryptography) to establish a secure channel. 
It is familiar to most Internet users in the form of [HTTPS](https://en.wikipedia.org/wiki/HTTPS) ("HTTP over SSL"),
which is a protocol used to securely serve most privacy-sensitive websites in the Internet nowadays.

In its most familiar form, connection security in SSL is established by authenticating the server to the client. For that the server 
sends to the client its public key. This key can be used by the client to encrypt its messages to the server and proceed
with secure communication. In order to guarantee to the client that the provided public key indeed belongs to the server (rather than
to an imposter), the public key is accompanied by a *certificate*. A certificate is a document, which claims "this public key belongs to this domain"
and which is signed using a private key belonging to some third party. This party is known as a *Certification Authority* (CA) and it is trusted by the client to *not* issue false claims. Sometimes the public key of the CA is further signed by a higher-level CA, which may in turn be signed by a further CA, all the way to the *Root CA*, which is trusted unconditionally. A list of such trusted Root CA-s is predefined in the client's browser.
Consequently, any key used in SSL communications must be associated with a chain of signatures *(certificate chain)*, linking it to a trusted Root CA. Whenever this is not the case (i.e. the root CA is not trusted), the browser will issue warnings about insecure connections. 

### SSL client certificate authentication
The SSL protocol allows to use the same mechanism to authenticate the client. That is, the server may request the client to prove that he owns some public key along with a certificate chain linking this key to some Root CA *trusted by the server*. If this succeeds, the server may use the information in the certificate to identify the user. This process of *[client certificate authentication](https://en.wikipedia.org/wiki/Transport_Layer_Security#Client-authenticated_TLS_handshake)* forms the basis for using an ID card in web authentication. Namely, in order for such authentication to happen:

* The server must establish an SSL connection (which presumes the server can present a certificate signed by a RootCA, trusted by the client).
* The server must request the client to send his own certificate.
* The client's computer must have a connected card reader along with the necessary drivers which would respond to this request by providing the certificate from the smart card.
* The server must trust the RootCA which issued the client's certificate.

## Configuring ID-card based authentication

ID-card authentication is nothing more than requesting (on the server side), providing (on the client side) and accepting (on the server side again) the corresponding certificate from the smart card during the SSL handshake phase. As SSL is a transport-level protocol, in most web application platforms the details of this handshake are usually not under control of the web application and have to be configured at the server level.

In the following we describe the steps needed to set up the server along with example applications in PHP, Python and NodeJS. In addition we provide example setups in the subdirectories of this repository packaged as docker containers. See the README in the corresponding directories.

Note that the examples and sample code are only meant as initial guidelines. Do not copy those blindly and make sure you understand all the security implications of particular set up options for your project. In particular, please take time to read through the following paper before pushing your application into production:

 * [Practical Issues with TLS Client Certificate Authentication](https://eprint.iacr.org/2013/538.pdf). Arnis Parsovs. 2013.

 
### Creating a server certificate

No matter which platform you choose, you will need to create a server keypair along with a certificate, proving the ownership of your domain name. Note that for the certificate to be trusted by the client's browser (so that he would be able to access your site without seeing the security warnings), you must obtain one from a certification authority that is trusted by the browsers. Unfortunately, most such CAs provide their services at a price. Estonia's own Sertifitseerimiskeskus [offers such a service](https://www.sk.ee/en/services/ssl-certificates). Some cheaper possibilities could be explored [here](https://www.namecheap.com/security/ssl-certificates/domain-validation.aspx). For most start-up and personal projects the best option is to obtain a certificate for free from [StartSSL](http://www.startssl.com/). Finally, at development time you may resort to using a *self-signed* certificate, which is easy to generate without the need to contact any external parties.

The steps needed to be taken to create a certificate signed using a trusted CA service are usually well documented on the site of the corresponding CA. If you need a self-signed certificate instead, you can generate it as follows (change "`yourdomainname.com`" to the actual domain name):

>     openssl genrsa -out /etc/ssl/private/server.key 2048
    openssl req -new -key /etc/ssl/private/server.key -out server.csr -subj "/C=EE/ST=Harjumaa/L=Tallinn/O=Test/OU=Test Department/CN=yourdomainname.com"
    openssl x509 -req -days 365 -in server.csr -signkey /etc/ssl/private/server.key -out /etc/ssl/eid/server.crt
    rm server.csr

No matter which way you go, you will end up with two files: `server.key` (the private key) and `server.crt` (the public key along with the certificate). Those files will be necessary in further steps. If you feel confused about the process of certificate generation, the Internet provides multiple well-written guides. One nice example is:

 * [OpenSSL Essentials: Working with SSL Certificates, Private Keys and CSRs](https://www.digitalocean.com/community/tutorials/openssl-essentials-working-with-ssl-certificates-private-keys-and-csrs). Mitchell Anicas. DigitalOcean community tutorials.
 
### Enabling SSL in the server

Once you have the `server.key` and `server.crt`, you need to configure the server to use those in SSL communications. 
In Apache you would expect to start with a configuration block like the following:

    <IfModule mod_ssl.c>
        <VirtualHost _default_:443>
            DocumentRoot /var/www/html

            SSLEngine on

            SSLCertificateFile    /etc/ssl/eid/server.crt
            SSLCertificateKeyFile /etc/ssl/private/server.key
        </VirtualHost>
    </IfModule>

The configuration concepts for other servers are analogous. It is worth noting, however, that at the current moment Apache seems to be the best option for configuring ID-card-based client authentication. Nginx has [some issues](https://trac.nginx.org/nginx/ticket/317) which make such a configuration very inconvenient. See the `README` in the `nginx` subdirectory.
    
### Enabling client certificate authentication

In order for the server to accept Estonian ID card certificates you need to configure it to trust the [corresponding root CA certificates](https://sk.ee/en/repository/certs/). Those must be first downloaded and concatenated together into a single file:

>     wget http://sk.ee/upload/files/JUUR-SK.PEM.cer
    wget http://sk.ee/upload/files/EECCRCA.pem.cer
    wget http://sk.ee/upload/files/ESTEID-SK%202007.PEM.cer
    wget http://sk.ee/upload/files/ESTEID-SK%202011.pem.cer
    cat JUUR-SK.PEM.cer EECCRCA.pem.cer ESTEID-SK\ 2007.PEM.cer ESTEID-SK\ 2011.pem.cer > ca.crt
    rm JUUR-SK.PEM.cer EECCRCA.pem.cer ESTEID-SK\ 2007.PEM.cer ESTEID-SK\ 2011.pem.cer
    # NB: this list of commands will become outdated soon.
    # Review https://sk.ee/en/repository/certs/ to make sure you download all the necessary certificates

The result is a `ca.crt` file, containing all eID root certificates concatenated. For the server to use this file it must be added to the host configuration block:

    SSLCACertificateFile /etc/ssl/eid/ca.crt
          
Next you should configure which accesses to the server should trigger a certificate request, and whether presenting a valid certificate is required for the connection to proceed. For example:

    <Location />
        SSLVerifyClient optional
        SSLVerifyDepth 3
    </Location>

The data of the provided certificate will be accessible in your PHP or CGI scripts via the environment variables `SSL_CLIENT_S_DN` and `SSL_CLIENT_VERIFY` if you enable the `+StdEnvVars` option as follows:

    <FilesMatch "\.(cgi|shtml|phtml|php)$">
        SSLOptions +StdEnvVars
    </FilesMatch>
    

### Dealing with certificate revocation

Issued certificates may be revoked before their official validity end date -- this happens when an ID card gets lost or stolen. Consequently, every time a client authenticates with a certificate, a separate check needs to be done to ensure the certificate has not been revoked. There are multiple ways of implementing such checks. 

#### CRLs

The "simple" way is based on *certificate revocation lists* (CRLs), that are regularly published by the certification authorities. To enable certificate checking against CRLs you need to first download them to a single directory:

>     wget http://www.sk.ee/crls/esteid/esteid2007.crl
    wget http://www.sk.ee/crls/juur/crl.crl
    wget http://www.sk.ee/crls/eeccrca/eeccrca.crl
    wget http://www.sk.ee/repository/crls/esteid2011.crl
    
Convert them to PEM format:

>     openssl crl -in esteid2007.crl -out esteid2007.crl -inform DER
    openssl crl -in crl.crl -out crl.crl -inform DER
    openssl crl -in eeccrca.crl -out eeccrca.crl -inform DER
    openssl crl -in esteid2011.crl -out esteid2011.crl -inform DER
    
Create symlinks of the form `<file hash>.r0 --> file.crl` (alternatively, you could concatenate them all into a single file):

>     ln -s crl.crl `openssl crl -hash -noout -in crl.crl`.r0
    ln -s esteid2007.crl `openssl crl -hash -noout -in esteid2007.crl`.r0
    ln -s eeccrca.crl `openssl crl -hash -noout -in eeccrca.crl`.r0
    ln -s esteid2011.crl `openssl crl -hash -noout -in esteid2011.crl`.r0

Let your webserver know about the directory where the CRLs are stored (alternatively, you could use the `SSLCARevocationFile` directive pointing to concatenated certificates):

    SSLCARevocationPath /etc/ssl/eid/crl
    
Finally, and most importantly, create a script that would regularly re-download the new CRLs and reload the server. You need to configure this script to run automatically at frequent enough intervals (e.g. using `cron`). An example script `renew_crl.sh` is provided in the `apache` subdirectory of this project. It will need tuning to your taste. Note that **if your CRLs become outdated, Apache will deny connections completely**.

#### OCSP

CRLs are only updated at fixed intervals, hence in the time between a certificate revocation and the re-download of the updated CRL the revoked certificate will still be considered valid by the server, which is a security risk. To avoid this problem we may directly contact the CA *each time* a certificate is being presented to the server and check the current validity status of the certificate. Such a validity confirmation can be requested using the [Open Certificate Status Protocol (OCSP)](https://en.wikipedia.org/wiki/Online_Certificate_Status_Protocol) from the [OCSP service](https://www.sk.ee/en/services/validity-confirmation-services/auth-ocsp/) provided by the CA.

The benefit of this approach is apparent, as it makes all certificate validations up to date and removes the need to keep track of the CRLs. The main drawback is that the SK.ee OCSP service is not free and requires a contract. During development you may use a [test OCSP service](http://www.id.ee/index.php?id=37330) provided by the SK.

In theory, you should be able to enable OCSP by simply configuring the webserver as follows:

    SSLOCSPEnable on
    SSLOCSPDefaultResponder http://ocsp.sk.ee/_auth
    SSLOCSPOverrideResponder on

In practice, due to [this Apache bug](https://bz.apache.org/bugzilla/show_bug.cgi?id=46037) and the particularities of eID OCSP service, this will not work. Instead, you will need to implement OCSP checks at the application level. Starter code, illustrating how this could be done via openssl is provided in `apache/www/ocsp.php`.

### Login/logout

The SSL handshake is usually not under control of the application. Besides that, there are currently no standards, dictating how could the browser be forced to "forget" the authenticated user (although [some work is ongoing](http://html5.creation.net/webcrypto-api/)). Because of that, implementing convenient login/logout functionality in a cross-browser manner upon SSL client authentication [can be tricky](http://stackoverflow.com/questions/10487205/https-client-certificate-logout-relogin), if not impossible. This project does not provide a bulletproof solution. Indeed, most applications out there seem to ignore the problem whatsoever and are notorious for requiring browser (and sometimes even computer) restarts when you need to re-authenticate to them with a different ID card or when your first authentication attempt failed and you want to retry. Consider the example code in `apache/login`.


### Client setup

Obviously, for the authentication process to work the user must install appropriate drivers and have EstEID root certificates added to the trusted store of his browser(s). This would normally happen when the user installs [ID-software](https://installer.id.ee/). If you encounter issues with Firefox, [check this post](http://www.id.ee/?lang=en&id=34392).


## Sample applications

The remaining part of this project contains several sample applications which should help you better understand the concepts above. See the `README` files in the corresponding subdirectories for further guidance.

* `apache`: probably the most common option for ID-card authentication platform.
* `nginx`: Nginx does not allow to configure client authentication requirements on a per-directory basis (it is either the whole server or nothing). Because of that Nginx is not a viable choice for ID card authentication, but there is a sample for the curious none the less.
* `node`: NodeJS makes it possible to create a set up similar to the one with Apache, except for two differences. Firstly, CRL support in Node seems to be incompatible with what is necessary for ID cards (also, you could not reload CRLs without restarting the server). Thirdly, it is impossible to deny SSL verification once a handshake was established - this functionality is not crucial, but could have been used as a hack to enable "logout" for Chrome.
* `python`: the standard solution for Python webapps is to have an Apache frontend handle SSL authentication. 
* `j2ee`: J2EE containers seem to suffer from the same problem as Nginx -- they easily let you configure client certificates for the whole site. It should in principle be possible to create a more granular setup with only parts of the site requiring certificates, but this seems to be tricky.

## License

* **Code**: MIT. 
* **Text**: CC-BY-SA.
* **Copyright**: Konstantin Tretyakov, 2015.
