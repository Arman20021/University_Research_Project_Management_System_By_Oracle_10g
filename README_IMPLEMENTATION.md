# University Research Project Management System — PHP + Oracle 10g

This project converts the supplied React/Tailwind dashboard into a PHP + HTML multi-page application while keeping the same sky/slate academic theme.

## 1. What is included

- `index.php` — login page with Admin/User role selection
- `dashboard.php` — role-aware dashboard with live Oracle counts
- `departments.php` — full CRUD for departments
- `users.php` — full CRUD for students, supervisors, and reviewers
- `projects.php` — full CRUD for research projects and assigned students
- `submit_project.php` — user project submission screen
- `my_projects.php` — user project list and details
- `publications_reviews.php` — publications/reviews view and admin CRUD
- `notifications.php` — notifications view and admin create/delete
- `profile.php` — profile update and password change
- `database/01_create_user.sql` — creates the Oracle user/schema
- `database/02_tables_and_seed.sql` — creates tables and inserts seed data
- `config/db.php` — OCI8 connection configuration

## 2. Requirements

- Oracle Database 10g / 10g XE
- Apache or another PHP-capable web server
- PHP with OCI8 enabled
- Oracle Client / Instant Client compatible with your PHP OCI8 installation

## 3. Create the Oracle user

Open SQL*Plus as SYS/SYSTEM or another DBA account:

```sql
@database/01_create_user.sql
```

Then connect as the application user:

```sql
CONNECT RESEARCH_HUB/research123@localhost/XE
@database/02_tables_and_seed.sql
```

If your database service is not `XE`, replace it with your service name.

## 4. Configure the PHP connection

Open `config/db.php` and update:

```php
define('DB_USERNAME', 'RESEARCH_HUB');
define('DB_PASSWORD', 'research123');
define('DB_CONNECTION_STRING', 'localhost/XE');
```

For Oracle 10g Easy Connect, examples are:

```php
localhost/XE
//localhost:1521/XE
//192.168.1.10:1521/ORCL
```

## 5. Put the project on your web server

Copy the complete `research_hub_php_oracle` folder into your server document root, for example:

```text
C:\xampp\htdocs\research_hub_php_oracle
```

or on Linux:

```text
/var/www/html/research_hub_php_oracle
```

Open:

```text
http://localhost/research_hub_php_oracle/
```

## 6. Demo accounts

Both demo accounts use password:

```text
password
```

Admin:

```text
premiumtrendz290@gmail.com
```

User:

```text
ava.rahman@univ.edu
```

## 7. CRUD implementation notes

- Every form uses OCI8 bind variables through `db_execute()` in `config/db.php`.
- Oracle dates are saved with `TO_DATE(:field, 'YYYY-MM-DD')`.
- Passwords are stored as PHP bcrypt hashes and verified with `password_verify()`.
- Project/student assignment uses the bridge table `project_students`.
- The dashboard counts are loaded live from the database.

## 8. Common Oracle/PHP errors

### OCI8 extension is missing

Enable OCI8 in `php.ini`, restart Apache/PHP-FPM, then verify with:

```bash
php -m | grep oci8
```

### ORA-12154 / ORA-12514

The connection string or service name is wrong. Check listener status on the Oracle machine:

```bash
lsnrctl status
```

### ORA-01017

Username/password is wrong. Check `config/db.php` and your Oracle user.

### Foreign key error while deleting

A department, supervisor, reviewer, or project is referenced by another table. Delete or update dependent records first.
