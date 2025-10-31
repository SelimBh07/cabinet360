---
mode: agent
---
You are my DevOps assistant. I have a PHP + MySQL project called "Cabinet360 Ilyass Merzak" currently running locally.
I want to deploy it in production using Render for the backend and Vercel for the frontend.

Your mission:
1. Prepare the repo for deployment:
   - Initialize a Git repo if not already.
   - Create `.gitignore` (ignore vendor, node_modules, uploads, .env).
   - Create a professional `README.md` explaining how to run locally and deploy.
   - Update PHP config to read DB credentials from environment variables.

2. Add production deployment files:
   - Add a `Dockerfile` using PHP 8.1 + Apache with mysqli and pdo_mysql enabled.
   - Add a `render.yaml` file describing the Render web service configuration (Docker build).
   - Add an `.env.example` file showing variables: DB_HOST, DB_USER, DB_PASS, DB_NAME, APP_ENV.

3. Automate GitHub setup:
   - Create a new private repo named `cabinet360` (if possible through CLI or guide me to create it manually).
   - Push all local files to `main` branch.

4. Prepare database integration:
   - Instruct me to create a PlanetScale database.
   - Guide me how to import my `database.sql` file into PlanetScale.
   - Generate the PlanetScale connection env variable section for Render.

5. Configure Render deployment:
   - Deploy the app as a **Docker Web Service**.
   - Set Render environment variables based on `.env.example`.
   - Confirm the service exposes port 80 and works over HTTPS.
   - Add post-deploy test instructions (check DB connection, login page).

6. Configure Vercel (for static landing page if exists):
   - Deploy `/frontend` folder (or root if single page).
   - Set `API_BASE_URL` to the Render URL after deployment.
   - Enable automatic redeploy on GitHub push.

7. Final output:
   - Print all commands I need to run manually (if needed).
   - Print all file paths it created or updated.
   - Confirm when everything is ready to deploy.

Goal: I should be able to run this entire workflow inside VS Code, commit → push → deploy → send live link to my client (Ilyass) within 30 minutes.

Now start executing step 1.
