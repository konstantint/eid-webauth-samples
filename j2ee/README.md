# eID Web Authentication: Sample J2EE app

Most decent J2EE containers support SSL client authentication, however the default choice there is to either use client certificates for the 
whole site or not at all. Presumably, one *may* find a way to have the server request certificates for parts of the site only, however solving this puzzle required more time than the author of the document had available. If you know how to do it, feel free to contribute. [Here](http://stackoverflow.com/a/17131341) is a step in the correct direction, what is left is making the server let any authenticated user in without having to list particular certificates.

This directory contains an example of a Tomcat configuration, which forces SSL client certificate authentication for the whole site.

To build the docker container:

>     docker build -t eid-j2ee .

To run:

>     docker run -p 8001:443 -d eid-j2ee

You can now access the server via `https://localhost:8001/`.

Neither CRL nor OCSP support is implemented in the example.

