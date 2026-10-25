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
