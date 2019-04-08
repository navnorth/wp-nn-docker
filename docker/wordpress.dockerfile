FROM wordpress:5.1
RUN apt-get update
RUN apt-get install -y nano vim mysql-client
