PlanetScale setup and import

This file contains step-by-step instructions for creating a PlanetScale database, importing the local `database.sql` schema, and creating a password for Render.

Prerequisites
- Install the PlanetScale CLI (`pscale`). See https://planetscale.com/docs/reference/cli
- Install a MySQL client (`mysql`) in your PATH.

Steps (PowerShell)

1. Authenticate to PlanetScale:

```powershell
pscale auth login
```

2. Create the database (or create it in the PlanetScale UI):

```powershell
pscale database create cabinet360_saas
```

3. (Optional) Create a branch called `main` if not auto-created:

```powershell
pscale branch create cabinet360_saas main
```

4. Start the local PlanetScale proxy to allow importing the schema (keep this terminal open while importing):

```powershell
pscale connect cabinet360_saas main --port 3306
```

5. In a new PowerShell window, import the schema using the local proxy:

```powershell
# From repository root
mysql -h 127.0.0.1 -P 3306 -u root < .\database.sql
```

If you prefer a GUI, connect your MySQL client to `127.0.0.1:3306` while the proxy runs and import `database.sql`.

6. Create a cross-region password (or a password for Render):

```powershell
pscale password create cabinet360_saas render-user
```

This command will print a password once — copy it immediately. You'll use `render-user` and the printed password for `DB_USER` and `DB_PASS` in Render.

Notes about TLS/SSL
- PlanetScale requires TLS for external connections. Use the entrypoint in the Docker image to provide the CA certificate at container start.
- Two options to provide CA to Render:
  - `DB_SSL_PEM_B64`: Base64-encoded PEM file contents. The container entrypoint will decode this and write `/etc/ssl/certs/pscale-ca.pem`.
  - `DB_SSL_CA_CONTENT`: The raw PEM contents (less common in UI; can be added via Render's secret management).

How to get the CA PEM
- PlanetScale uses a CA that is usually publicly trusted; however if you need a CA file from your provider follow their docs. Alternatively, you can skip providing CA and let OS trust chain handle it (Render base images normally ship with standard CA bundles). If TLS errors occur, provide the CA via `DB_SSL_PEM_B64`.

Troubleshooting
- If the import fails with authentication errors, ensure the proxy is running and you're connecting to 127.0.0.1:3306.
- If you get TLS errors from your running container on Render, provide the CA PEM to `DB_SSL_PEM_B64` as described in the Render instructions file.
