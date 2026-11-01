# AI Usage Log

## Milestone 1: Initial Setup, Static Frontend, and Database Planning

### 1. AI Tools Used
- **Cursor IDE / AI Assistant**: Used for boilerplate project structure setup, HTML/Bootstrap SPA layout generation, navigation routing in JS, and initial documentation drafts.

### 2. Key Prompts Used
- *"Generate a SPA web application project layout following the specification with frontend and backend folders, .gitignore, and config.example.php."*
- *"Create a static Bootstrap 5 single-page application with hash-based routing for Bushcraft Tools containing views for Login, Register, Dashboard, Tools Catalog, Tool Details, Profile, and Weather/Camp recommendations."*
- *"Draft a relational database ERD structure with at least 5 entities suitable for a Bushcraft Tools e-commerce & review application."*

### 3. AI Errors and Corrections (Minimum 2 required)

#### Example 1: Missing Required Views & Ignoring `.gitignore` Specifics
- **What was wrong**: The AI generated the basic structure and main views (Catalog, Dashboard, Login), but omitted the **User Profile view**, which is explicitly mentioned as a required example in the project specification. Additionally, the AI set `.gitignore` to only ignore `*.log` files instead of ensuring clean log directory handling.
- **How I noticed it**: I cross-referenced the generated pages and `.gitignore` file against the Milestone 1 requirements checklist in the project PDF document.
- **How I fixed it**: I instructed the AI to add `frontend/views/profile.html` with user details, email, role, and a change-password form, updated the JS router, and adjusted `.gitignore` to exclude `backend/logs/*` while retaining `.gitkeep`.

#### Example 2: Incomplete README Draft and Missing ERD Plan
- **What was wrong**: The AI created a very brief `README.md` with only two sentences about the topic, omitting the required technology stack, project folder structure, ERD schema breakdown, and execution instructions. It also failed to generate the initial database schema plan for the 5 entities.
- **How I noticed it**: Reviewing the Milestone 1 deliverables requirement list, which strictly demands a comprehensive draft `README.md` and draft ERD diagram/schema.
- **How I fixed it**: I defined the 5 database entities (`User`, `Category`, `Tool`, `Review`, `Favorite`) with primary/foreign key relationships, created the ERD diagram (`docs/erd.png`, source `docs/erd.dbml`), and expanded `README.md` to cover setup instructions, entity descriptions, roles, and the planned OpenWeather integration.

## Milestone 2: Database and DAO Layer

### 1. AI Tools Used
- **Cursor IDE / AI Assistant**: Used for the MySQL schema dump, `BaseDao` and entity DAOs, FlightPHP REST CRUD routes, ERD refresh, and README / AI-log updates.

### 2. Key Prompts Used
- *"Milestone 2: design a MySQL schema with at least five entities, primary and foreign keys, export a .sql file with sample data, and implement a DAO layer with a base class and CRUD using prepared statements."*
- *"Database credentials must be read from config.php which is not committed; commit config.example.php instead. Expose GET, POST, PUT/PATCH, and DELETE for each entity."*

### 3. AI Errors and Corrections (Minimum 2 required)

#### Example 1: UPDATE/INSERT identifiers taken from request keys
- **What was wrong**: The first `BaseDao` draft built `SET email = :email` style SQL by looping every key in the JSON body. Prepared statements bind *values*, not column names, so a client could have sent extra keys and changed the SQL shape.
- **How I noticed it**: Reviewing the generated DAO against the “always use PDO prepared statements” rule — binding values is not enough if identifiers come from the client.
- **How I fixed it**: Each entity DAO now sets a `$columns` whitelist (`email`, `name`, `rating`, …). `filterColumns()` drops anything else before SQL is built. `id` cannot be overwritten on insert/update.

#### Example 2: Composer TLS failure and passwords leaking in GET /users
- **What was wrong**: `composer install` for FlightPHP failed (Avast MITM / missing local CA). A first `UserDao` also returned `password_hash` on GET because it reused `BaseDao::getAll()` unchanged.
- **How I noticed it**: Composer exited with curl error 60. A trial JSON payload for `GET /users` still contained bcrypt hashes.
- **How I fixed it**: Vendored FlightPHP 3.16.1 under `backend/lib/flightphp/` and added `backend/autoload.php` so the API runs without Packagist. `UserDao` hashes a `password` field with `password_hash()` and strips `password_hash` from every read. Credentials stay in gitignored `config.php`; only `config.example.php` is committed.
