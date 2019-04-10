FROM nginx:alpine
LABEL maintainer="Jen McQuade <info@indiewebconsulting.com>"

VOLUME /etc/letsencrypt
EXPOSE 80
EXPOSE 443

# Do this apk stuff all in one RUN command to avoid creating large
# intermediate layers on non-squashable docker installs
RUN apk add --update --no-cache \
    certbot openssl
# Copy in scripts for certbot
COPY ./scripts/ /scripts
RUN chmod +x /scripts/*.sh

# Add /scripts/startup directory to source more startup scripts
RUN mkdir -p /scripts/startup

# Copy in default nginx configuration (which just forwards ACME requests to
# certbot, or redirects to HTTPS, but has no HTTPS configurations by default).
RUN rm -f /etc/nginx/conf.d/*
COPY nginx_conf.d/ /etc/nginx/conf.d/

# default variables for OpenSSL
ENV COUNTY "US"
ENV STATE "Washington"
ENV LOCATION "Tacoma"
ENV ORGANIZATION "Indie Web Consulting"
ENV ROOT_CN "Root"
ENV ISSUER_CN "Indie Web Consulting"
ENV PUBLIC_CN "*.example.com"
ENV ROOT_NAME "root"
ENV ISSUER_NAME "Indie Web Consulting"
ENV PUBLIC_NAME "public"
ENV RSA_KEY_NUMBITS "2048"
ENV DAYS "365"
ENV KEYSTORE_NAME "keystore"
ENV KEYSTORE_PASS "changeit"
ENV CERT_DIR "/etc/letsencrypt/live"

VOLUME /etc/letsencrypt/live

ENTRYPOINT [tail -f /dev/null]
CMD ["/bin/bash", "/scripts/entrypoint.sh"]
