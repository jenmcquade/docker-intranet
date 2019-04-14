FROM arm64v8/alpine:edge
MAINTAINER Jen McQuade <info@indiewebconsulting.com>

LABEL com.iwc.application="docker-samba" 
LABEL com.iwc.description="Samba services for your Windows friends" 

RUN apk --no-cache --no-progress upgrade && \
    apk --no-cache --no-progress add bash samba shadow tini && \
    addgroup -S samba && adduser -D -S -G samba samba && \
    mkdir -p /media/samba/public && \
    chmod 777 /media/samba/public && \
    chmod 644 /media/samba

COPY samba.sh /usr/bin/
COPY smb.conf /etc/samba/
EXPOSE 137/udp 138/udp 139 445

HEALTHCHECK --interval=60s --timeout=15s \
CMD smbclint -L '\\127.0.0.1\ -U '%' -m SMB3

VOLUME ["/etc","/var/cache/samba","/var/lib/samba","/run/samba","/var/log/samba","/media/samba/public"]

ENTRYPOINT ["/sbin/tini","--","/usr/bin/samba.sh"]

