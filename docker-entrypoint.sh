#!/bin/sh
set -e

# If a base64-encoded CA certificate is provided, write it to disk and set DB_SSL_CA
if [ -n "${DB_SSL_PEM_B64}" ]; then
  echo "Writing DB SSL CA from DB_SSL_PEM_B64 to /etc/ssl/certs/pscale-ca.pem"
  mkdir -p /etc/ssl/certs
  echo "$DB_SSL_PEM_B64" | base64 -d > /etc/ssl/certs/pscale-ca.pem
  export DB_SSL_CA=/etc/ssl/certs/pscale-ca.pem
fi

# If DB_SSL_CA contains a PEM string (not base64), allow writing it too
if [ -n "${DB_SSL_CA_CONTENT}" ]; then
  echo "Writing DB SSL CA from DB_SSL_CA_CONTENT to /etc/ssl/certs/pscale-ca.pem"
  mkdir -p /etc/ssl/certs
  printf '%s' "$DB_SSL_CA_CONTENT" > /etc/ssl/certs/pscale-ca.pem
  export DB_SSL_CA=/etc/ssl/certs/pscale-ca.pem
fi

# Fall back: if DB_SSL_CA is a path that exists, keep it as-is.
if [ -n "${DB_SSL_CA}" ] && [ -f "${DB_SSL_CA}" ]; then
  echo "Using existing DB_SSL_CA path: ${DB_SSL_CA}"
fi

# Finally launch Apache (original official php image uses apache2-foreground)
exec apache2-foreground
