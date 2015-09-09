FROM ubuntu:14.04

ENV DOMAIN_NAME=my.domain

RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y nginx openssl wget mailutils php5-fpm

# Generate a self-signed sertificate for the server

RUN mkdir /etc/nginx/eid && \
    openssl genrsa -out /etc/nginx/eid/server.key 2048 && \
    openssl req -new -key /etc/nginx/eid/server.key -out server.csr -subj "/C=EE/ST=Harjumaa/L=Tallinn/O=Test/OU=Test Department/CN=$DOMAIN_NAME" && \
    openssl x509 -req -days 365 -in server.csr -signkey /etc/nginx/eid/server.key -out /etc/nginx/eid/server.crt && \
    rm server.csr

RUN wget http://sk.ee/upload/files/JUUR-SK.PEM.cer && \
    wget http://sk.ee/upload/files/EECCRCA.pem.cer && \
    wget http://sk.ee/upload/files/ESTEID-SK%202007.PEM.cer && \
    wget http://sk.ee/upload/files/ESTEID-SK%202011.pem.cer && \
    cat JUUR-SK.PEM.cer EECCRCA.pem.cer ESTEID-SK\ 2007.PEM.cer ESTEID-SK\ 2011.pem.cer > /etc/nginx/eid/ca.crt && \
    rm JUUR-SK.PEM.cer EECCRCA.pem.cer ESTEID-SK\ 2007.PEM.cer ESTEID-SK\ 2011.pem.cer

# Download CRLs
RUN mkdir /etc/nginx/eid/crl
ADD ./renew_crl_nginx.sh /etc/nginx/eid/crl/
RUN bash /etc/nginx/eid/crl/renew_crl_nginx.sh 1

# Set nginx configuration
RUN rm /etc/nginx/sites-enabled/default && \
    echo "\ndaemon off;" >> /etc/nginx/nginx.conf
ADD ./ssl-site /etc/nginx/sites-enabled/
ADD ./start.sh /


# Image interface
EXPOSE 443
CMD ["/bin/bash", "/start.sh"]

