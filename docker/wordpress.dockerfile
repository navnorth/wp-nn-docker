FROM wordpress:4.9
RUN apt-get update
RUN apt-get install -y nano vim mysql-client
