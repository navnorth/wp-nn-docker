FROM mysql:5.7
COPY data/current.sql /docker-entrypoint-initdb.d
