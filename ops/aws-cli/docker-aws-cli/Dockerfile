FROM python:alpine
MAINTAINER Jen McQuade <info@indiewebconsulting.com>

ARG CLI_VERSION=1.16.138

RUN apk -uv add --no-cache groff jq less \
&& pip3 install --no-cache-dir awscli==$CLI_VERSION

WORKDIR /aws

ENTRYPOINT tail -f /dev/null

CMD sh

