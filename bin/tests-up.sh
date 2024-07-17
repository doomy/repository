docker build -t test-db .
container_id=$(docker run -d -p 3999:3306 --name test-db test-db)
docker wait "$container_id"
echo "container up"