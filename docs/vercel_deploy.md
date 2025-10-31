Vercel: deploy frontend (static landing page)

If your repo contains a `frontend/` folder or a single-page landing in the root, deploy it to Vercel and configure the API base URL.

Steps
1. Create a Vercel account and connect your GitHub repository.
2. Import the project and select the folder to deploy (e.g. `/frontend` or `/`).
3. Build command: depends on your frontend framework. If it's static HTML/CSS/JS, no build command needed.
4. Output directory: set to the folder that contains `index.html` (or leave empty for root static files).
5. Add Environment Variable `API_BASE_URL` with the Render URL (e.g. `https://cabinet360.onrender.com`).
6. Enable automatic deployments from GitHub (default). Every push to `main` will redeploy.

Notes
- If you keep frontend inside the main repo and it uses Node build tools, ensure `package.json` exists and you add build and install steps in Vercel settings.
- For single-file landing pages, Vercel will deploy instantly.
- To test locally, you can set `API_BASE_URL` in a `.env.local` file for frameworks that support it.

Post-deploy
- Check that API calls from the frontend reach the Render service. If CORS issues appear, add appropriate headers in the backend or use a reverse proxy.
