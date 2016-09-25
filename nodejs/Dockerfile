FROM ubuntu:14.04

ENV DOMAIN_NAME=my.domain

RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y npm openssl wget build-essential libssl-dev

# Install NVM and the newer Node version
RUN rm /bin/sh && ln -s /bin/bash /bin/sh
ENV NVM_DIR /root/.nvm
ENV NODE_VERSION 4.0.0

# Install nvm with node and npm
RUN wget -O- https://raw.githubusercontent.com/creationix/nvm/v0.24.1/install.sh | bash \
    && source $NVM_DIR/nvm.sh \
    && nvm install $NODE_VERSION \
    && nvm alias default $NODE_VERSION \
    && nvm use default

# Generate a self-signed sertificate for the server

RUN mkdir /eid && \
    openssl genrsa -out /eid/server.key 2048 && \
    openssl req -new -key /eid/server.key -out server.csr -subj "/C=EE/ST=Harjumaa/L=Tallinn/O=Test/OU=Test Department/CN=$DOMAIN_NAME" && \
    openssl x509 -req -days 365 -in server.csr -signkey /eid/server.key -out /eid/server.crt && \
    rm server.csr

RUN mkdir /eid/ca && cd /eid/ca && \
    wget https://sk.ee/upload/files/EE_Certification_Centre_Root_CA.pem.crt -O EECCRCA.crt && \
    wget https://sk.ee/upload/files/ESTEID-SK_2011.pem.crt -O ESTEID2011.crt && \
    wget https://sk.ee/upload/files/ESTEID-SK_2015.pem.crt -O ESTEID2015.crt

# Download CRLs
ADD ./renew_crl_node.sh /eid/ca/
RUN bash /eid/ca/renew_crl_node.sh 1

# Initialize app
ADD ./eid-node-sample /eid-node-sample
RUN cd /eid-node-sample && npm install
ADD ./start.sh /

ENV PATH      $NVM_DIR/versions/node/v$NODE_VERSION/bin:$PATH

# Image interface
EXPOSE 443
CMD ["/bin/bash", "/start.sh"]


