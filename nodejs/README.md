# eID Web Authentication: Sample NodeJS app

NodeJS provides a `https` module, which supports client certificate authentication.
Moreover, it offers some low-level access to the connection, and using that it becomes possible to only request a certificate when the user
accesses a protected part of the site. For all this to work, however, you should use a decently recent version of Node (`0.10.x` did not work in our tests).

CRLs are supported by `https`, however we could not make eID CRLs to work. In addition, in order to update CRLs you need to restart the server which may be a problem in some cases. Hence, we would recommend implementing an OCSP-based solution.

This directory presents a simple NodeJS application in the `eid-node-sample` package. If you have a recent version of node installed,
you can run the app as usual. Modify the locations of the necessary keys and certificates in `server.js` and then run:

>     npm install
>     npm start

The example is also packaged as a docker container. To build it:

>     docker build -t eid-node .

To run:

>     docker run -p 8001:443 -d eid-node

You can now access the server via `https://localhost:8001/`.

Note that the CRL validation in this setup is left unconfigured, nor is there any OCSP validation implemented. Check the PHP example in the `apache` directory for guidance.
