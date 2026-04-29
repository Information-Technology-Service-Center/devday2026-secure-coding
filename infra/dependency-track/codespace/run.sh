#!/bin/bash

#!/bin/bash


# Start database and redis services first
echo "Starting database services..."
docker-compose up -d dojo-postgres dojo-valkey dtrack-postgres

# Wait for database to be ready
echo "Waiting for database to be ready..."
sleep 15

# Run DefectDojo migrations
echo "Running DefectDojo database migrations..."
docker run --rm --network codespace_default --entrypoint python \
  -e DD_DATABASE_URL="postgresql://defectdojo:defectdojo@codespace-dojo-postgres-1:5432/defectdojo" \
  -e DD_CELERY_BROKER_URL="redis://codespace-dojo-valkey-1:6379/0" \
  -e DD_SECRET_KEY="hhZCp@D28z!n@NED*yB!ROMt+WzsY*iq" \
  -e DD_CREDENTIAL_AES_256_KEY="&91a*agLqesc*0DJ+2*bAbsUZfR*4nLw" \
  defectdojo/defectdojo-django:latest \
  manage.py migrate

# Run DefectDojo initializer
echo "Initializing DefectDojo..."
docker run --rm --network codespace_default --entrypoint /entrypoint-initializer.sh \
  -e DD_DATABASE_URL="postgresql://defectdojo:defectdojo@codespace-dojo-postgres-1:5432/defectdojo" \
  -e DD_ADMIN_USER="admin" \
  -e DD_ADMIN_MAIL="admin@defectdojo.local" \
  -e DD_ADMIN_FIRST_NAME="Admin" \
  -e DD_ADMIN_LAST_NAME="User" \
  -e DD_INITIALIZE="true" \
  -e DD_SECRET_KEY="hhZCp@D28z!n@NED*yB!ROMt+WzsY*iq" \
  -e DD_CREDENTIAL_AES_256_KEY="&91a*agLqesc*0DJ+2*bAbsUZfR*4nLw" \
  defectdojo/defectdojo-django:latest

# Start all remaining services
echo "Starting all services..."
docker-compose up -d

# Wait a bit for services to start
sleep 10

# Display access URLs
echo "Services started successfully!"
echo "Access URLs:"
echo "DefectDojo: https://$CODESPACE_NAME-${DD_PORT:-18083}.$GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN"
echo "Dependency-Track API: https://$CODESPACE_NAME-${DTRACK_API_PORT:-18081}.$GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN"
echo "Dependency-Track UI: https://$CODESPACE_NAME-${DTRACK_UI_PORT:-18082}.$GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN"
echo ""
echo "Note: Make sure to set DEFECTDOJO_API_KEY environment variable before running if not already set."

# Display access URLs
echo "Services started successfully!"
echo "Access URLs:"
echo "DefectDojo: https://$CODESPACE_NAME-${DD_PORT:-18083}.$GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN"
echo "Dependency-Track API: https://$CODESPACE_NAME-${DTRACK_API_PORT:-18081}.$GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN"
echo "Dependency-Track UI: https://$CODESPACE_NAME-${DTRACK_UI_PORT:-18082}.$GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN"
echo ""
echo "Note: Make sure to set DEFECTDOJO_API_KEY environment variable before running if not already set."