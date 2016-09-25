FROM ubuntu:14.04

ENV DOMAIN_NAME=my.domain

RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y apache2 mailutils libapache2-mod-php5 php5-ldap wget

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
RUN wget https://sk.ee/upload/files/EE_Certification_Centre_Root_CA.pem.crt && \
    wget https://sk.ee/upload/files/ESTEID-SK_2011.pem.crt && \
    wget https://sk.ee/upload/files/ESTEID-SK_2015.pem.crt && \
    cat EE_Certification_Centre_Root_CA.pem.crt ESTEID-SK_2011.pem.crt ESTEID-SK_2015.pem.crt > /etc/ssl/eid/ca.crt && \
    rm EE_Certification_Centre_Root_CA.pem.crt ESTEID-SK_2011.pem.crt ESTEID-SK_2015.pem.crt

# Download CRLs
RUN mkdir /etc/ssl/eid/crl
ADD ./renew_crl.sh /etc/ssl/eid/crl/
RUN bash /etc/ssl/eid/crl/renew_crl.sh 1

# Set apache configuration
RUN a2enmod ssl && a2dissite 000-default
ADD ./ssl-site.conf /etc/apache2/sites-enabled/


# Image interface
EXPOSE 443
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]

