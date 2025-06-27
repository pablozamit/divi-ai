# Divi AI Designer

This repository contains two main pieces:

1. **WordPress plugin** located in `gemini-weaver-divi/`.
2. **Next.js front end** built with Genkit flows under `src/`.

## WordPress plugin installation

1. Copy the `gemini-weaver-divi` folder into the `wp-content/plugins` directory of your WordPress site (or compress the folder as a zip and upload it from **Plugins ▸ Add New**).
2. Activate **Gemini Weaver Divi** from the Plugins screen.
3. After activation, navigate to **Settings ▸ Gemini Weaver** and enter your Google Gemini API key. The plugin will store it in the `gwd_gemini_api_key` option. You can also define the constant `GWD_GEMINI_API_KEY` in `wp-config.php` to provide the key.

## Next.js application

The Next.js app uses the `@genkit-ai/googleai` plugin. It reads the Gemini API key from environment variables (`GEMINI_API_KEY`, `GOOGLE_API_KEY` or `GOOGLE_GENAI_API_KEY`). Create a `.env.local` file at the project root and define one of these variables:

```bash
GEMINI_API_KEY=your-api-key
```

Install dependencies and run the development server:

```bash
npm install
npm run dev
```

The app will start on <http://localhost:9002>.

## Development workflow

- **Plugin** – Work inside `gemini-weaver-divi/`. Copy changes to your local WordPress setup and refresh the admin page to test. The plugin assets in `assets/js` and `assets/css` are plain files that can be edited directly.
- **Next.js front end** – Use `npm run dev` while editing React components. To run Genkit flows in isolation, start them with `npm run genkit:dev`.

For an entry point to the React app, see `src/app/page.tsx`.
