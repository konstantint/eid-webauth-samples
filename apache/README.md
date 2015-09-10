# eID Web Authentication: Sample Apache configuration

This directory provides an example of Apache configuration on Ubuntu along with some PHP scripts for testing its effects. 
The contents of the directory is as follows:

* `ssl-site.conf`: An example `VirtualHost` configuration block demonstrating the common SSL configuration examples.
* `renew_crl.sh`: An example script for renewing certificate revocation lists, that must be configured via `cron` to run regularly.
* `www/`: A set of PHP scripts useful for testing the corresponding settings. In particular, `ocsp.php` provides an example of application-level OCSP validation, `ldap.php` shows the use LDAP, and scripts under `login/*` demonstrate a possible way of implementing login/logout functionality.
* `Dockerfile` is a configuration file for [Docker](https://docker.com/) which illustrates the commands necessary to bootstrap and configure Apache on a bare Ubuntu box.

You can also use Docker to construct a reproducible environment to experiment on your own. For that first build the image:

>     docker build -t eid-apache .

then start the apache in a container built on this image (in this example we are exposing the HTTPS port from the container to host port `8001`):

>     docker run -p 8001:443 -v $PWD/www:/var/www -d eid-apache

You can now access the server via `https://localhost:8001/`.

Another convenient option is to start `bash` within the container first:

>     docker run -p 8001:443 -v $PWD/www:/var/www -it eid-apache bash

And from there on manually start Apache:

>     service apache2 start

This will let you play with configuration files, restarting apache when necessary, and fine tune the image to your liking. (*NB: Do not exit the bash shell of the container as this will destroy it along with any changes you might have done. Use Ctrl+P Ctrl+Q to detach from a docker container shell.*). 

To stop and destroy the container do something like

>     docker ps
>     docker stop <your container name>

Note that besides experimentation, you can use a similar Docker container approach to serve your project in production, in some situations it can be convenient.
