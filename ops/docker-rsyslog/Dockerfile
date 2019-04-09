FROM alpine:edge
MAINTAINER Jen McQuade <info@indiewebconsulting.com>

LABEL com.iwc.application="docker-rsyslog" 
LABEL com.iwc.description="All of your log r belong to us." 

RUN apk update ; apk add --no-cache rsyslog

# webproc release settings
ENV WEBPROC_VERSION 0.2.2
ENV WEBPROC_URL https://github.com/jpillora/webproc/releases/download/$WEBPROC_VERSION/webproc_linux_amd64.gz
# fetch webproc binary
RUN apk update \
        && apk --no-cache add dnsmasq \
        && apk add --no-cache --virtual .build-deps curl \
        && curl -sL $WEBPROC_URL | gzip -d - > /usr/local/bin/webproc \
        && chmod +x /usr/local/bin/webproc \
        && apk del .build-deps

COPY ./rsyslogd.conf /etc/rsyslogd.conf
COPY ./webproc-rsyslog.toml /config/webproc/webproc-rsyslog.toml

EXPOSE 514/tcp 514/udp 8080/tcp 8090/tcp
ENTRYPOINT ["webproc","/config/webproc/webproc-rsyslog.toml"]

