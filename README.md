# Voting Application

A secure web-based voting system built with PHP and MySQL. Features a minimalist monochrome design with animated icons, per-election voting, and an admin panel for managing elections and candidates.

## Features

- **Voter Registration** -- 3-step registration with photo upload
- **Election Management** -- Create, toggle, and delete elections with start/end times
- **One Vote Per Election** -- Database-enforced via UNIQUE constraint on `election_votes` table
- **Results** -- Real-time results with progress bars, sorting, and search
- **Admin Panel** -- Manage elections, candidates, audit log, and vote reset
- **Profile Management** -- Edit username, email, photo, and password
- **Rate Limiting** -- Login brute-force protection (5 attempts per 15 minutes per IP)
- **CSRF Protection** -- All forms use CSRF tokens
- **Animated UI** -- Monochrome design with CSS icon animations (fade, bounce, scale, draw, etc.)

## Tech Stack

- **Backend:** PHP 8.5+, MySQLi
- **Database:** MariaDB
- **Frontend:** Tailwind CSS (CDN), vanilla JavaScript
- **Server:** Apache with PHP-FPM

## Prerequisites

- PHP 8.0 or higher (with `finfo`, `session`, `mysqli` extensions)
- MariaDB or MySQL
- Apache with `mod_rewrite` enabled
- PHP-FPM (recommended) or `mod_php`

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/hollali/vote.git
   cd vote
   ```

2. Set up the database:

   ```bash
   mysql -u root -p <<'SQL'
   CREATE DATABASE vote;
   USE vote;

   CREATE TABLE userdata (
     id INT AUTO_INCREMENT PRIMARY KEY,
     username VARCHAR(100) NOT NULL,
     idNum VARCHAR(10) NOT NULL,
     email VARCHAR(255) DEFAULT NULL,
     password VARCHAR(100) NOT NULL,
     reset_token VARCHAR(64) DEFAULT NULL,
     reset_expiry DATETIME DEFAULT NULL,
     photo VARCHAR(100) NOT NULL,
     standard ENUM('group','voter','admin') NOT NULL,
     election_id INT DEFAULT NULL,
     status INT NOT NULL,
     last_login DATETIME DEFAULT NULL,
     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
     login_attempts INT NOT NULL DEFAULT 0,
     votes INT NOT NULL
   );

   CREATE TABLE elections (
     id INT AUTO_INCREMENT PRIMARY KEY,
     name VARCHAR(255) NOT NULL,
     description TEXT DEFAULT NULL,
     start_time DATETIME NOT NULL,
     end_time DATETIME NOT NULL,
     is_active TINYINT(1) NOT NULL DEFAULT 1,
     created_at DATETIME DEFAULT CURRENT_TIMESTAMP
   );

   CREATE TABLE election_votes (
     id INT AUTO_INCREMENT PRIMARY KEY,
     election_id INT NOT NULL,
     user_id INT NOT NULL,
     candidate_id INT NOT NULL,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     UNIQUE KEY unique_vote (election_id, user_id),
     FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
     FOREIGN KEY (user_id) REFERENCES userdata(id) ON DELETE CASCADE,
     FOREIGN KEY (candidate_id) REFERENCES userdata(id) ON DELETE CASCADE
   );

   CREATE TABLE audit_log (
     id INT AUTO_INCREMENT PRIMARY KEY,
     user_id INT DEFAULT NULL,
     action VARCHAR(100) NOT NULL,
     details TEXT DEFAULT NULL,
     ip_address VARCHAR(45) DEFAULT NULL,
     created_at DATETIME DEFAULT CURRENT_TIMESTAMP
   );

   CREATE TABLE login_attempts (
     id INT AUTO_INCREMENT PRIMARY KEY,
     ip_address VARCHAR(45) NOT NULL,
     username VARCHAR(100) DEFAULT NULL,
     attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP
   );
   SQL
   ```

3. Configure database credentials in `actions/connect.php`:

   ```php
   $host = 'localhost';
   $user = 'root';
   $pass = 'your_password';
   $db   = 'vote';
   ```

4. Set directory permissions:

   ```bash
   chmod 755 uploads/
   chown -R www-data:www-data uploads/
   ```

5. Create a default admin account:

   ```sql
   INSERT INTO userdata (username, idNum, password, photo, standard, status, votes)
   VALUES ('admin', '0000000000', '$2y$12$...', 'default.png', 'admin', 0, 0);
   ```

## Project Structure

```
vote/
├── index.php                 # Login page
├── style.css                 # Monochrome theme + icon animations
├── .htaccess                 # Error document routing
├── actions/
│   ├── connect.php           # DB connection, helpers, CSRF, auth functions
│   ├── login.php             # Login handler
│   ├── register.php          # Registration handler
│   ├── voting.php            # Vote handler (enforces one-vote-per-election)
│   ├── forgot-password.php   # Password reset request
│   └── reset-password.php    # Password reset execution
├── includes/
│   ├── header.php            # Shared nav, toast system, election banner
│   └── footer.php            # Shared footer + loading overlay
├── partials/
│   ├── dashboard.php         # Main voting interface
│   ├── results.php           # Election results with progress bars
│   ├── profile.php           # User profile editor
│   ├── registration.php      # 3-step registration form
│   ├── logout.php            # Session destroy
│   ├── forgot-password.php   # Forgot password form
│   └── reset-password.php    # Reset password form
├── admin/
│   └── index.php             # Admin panel (elections, candidates, audit log)
├── uploads/                  # User-uploaded photos
│   └── .htaccess             # Blocks PHP execution in uploads
└── errors/
    ├── 404.php               # Custom 404 page
    └── 500.php               # Custom 500 page
```

## Database Schema

### Core Tables

| Table | Purpose |
|-------|---------|
| `userdata` | Users, candidates, and admins (`standard` enum: `voter`, `group`, `admin`) |
| `elections` | Election definitions with time windows and active toggle |
| `election_votes` | Per-election vote records with `UNIQUE(election_id, user_id)` constraint |
| `audit_log` | Action audit trail (logins, votes, admin actions) |
| `login_attempts` | Rate limiting for login brute-force protection |

### Vote Enforcement

Voting is enforced at the database level. The `election_votes` table has a `UNIQUE` constraint on `(election_id, user_id)`, making it impossible for a user to vote more than once in the same election. The `votes` column on `userdata` is maintained as a denormalized cache for fast reads.

## Default Test Users

| Username | Voter ID | Password | Role |
|----------|----------|----------|------|
| admin | 0000000000 | (set at creation) | admin |
| (register via UI) | (10-digit ID) | (set at creation) | voter |

## License

For educational use.
