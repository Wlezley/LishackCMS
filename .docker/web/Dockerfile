FROM jakubboucek/lamp-devstack-php:8.4

ARG USER_ID=1000
ARG GROUP_ID=1000

RUN groupadd -g ${GROUP_ID} developer && \
    useradd -u ${USER_ID} -g developer -m -s /bin/bash developer

ENV APACHE_RUN_USER=developer
ENV APACHE_RUN_GROUP=developer

SHELL ["/bin/bash", "-o", "pipefail", "-c"]
