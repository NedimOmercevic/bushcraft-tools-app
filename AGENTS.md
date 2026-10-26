# AGENTS.md - Guidelines for AI Assistant

## Project Overview
Bushcraft Tools Management Application - A Single Page Application (SPA) built with vanilla HTML/CSS/JS frontend and PHP (FlightPHP framework) REST API backend with MySQL database.

## Architecture & Technology Stack
- **Frontend**: Vanilla JavaScript (AJAX / Fetch API), HTML5, Bootstrap 5 (SPA architecture).
- **Backend**: PHP 8.x, FlightPHP framework, MySQL database (PDO for queries).
- **Authentication**: JWT (JSON Web Tokens).

## Code & Directory Structure Rules
- **Backend Folder**: `backend/`
  - `config/` -> Environment configuration (secrets in `config.php`, template in `config.example.php`).
  - `dao/` -> Data Access Objects using PDO and prepared statements.
  - `services/` -> Business logic and external API integrations.
  - `routes/` -> FlightPHP REST endpoint definitions.
  - `middleware/` -> Authentication, authorization, logging, and error handling.
  - `logs/` -> Info and error log files.
- **Frontend Folder**: `frontend/`
  - `css/`, `js/`, `views/`, `static/`
- **Documentation Folder**: `docs/`
  - `AI_LOG.md` -> Log of AI tool usage per milestone.

## Guidelines for AI Generation
- NEVER mix backend PHP code with frontend HTML/CSS/JS.
- DO NOT render server-side HTML; keep the REST API pure JSON.
- Standardize REST HTTP response status codes (200, 201, 400, 401, 403, 404, 500).
- Always use PDO prepared statements to prevent SQL Injection.