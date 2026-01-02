# Integration Tests

This directory contains integration tests that require a local database.

## Running Tests

The tests use a local MariaDB database running in Docker with test-only credentials.

### Setup

1. Start the test database:
   ```bash
   ./bin/tests-up.sh
   ```

2. Run the tests:
   ```bash
   composer test
   ```

3. Stop the test database when done:
   ```bash
   ./bin/tests-down.sh
   ```

## Database Credentials

The `dbCredentials.json` file contains credentials for the **local test database only**. These credentials:
- Only work with the local Docker container (not any production system)
- Connect to localhost on port 3999
- Are safe to commit to version control
- Match the configuration in `Dockerfile` and `setup.sql`

The test database runs in an isolated Docker container and is only accessible on your local machine.
