FROM ubuntu:14.04

ENV DOMAIN_NAME=my.domain

RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y tomcat7 default-jdk openssl wget

# Generate a self-signed sertificate for the server

RUN mkdir /eid && cd /eid && \
    keytool -genkeypair -alias serverkey -keyalg RSA -keysize 2048 -dname "CN=$DOMAIN_NAME,OU=Test Department,O=Test,L=Tallinn,ST=Harjumaa,C=EE" -keypass 123456 -storepass 123456 -keystore server.jks

# Download CA certificates & convert to the Java keystore format
RUN mkdir /eid/ca && cd /eid/ca && \
    wget http://sk.ee/upload/files/JUUR-SK.PEM.cer -O JUUR.crt && \
    wget http://sk.ee/upload/files/EECCRCA.pem.cer -O EECCRCA.crt && \
    wget http://sk.ee/upload/files/ESTEID-SK%202007.PEM.cer -O ESTEID2007.crt && \
    wget http://sk.ee/upload/files/ESTEID-SK%202011.pem.cer -O ESTEID2011.crt && \
    keytool -import -noprompt -file JUUR.crt -alias JUUR -keystore ca.jks -storepass '123456' && \
    keytool -import -noprompt -file EECCRCA.crt -alias EECCRCA -keystore ca.jks -storepass '123456' && \
    keytool -import -noprompt -file ESTEID2007.crt -alias ESTEID2007 -keystore ca.jks -storepass '123456' && \
    keytool -import -noprompt -file ESTEID2011.crt -alias ESTEID2011 -keystore ca.jks -storepass '123456'

# Configure Tomcat
ADD ./server.xml /var/lib/tomcat7/conf/
RUN echo "AUTHBIND=yes" >> /etc/default/tomcat7 && \
    touch /etc/authbind/byport/443 && \
    chmod 500 /etc/authbind/byport/443 && \
    chown tomcat7 /etc/authbind/byport/443

# Compile application
RUN mkdir /eid-java-app
ADD ./app /eid-java-app/app
ADD ./EidSample.java /eid-java-app/
RUN cd /eid-java-app && \
    mkdir -p app/WEB-INF/classes && \
    javac -cp /usr/share/tomcat7/lib/servlet-api.jar:/usr/lib/jvm/java-7-openjdk-amd64/jre/lib/jsse.jar -d app/WEB-INF/classes EidSample.java && \
    rm -rf /var/lib/tomcat7/webapps/ROOT && \
    cp -r ./app /var/lib/tomcat7/webapps/ROOT

CMD service tomcat7 start ; tail -f /var/log/tomcat7/catalina.out
 
