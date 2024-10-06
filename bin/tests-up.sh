script_dir=$(dirname "$0")
container_name="test-repo-db"

# Check if the container already exists
if [ "$(docker ps -aq -f name=$container_name)" ]; then
    # Check if the container is running
    if [ "$(docker ps -q -f name=$container_name)" ]; then
        echo "Container $container_name is already running."
    else
        echo "Starting existing container $container_name."
        docker start $container_name
    fi
else
    echo "Building and running new container $container_name."
    docker build -t $container_name "$script_dir/.."
    container_id=$(docker run -d -p 3999:3306 --name $container_name $container_name)
    docker wait "$container_id"
fi

echo "Container up"