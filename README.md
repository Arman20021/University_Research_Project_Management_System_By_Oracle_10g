# 🏢 University Research Project Management System (Research Hub)

Video Link:https://drive.google.com/file/d/1rJT3z88uItLNIKcxIHUKDJqeSC4lA1X2/view?usp=sharing

Developed a role-based web application for managing academic research projects, publications, and institutional data. The system helps automate research workflows, track project progress, manage publications, and reduce administrative work. Built using a modular three-layer PHP architecture to ensure maintainability, scalability, and efficient data management.

---

## 🎯 System Architecture & Design Philosophy

The application is structured around an industry-standard **Model-View-Controller (MVC) adaptation** engineered for monolithic web architectures. 

* **Presentation Layer:** Built entirely on semantic HTML5 and styled with a precision fluid grid layout using Tailwind CSS. It features absolute responsiveness, context-aware interface layouts, and reactive micro-interactions driven by modern ES6+ JavaScript.
* **Business Logic & Middleware Layer:** Powered by modular PHP modules that process strict session validation, cryptographic identity validation, stateful transaction checks, and data routing constraints before reaching core files.
* **Data Abstraction Layer:** Relies on robust parameterization and decoupled transaction helper routines to abstract relational database interactions, enforce strict referential data integrity, and protect the system against common security vulnerabilities.

---

## 🚀 Deep Feature Specifications

### 👤 1. Advanced Multi-Tenant Authentication & Session Security
* **Role-Based Access Control (RBAC):** Built with decoupled structural paths that safely isolate the operational scopes of **System Administrators** from **Student Researchers/Users**.
* **Strict Route Guard Middleware:** Implements a proactive session gate tracking engine (`require_role()`). If an unauthorized entity attempts to manually access administrative endpoints via URL manipulation, the request is immediately terminated, sessions are audited, and the user is redirected to a secure login terminal.
* **Secure State Maintenance:** Utilizes native PHP session scopes layered with timeout tokens and state synchronization filters to mitigate hijacking risks and guarantee persistent identity monitoring across separate sub-modules.

### 📄 2. End-to-End Research Lifecycle & Milestone Tracking
* **Comprehensive Project Metadata Index:** Tracks extensive multi-column project definitions including system-generated unique operational IDs, alphanumeric titles, precise creation/submission ISO timestamps, funding tier classification brackets, and supervisor/reviewer primary key relationships.
* **Interactive Development Progress Engine:** Incorporates dynamic, data-driven visual progress matrices tracking project development metrics from absolute early-stage draft concepts up to fully validated, peer-reviewed final academic publications.
* **Collaborative Relational Group Mapping:** Engineered to fully support complex relational many-to-many structures, enabling multi-student research teams to collaborate seamlessly under unified project entities without breaking referential integrity or introducing data duplication.

### 📚 3. Academic Publication Auditing & Peer-Review Pipelines
* **Rigorous Review Panel Verification:** Provides structural ledger options to document and preserve formal publication metadata, capturing official unique Digital Object Identifier (DOI) strings, verified institutional journal publishers, evaluator evaluation remarks, and comprehensive feedback histories.
* **State-Machine Lifecycle Classifications:** Enforces localized transactional business logic states that map progress statuses across tightly monitored structural milestones: `Pending Evaluation`, `Under Active Review`, `Approved for Publication`, and `Rejected/Requires Revision`.
* **Asynchronous Parent Cross-Referencing:** Implements a highly cohesive relational schema that accurately maps peer-reviewed publication entries back to their respective foundational university research projects for multi-tier tracking.

### 🏛️ 4. Institutional Departmental & User Directory Administration
* **Structural Infrastructure Topology Maps:** Houses an administrative master registry mapping high-level department profiles, physical campus facility allocations, office room coordinates, and official contact channels.
* **Granular Identity Provisioning Terminals:** Grants system administrators full database administration capabilities to securely audit, modify, update, provision, or suspend user records corresponding to Students, Supervising Faculty, and independent Academic Reviewers.

### 🔔 5. Automated Asynchronous Notifications & Alert Engine
* **Targeted Communication Dispatcher:** Empowers administrative teams to dispatch system-wide global emergency alerts or configure specific, individual notifications filtered by custom tracking channels (`System Approval`, `Approaching Deadlines`, `Review Submissions`).
* **State-Driven Trigger Mechanisms:** Automated backend listener patterns instantly generate targeted alert logs for all collaborating research team members the exact second a project or publication entity shifts its lifecycle state.

### 🔍 6. High-Performance Unified Global Search Engine
* **Cross-Entity Multi-Table Query Indexer:** A comprehensive, optimized string-matching search interface capable of scanning and indexing across disjointed database collections simultaneously.
* **Instantaneous Data Extraction:** Enables administrators and users to query partial or exact string sequences matching Project Titles, Student Profiles, and Publication DOI numbers instantly from a single centralized field.

---

## 🛡️ Enterprise Security & Defensive Implementation

To ensure compliance with secure coding standards (OWASP Top 10), the application integrates native protective mechanisms directly into its middleware layer:
* **SQL Injection Prevention:** Absolute elimination of raw string concatenation within query structures. The application enforces strict data binding parameterization to separate SQL execution commands from incoming string metrics.
* **Cross-Site Scripting (XSS) Mitigation:** Employs global context-aware escaping wrapper layers (`e()`) that capture, parse, and safely sanitize database outputs before rendering them to the Document Object Model (DOM), defusing potential vector payloads.
* **Cryptographic Credential Protection:** User authentication workflows enforce strong cryptographic hashing functions (`PASSWORD_BCRYPT` salted with `PASSWORD_DEFAULT`) to prevent cleartext password breaches.

---

## 📁 Scalable Directory Architecture

```text
research_hub_web/
├── assets/                  # Compiled utility CSS layers, global asset icons, and interaction files
├── config/                  # Production server environment configurations and ecosystem parameters
├── database/                # Modular DDL table structures, sequence generators, and constraint rules
├── includes/                # Decoupled application building blocks and reusable modules
│   ├── auth.php             # Session state verifiers, RBAC structural gates, and route-guard middleware
│   ├── ui.php               # Dynamic UI component factories, visual badge layout blocks, and form templates
│   ├── data_helpers.php     # Global core database abstraction utilities and sanitization drivers
│   ├── header.php           # Global responsive top-navigation dashboard header panel
│   └── footer.php           # Global system structural page termination and layout footer
├── dashboard.php            # Analytical overview terminal dividing application views by active role state
├── departments.php          # Administrative configuration workspace for institutional campus mappings
├── index.php                # System authentication gateway terminal and core application entry index
├── logout.php               # Flushes system sessions and clears state parameters securely
├── my_projects.php          # Dedicated student researcher workspace tracking active project lifecycles
├── notifications.php        # Unified user communication notice board and administrative broadcast terminal
├── profile.php              # Secure personal profile dashboard, identity tracking, and password resets
├── projects.php             # Master administrative project repository planner and auditing manager
├── publications_reviews.php # Metadata tracking terminal handling peer evaluations, DOIs, and status updates
├── search.php               # Unified high-performance multi-entity keyword mapping and search system
├── signup.php               # Self-service provisioning interface for incoming student researchers
└── users.php                # Master administrative directory for role updates and identity controls