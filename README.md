# ConnectEd

**BSAU Institute of Computer Studies (ICS) Skill-Based OJT Placement and Company Verification System**

ConnectEd is an institutional web platform designed for Bulacan State Agricultural University (BSAU) to streamline On-the-Job Training (OJT) operations. It provides centralized partner enterprise verification, multi-party Memorandum of Agreement (MOA) tracking, transparent skill-based internship recommendations, concurrency-safe student placements, narrative weekly report monitoring, and institutional CHED/accreditation reporting.

---

## Technology Stack

- **Backend Framework:** Laravel 10 (PHP 8.3)
- **Frontend & App Shell:** Laravel Breeze (Blade templates), Tailwind CSS 3 via Vite, Alpine.js
- **Database:** PostgreSQL 16 (Neon Serverless PostgreSQL)
- **Authentication & RBAC:** Native enum-based authorization (`App\Enums\UserRole`) with server-side Laravel Policies and Gates
- **Hosting / Container Target:** FrankenPHP container / Vercel Edge Serverless
- **File Storage:** Private S3-compatible cloud bucket / local disk abstraction (`DocumentService`)

---

## User Roles & Responsibilities

| Role | Badge | Key Responsibilities |
|---|---|---|
| **OJT Coordinator (OC)** | `Admin / Command` | Creates company records, verifies statutory requirements, accredits partners, manages skills catalog, authorizes placements, delegates tasks, and exports institutional compliance reports. |
| **Co-OJT Coordinator (COC)** | `Coordinator` | Supervises assigned student cohorts, reviews narrative weekly reports, assists with MOA routing, and executes delegated administrative tasks. |
| **University Attorney (UA)** | `Legal Console` | Performs legal vetting on MOAs, records confidential risk remarks and revisions, and issues formal legal review clearances. |
| **Partner Company (PC)** | `Enterprise` | Maintains enterprise profile, posts internship opportunities with skill requirements, reviews student applicants chronologically, and confirms student placements. |
| **ICS Student** | `Intern Hub` | Maintains technical skill profile and certifications, suggests partner companies, receives transparent private skill recommendations, applies for opportunities, confirms placement offers, and uploads weekly narrative reports. |

---

## Core Institutional Rules (Non-Negotiable)

1. **Rule BR-01 (No Company Self-Registration):** Companies never self-register. Company records are created and accredited exclusively by the OJT Coordinator.
2. **Rule BR-02 (In-System Notifications Only):** Strictly in-system database notifications; no external mail, SMS, or chat dependencies.
3. **Rule BR-15 (Soft Deletes Everywhere):** Records are never hard-deleted. Soft deletes and immutable audit logging (`audit_logs`) capture every state change.
4. **Rule BR-19 (Single Active Placement):** Concurrency-safe single active placement per student. Confirmation of a placement automatically withdraws all competing pending applications.
5. **Rule BR-20 (Anti-Ranking & Private Match Scores):** Opportunity match scores are computed on the fly and visible **only** to the student concerned. The system never scores, ranks, or sorts students for companies or coordinators. Applicant lists are presented chronologically.

---

## Demo Accounts & Synthetic Credentials

All seed data is 100% synthetic for academic demonstration purposes. Default password for all seed accounts: `Password123!`

| Role | Name | Email | Password |
|---|---|---|---|
| **OJT Coordinator (OC)** | Dr. Elena Santos | `coordinator@bsau.edu.ph` | `Password123!` |
| **Co-Coordinator (COC)** | Prof. Marcus Reyes | `cocoordinator@bsau.edu.ph` | `Password123!` |
| **University Attorney (UA)** | Atty. Ramon Morales | `attorney@bsau.edu.ph` | `Password123!` |
| **Partner Company (PC)** | Nexus Software Labs | `partner@company.com` | `Password123!` |
| **ICS Student** | Juan Dela Cruz | `student@bsau.edu.ph` | `Password123!` |

---

## Quick Start (Local Development)

### Prerequisites
- PHP 8.3+ with `pdo_pgsql`, `pdo_sqlite`, `gd`, `zip`, `fileinfo` extensions
- Composer 2.x
- Node.js 18+ & NPM
- SQLite (testing) or PostgreSQL 16 (development / production)

### Setup Instructions

1. **Clone and Install Dependencies:**
   ```bash
   git clone https://github.com/christianicelbagaresbasc-debug/ConnectEd_Version1.git
   cd ConnectEd_Version1
   composer install
   npm install
   ```

2. **Environment Configuration:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Migration & Synthetic Seeding:**
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Compile Frontend Assets:**
   ```bash
   npm run build
   # or for hot reloading:
   npm run dev
   ```

5. **Start Local Development Server:**
   ```bash
   php artisan serve
   ```
   Navigate to `http://localhost:8000` and sign in with any of the demo accounts listed above.

---

## Running Automated Tests

ConnectEd includes a comprehensive automated test suite covering authentication, RBAC, company accreditation, MOA legal review, transparent matching, placement concurrency, document security, and CSV reports:

```bash
# Run all tests
php artisan test

# Run code style linter
./vendor/bin/pint --test

# Run dependency audit
npm audit --omit=dev
composer audit
```

---

## Documentation Index

- [Feature Master Index](docs/INDEX.md) (`docs/INDEX.md`)
- [Requirements & Rules Traceability Matrix](docs/TRACEABILITY.md) (`docs/TRACEABILITY.md`)
- [Security & Privacy Architecture Audit](docs/backend/security.md) (`docs/backend/security.md`)
- [Assumptions Log](docs/ASSUMPTIONS.md) (`docs/ASSUMPTIONS.md`)
- [Feature Specifications Directory](docs/features/) (`docs/features/F-001` through `F-021`)

---

## License

Developed as an undergraduate capstone for the **Institute of Computer Studies, Bulacan State Agricultural University (BSAU)**. Proprietary academic license &copy; 2026.
