DROP USER IF EXISTS 'testuser'@'%';
CREATE USER 'testuser'@'%' IDENTIFIED BY 'testpassword';
GRANT ALL PRIVILEGES ON testing.* TO 'testuser'@'%';
FLUSH PRIVILEGES;