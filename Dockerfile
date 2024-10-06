FROM mariadb:latest

# Environment variables
ENV MYSQL_ROOT_PASSWORD=root
ENV MYSQL_DATABASE=testing
ENV MYSQL_USER=testuser
ENV MYSQL_PASSWORD=testpassword

# Expose port 3999
EXPOSE 3999

# Add setup script
ADD setup.sql /docker-entrypoint-initdb.d/