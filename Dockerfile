# Docker build script for the Propel Criteria site
#
# @todo Add a healthcheck into this project

FROM alpine:3.8

# Install software
RUN apk update
RUN apk add php-apache2
# Stuff needed by the old version of Symfony
RUN apk add php-ctype php-session

# Prep Apache
RUN mkdir -p /run/apache2
RUN echo "ServerName localhost" > /etc/apache2/conf.d/server-name.conf
RUN echo "LoadModule rewrite_module modules/mod_rewrite.so" > /etc/apache2/conf.d/rewrite.conf
RUN printf "<Directory '/var/www/localhost/htdocs'>\nAllowOverride All\n</Directory>" >> /etc/apache2/conf.d/rewrite.conf

# Copy contents of a web dir
RUN rm -rf /var/www/localhost/htdocs
COPY web /var/www/localhost/htdocs

# Copy resources one level up from the docroot
COPY apps /var/www/localhost/apps
COPY config /var/www/localhost/config
COPY data /var/www/localhost/data
COPY lib /var/www/localhost/lib
COPY plugins /var/www/localhost/plugins

EXPOSE 80

# Set up Symfony cache
RUN mkdir /var/www/localhost/cache
RUN chmod 777 /var/www/localhost/cache

# The healthcheck is used by the Routing Mesh, during a rolling update, to understand
# when to avoid a container that is not ready to receive HTTP traffic.
#HEALTHCHECK --interval=5s --timeout=5s --start-period=2s --retries=5 \
#    CMD wget -qO- http://localhost/health.php > /dev/null || exit 1

# Start the web server
CMD ["/usr/sbin/httpd", "-DFOREGROUND"]

