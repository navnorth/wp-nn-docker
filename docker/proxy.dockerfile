# need this to increase the upload size
RUN echo 'client_max_body_size 128M;' >> /etc/nginx/conf.d/uploads.conf;
