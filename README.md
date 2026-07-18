# Voting Application

A secure web-based voting system built with PHP and MySQL. Features a minimalist monochrome design with animated icons, a dedicated voting booth with confirmation, visual results, and a full admin panel with sidebar navigation.

## Features

### Voting
- **Voting Booth** -- Dedicated page with candidate cards, photos, bios, and confirmation modal
- **Vote Confirmation** -- Receipt screen with election name, candidate, timestamp, and receipt ID
- **One Vote Per Election** -- Database-enforced via `UNIQUE(election_id, user_id)` constraint
- **Results Visualization** -- Leading candidate highlight, horizontal bar charts, ranked candidate cards with progress bars

### User
- **Registration** -- 3-step registration (voter only) with photo upload and ID verification
- **Profile Management** -- Edit username, email, photo, and password (current password required)
- **Password Reset** -- Token-based forgot/reset password flow

### Admin Panel (Sidebar Navigation)
- **Dashboard** -- Stats overview, active election info, quick actions, recent activity
- **Elections** -- Create, toggle, delete elections; reset all votes
- **Candidates** -- Add, edit (name, photo, bio), change password, delete candidates
- **Voters** -- View all voters, unlock locked accounts, delete voters
- **Admins** -- Add new admins, remove (demote) existing admins
- **Activity Log** -- Full audit trail with timestamps, users, actions, and details

### Security
- **Rate Limiting** -- Login brute-force protection (5 attempts per 15 minutes per IP)
- **Account Lockout** -- Accounts locked after 10 failed login attempts; admin can unlock
- **CSRF Protection** -- All forms use rotating CSRF tokens
- **Security Headers** -- `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`
- **Prepared Statements** -- All database queries use parameterized queries
- **Flash Messages** -- Sanitized flash type against allowlist
- **Upload Validation** -- MIME-type based extension detection, file size limits

### Design
- **Monochrome Theme** -- Neutral palette (`#0a0a0a` bg, `#111` nav/sidebar, neutral-* accents)
- **Animated Icons** -- CSS keyframe animations (fade, bounce, scale, draw, float, spin, pop)
- **Responsive** -- Sidebar collapses to hamburger on mobile; all pages mobile-friendly
- **Component System** -- `.card`, `.input-field`, `.progress-bar`, `.stagger`, `.icon-*` utility classes

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
     bio TEXT DEFAULT NULL,
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

3. Configure database credentials:

   Copy `config/config.example.php` to `config/config.php` and update the values:

   ```php
   <?php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'vote');
   ```

4. Set directory permissions:

   ```bash
   chmod 777 uploads/
   ```

5. Create a default admin account:

   ```sql
   INSERT INTO userdata (username, idNum, password, photo, standard, status, votes)
   VALUES ('admin', '0000000000', '$2y$12$...', 'default.png', 'admin', 0, 0);
   ```

   Or register via the UI and update `standard` to `admin` in the database.

## Project Structure

```
vote/
├── index.php                     # Login page
├── style.css                     # Monochrome theme + icon animations
├── .htaccess                     # Error document routing, HTTPS redirect (commented)
├── config/
│   └── config.php                # DB credentials (gitignored)
├── actions/
│   ├── connect.php               # DB connection, helpers, CSRF, auth, rate limiting
│   ├── login.php                 # Login handler (account lockout, std validation)
│   ├── register.php              # Registration handler (voter only, rate limited)
│   ├── voting.php                # Vote handler (redirects to confirmation screen)
│   ├── forgot-password.php       # Password reset request (rate limited)
│   └── reset-password.php        # Password reset execution
├── includes/
│   ├── header.php                # Shared nav, toast system, election banner
│   └── footer.php                # Shared footer + loading overlay
├── partials/
│   ├── dashboard.php             # Main dashboard with candidate list + profile
│   ├── booth.php                 # Voting booth with candidate cards + confirmation modal
│   ├── confirmation.php          # Vote receipt screen after casting
│   ├── results.php               # Results with bar charts, leading candidate, rankings
│   ├── profile.php               # User profile editor
│   ├── registration.php          # 3-step registration form
│   ├── logout.php                # Session destroy
│   ├── forgot-password.php       # Forgot password form
│   └── reset-password.php        # Reset password form
├── admin/
│   ├── index.php                 # Admin dashboard (stats, quick actions, recent activity)
│   ├── elections.php             # Election management (create, toggle, delete, reset votes)
│   ├── candidates.php            # Candidate management (add, edit, bio, photo, password)
│   ├── voters.php                # Voter management (view, unlock, delete)
│   ├── admins.php                # Admin management (add, remove)
│   ├── audit.php                 # Activity log (last 50 entries)
│   └── includes/
│       ├── admin_header.php      # Admin layout with sidebar
│       └── admin_footer.php      # Admin footer
├── uploads/                      # User-uploaded photos
│   └── .htaccess                 # Blocks PHP execution in uploads
└── errors/
    ├── 404.php                   # Custom 404 page
    └── 500.php                   # Custom 500 page
```

## Database Schema

### Core Tables

| Table | Purpose |
|-------|---------|
| `userdata` | Users, candidates, and admins (`standard` enum: `voter`, `group`, `admin`). Includes `bio` field for candidate descriptions |
| `elections` | Election definitions with time windows and active toggle |
| `election_votes` | Per-election vote records with `UNIQUE(election_id, user_id)` constraint |
| `audit_log` | Action audit trail (logins, votes, admin actions) with IP logging |
| `login_attempts` | Rate limiting for login brute-force protection |

### Vote Enforcement

Voting is enforced at the database level. The `election_votes` table has a `UNIQUE` constraint on `(election_id, user_id)`, making it impossible for a user to vote more than once in the same election. The `votes` column on `userdata` is maintained as a denormalized cache for fast reads.

## Default Test Users

| Username | Voter ID | Password | Role |
|----------|----------|----------|------|
| admin | 0000000000 | admin123 | admin |
| (register via UI) | (10-digit ID) | (set at creation) | voter |

## User Roles

| Role | Can Access |
|------|-----------|
| **voter** | Dashboard, Voting Booth, Results, Profile |
| **group** (candidate) | Dashboard, Results, Profile |
| **admin** | All of the above + Admin Panel (sidebar with Dashboard, Elections, Candidates, Voters, Admins, Activity) |

## License

For educational use.
