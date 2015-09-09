FROM ubuntu:14.04

ENV DOMAIN_NAME=my.domain

RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y apache2 mailutils libapache2-mod-php5 wget

# Generate a self-signed sertificate for the server

RUN openssl genrsa -out /etc/ssl/private/server.key 2048 && \
    chown root.ssl-cert /etc/ssl/private/server.key && \
    chmod 640 /etc/ssl/private/server.key && \
    \
    openssl req -new -key /etc/ssl/private/server.key -out server.csr -subj "/C=EE/ST=Harjumaa/L=Tallinn/O=Test/OU=Test Department/CN=$DOMAIN_NAME" && \
    mkdir /etc/ssl/eid && \
    openssl x509 -req -days 365 -in server.csr -signkey /etc/ssl/private/server.key -out /etc/ssl/eid/server.crt && \
    rm server.csr

# Obtain CA certificates
RUN wget http://sk.ee/upload/files/JUUR-SK.PEM.cer && \
    wget http://sk.ee/upload/files/EECCRCA.pem.cer && \
    wget http://sk.ee/upload/files/ESTEID-SK%202007.PEM.cer && \
    wget http://sk.ee/upload/files/ESTEID-SK%202011.pem.cer && \
    cat JUUR-SK.PEM.cer EECCRCA.pem.cer ESTEID-SK\ 2007.PEM.cer ESTEID-SK\ 2011.pem.cer > /etc/ssl/eid/ca.crt && \
    rm JUUR-SK.PEM.cer EECCRCA.pem.cer ESTEID-SK\ 2007.PEM.cer ESTEID-SK\ 2011.pem.cer

# Download CRLs
RUN mkdir /etc/ssl/eid/crl
ADD ./renew_crl.sh /etc/ssl/eid/crl/
RUN bash /etc/ssl/eid/crl/renew_crl.sh 1

# Set apache configuration
RUN a2enmod ssl && a2dissite 000-default
RUN a2enmod proxy && a2enmod proxy_http && a2enmod headers
ADD ./ssl-site.conf /etc/apache2/sites-enabled/

# --- Add python and the app ---
RUN DEBIAN_FRONTEND=noninteractive apt-get install -y python python-flask gunicorn
ADD ./eid-python-sample /eid-python-sample
ADD ./start.sh /

# Image interface
EXPOSE 443
CMD ["/bin/bash", "/start.sh"]

