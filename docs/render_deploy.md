Render (Docker) deployment checklist

Steps to create the Docker Web Service on Render

1. Create a Render account and connect your GitHub repository: https://render.com

2. Create a new service
- Type: Web Service
- Environment: Docker
- Name: cabinet360
- Branch: main
- Dockerfile path: `/Dockerfile`
- Build command: `composer install --no-dev --optimize-autoloader`
- Start command: leave blank (we use ENTRYPOINT)
- Health check path: `/`
- Auto deploy: enabled (optional)
- Disk: attach a persistent disk and mount to `/var/www/html/uploads` (size: 5-10GB)

3. Environment variables (paste these into Render as environment variables / secrets)

Use these exact keys (example values shown):

- APP_ENV=production
- APP_NAME=Cabinet360
- APP_URL=https://<your-render-host>   # update after initial deploy
- DB_HOST=<your-planetscale-host>.psdb.cloud
- DB_NAME=cabinet360_saas
- DB_USER=render-user
- DB_PASS=<password-you-copied-from-pscale>

TLS/CA options (choose one):
- DB_SSL_PEM_B64=<base64-of-planetScale-ca-pem>
  OR
- DB_SSL_CA_CONTENT=<raw-PEM-text>

Other recommended variables
- UPLOAD_PATH=/uploads
- UPLOAD_MAX_SIZE=5M
- SESSION_SECURE=true
- SESSION_TIMEOUT=1800
- JWT_SECRET=<a-strong-random-string>

4. How to generate the base64 CA (PowerShell)

If you have a CA file (ca.pem) locally, run:

```powershell
$bytes = [System.IO.File]::ReadAllBytes('C:\path\to\ca.pem')
[Convert]::ToBase64String($bytes) | Set-Content -Path ca.b64
# Then copy the contents of ca.b64 into Render's DB_SSL_PEM_B64 secret
```

5. First deploy
- Click "Create Web Service" and trigger the first deploy. If the build fails on `composer install` make sure Composer is available (Dockerfile includes Composer). Review build logs.

6. Post deploy
- After the service is live, copy the service URL (e.g. `https://cabinet360.onrender.com`) and set `APP_URL` in Render to that value and re-deploy if necessary.
- Verify:
  - Visit `https://<render-url>/login.php`
  - Check Render logs (Dashboard → Service → Logs) for application errors or DB connection errors
  - Test adding a client and uploading a file (uploads mounted to persistent disk)

7. Rollback / troubleshooting
- If PDO throws SSL errors, double-check `DB_SSL_PEM_B64` or `DB_SSL_CA_CONTENT` and ensure the entrypoint is writing the certificate file (the `docker-entrypoint.sh` prints a message when it writes the file).
- If uploads fail, open the shell on the Render instance and check `/var/www/html/uploads` permissions.

8. Notes about scaling and persistent storage
- Render's disk is a persistent volume attached to the service. For multi-instance scaling, consider using an external object storage (S3 / DigitalOcean Spaces) for uploads.

9. Post-deploy quick tests
- Login page loads
- Dashboard renders with DB-driven counts
- Upload and download a small PDF
- Create a payment and generate a receipt (if payments configured locally)

If you want, I can also prepare a small HTTP test script you can run from your machine which checks the root URL and login page (curl/powershell).