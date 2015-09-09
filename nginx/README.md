# eID Web Authentication: Sample Nginx configuration

To spare you the time: **do not use Nginx** to set up ID-card authentication. Due to [this issue](https://trac.nginx.org/nginx/ticket/317) Nginx is only capable to enable server-wide client certificate authentication. Which means that configuring natural "login/logout" use cases becomes close to impossible:

* The user is prompted for his certificate the first time he visits any page of the server (not what many websites need).
* If the user fails to produce a valid certificate, he will either be completely denied access (not what many websites need), or will be allowed unauthenticated with no possibility to renegotiate and "log in" (apart from restarting the browser). This is, again, not what most people need.

In theory, you could set up Nginx-based ID-card login-logoff functionality, but for that you would need to move authentication onto a separate server (with a separate domain name *and* IP), and use some kind of single sign-on techniques to transfer authentication information between the "sign-on" server and your app. Even then the usability would suffer at places.

For the sake of completeness, though, this directory presents a simple Nginx configuration example (see `ssl-site` and `index.php`).
It is also packaged as a docker container. To build it:

>     docker build -t eid-nginx .

To run:

>     docker run -p 8001:443 -v $PWD/www:/var/www -d eid-nginx

You can now access the server via `https://localhost:8001/`.

Note that the CRL validation in this setup is left unconfigured as Nginx seemed to refuse doing it at any cost.

In any case, simply do not use Nginx for ID card logins.

