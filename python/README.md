# eID Web Authentication: Sample Python app

Most Python web applications nowadays are communicating via the WSGI interface. Unfortunately there do not seem to be any WSGI containers that would support SSL client authentication properly. Hence, your best (if not only) option is to handle SSL with a separate frontend server. In other words, you should set up Apache and have it `ProxyPass` requests down to your WSGI server, setting the necessary environment variables. This directory presents an example of such a setup.

To build the docker container:

>     docker build -t eid-python .

To run:

>     docker run -p 8001:443 -d eid-python

You can now access the server via `https://localhost:8001/`.
