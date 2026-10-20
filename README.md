# DATA.JSON Manager

A PHP web interface for managing registered `data.json` files, viewing their contents, and reviewing change history.

## Features

- Dashboard for registered JSON files
- View and edit JSON data through the web interface
- Track changes in a local history file
- Check whether registered URLs are reachable
- JSON-based registry and history storage

## Requirements

- PHP 8.0+ recommended
- A web server such as Apache or Nginx
- Write permission for the application's `data/` directory and any JSON files the application is intended to manage

## Local setup

1. Clone or download this repository.
2. Put the project in your local PHP web root.
3. Ensure the `data/` directory is writable by the PHP process.
4. Start a local development server from the project directory:

   ```bash
   php -S 127.0.0.1:8000
   ```

5. Visit `http://127.0.0.1:8000/`.

The included registry and history files are intentionally empty starter data. Add only URLs and files you are authorized to manage.

## Important security warning

**This project currently does not include a login/authentication layer in the supplied code. Do not deploy it to a public server or expose `api.php` to the internet as-is.** Its API has actions that can modify the registry and managed JSON files. Before production use, add authentication and authorization, CSRF protection for state-changing requests, strict allowlisting of manageable file paths, URL validation/SSRF protections, and rate limiting. Restrict access at the web-server level until those controls are implemented.

The project may check or read remote URLs. Only use it with systems you own or are authorized to administer. Do not put private URLs, tokens, customer data, or production history into this repository.

## Data files

- `data/json-files.json` — registry of managed JSON files (starts empty)
- `data/history.json` — change history (starts empty)
- `history.json` — legacy history file (starts empty)

The `data/` directory should not be directly browsable on a deployed web server. The included `.htaccess` helps on Apache; configure equivalent access rules if using Nginx or another server.

## GitHub upload

```bash
git init
git branch -M main
git add .
git status
git commit -m "Initial commit: DATA.JSON Manager"
git remote add origin https://github.com/YOUR_USERNAME/data-json-manager.git
git push -u origin main
```

If the GitHub repository already contains a README or other commit, integrate that history before pushing instead of force-pushing.

## License

No license has been added. Add a license file if you intend to grant others permission to reuse, modify, or distribute this project.
