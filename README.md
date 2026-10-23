# Greenwood International School Management System (SMS)

A comprehensive, modular, end-to-end School Management System built from scratch in Laravel according to the full system architecture blueprints (Docs 1 through 10 & AI Blueprint).

---

## 🌟 Core Modules Built End-to-End

### 1. Platform & Academic Setup
- **Academic Years**: Single active year enforcement (`is_current`), year lock (`is_locked`) which freezes historical attendance, exams, and invoices into read-only mode.
- **Classes & Sections**: Hierarchical class definitions with `next_class_id` promotion chains, capacity limits, and section-level class teacher designation.
- **Curriculum & Mapping**: Global subject registry (Theory, Practical, Both) with class-subject matrix.
- **Faculty Teaching Assignments**: Mapping of section + subject + teacher for security scoping.

### 2. Student Lifecycle & Admissions
- **Admission Flow**: Atomic transaction creating Student, Guardian (with phone-based deduplication), and initial active Enrollment.
- **Student Directory**: High-performance directory with search, section filtering, and row-level authorization.
- **Full Student Profile**: 5 integrated tabs covering Demographics, Guardian Contacts, Attendance Rates, Exam Results & Report Cards, Fee Invoices & Payment Receipts, and Document attachments.
- **Year-End Bulk Promotion**: Idempotent class-to-class promotion with `promoted`, `retained`, and `alumni` outcomes without ever modifying historical enrollments.

### 3. Faculty & Staff Management
- Complete staff registry with employee codes, designations, qualifications, and role assignments (`Teacher`, `School Admin`, `Accountant`).
- Teaching assignment inspector and class teacher allocations.

### 4. Attendance Module
- **Daily Attendance Register**: Mobile-friendly, high-speed attendance sheet with 1-click **Mark All Present** and **Mark All Absent** controls.
- **Working Calendar**: Resolves holidays, weekly off-days, and school timezone (`Asia/Kolkata`).
- **Attendance Percentage Engine**: Shared mathematical formula accounting for late arrivals and excused leave.
- **Locking & Audit**: 2-day editing window; past-day adjustments require reason and `attendance.edit-past` permission.
- **Monthly Summary Rollups**: Precomputed `attendance_summaries` table for instant analytics and low-attendance warnings (< 75%).

### 5. Examinations, Marks & Report Cards
- **Exam Lifecycle State Machine**: Controlled transitions (`draft` → `marks_open` → `locked` → `published`).
- **Marks Entry Grid**: Rapid input with max marks enforcement, absent flag, grace marks, and post-lock modification audit log (`mark_changes`).
- **Result Computation Engine**: Computes total marks, percentages, CBSE standard 9-point grades (`A1` to `E`), GPAs, and class/section ranks with skipped tie handling.
- **Report Card Generator**: Official school progress report available in browser print preview and downloadable vector PDF via DomPDF with verification badge.

### 6. Fees, Invoices & Receipts
- **Exact Money Arithmetic**: `App\Support\Money` utilizing string math with `bcmath` and half-up rounding (no floating-point inaccuracy).
- **Fee Structures**: Configurable fee heads (Tuition, Lab, Exam, Transport) with once/monthly/quarterly frequencies.
- **Idempotent Invoicing**: Generates period invoices with automatic sibling/scholarship concessions and row-lock deduplication.
- **Counter Collection**: Payment entry supporting Cash, UPI, Card, NetBanking, Cheque with unique receipt numbering (`REC-YYYY-XXXXX`).
- **Official Payment Receipts**: Printable and downloadable PDF receipts with student details, fee breakdown, and cashier signatures.
- **Payment Reversals**: Authorized reversals with mandatory reason logging that safely restore invoice balances.
- **Defaulters Report**: Real-time overdue fee register with days overdue and parent contact numbers.

### 7. Notices & Communication Pipeline
- **Notice Board**: Pinned circulars, priority levels (`normal`, `important`, `emergency`), and parental acknowledgement tracking.
- **Targeted Audiences**: Multi-channel targeting (All School, Specific Roles, or Specific Classes).
- **Unified Notifier**: Dispatch pipeline supporting in-app logs, email, SMS, and WhatsApp drivers with fake drivers for testing.

### 8. AI Features Blueprint
- **AiGateway**: Modular interface with driver independence (`FakeAiDriver`, `GeminiAiDriver`).
- **Assistive Capabilities**:
  - Report Card Remark Drafter (constructive feedback formulated from exam marks).
  - Circular & Announcement Drafter (drafts notices in various tones).
  - Fee Reminder Drafter (courteous payment notices).
- **Human-in-the-Loop Governance**: AI outputs generate `ai_suggestions` with status `pending`; changes require human review before adoption.

---

## 🔒 Security & Row-Level Data Scoping

All Eloquent queries strictly respect `visibleTo(?User $user)` scopes:
- **Super Admin & School Admin**: Full administrative access.
- **Teacher**: Restricted to enrolled students within their assigned teaching sections.
- **Parent / Guardian**: Strictly isolated to their own children's attendance, marks, invoices, and notices.
- **Student**: Isolated to their individual academic record.

---

## 🚀 Quick Start Guide

### 1. Requirements
- PHP 8.2 or 8.3 with extensions (`pdo_sqlite`, `curl`, `mbstring`, `gd`, `intl`, `bcmath`, `zip`)
- Composer 2.x
- Node.js & npm

### 2. Setup & Database Seeding
```powershell
# Install dependencies
composer install
npm install && npm run build

# Run migrations and seed complete demo dataset
php artisan migrate:fresh --seed
```

### 3. Start Local Server
```powershell
php artisan serve
```
Visit: `http://127.0.0.1:8000`

---

## 🔑 Demo Login Accounts

All demo accounts use password: `password123`. The login screen also features **One-Click Instant Login** buttons:

| Role | Email | Capabilities |
|---|---|---|
| **Super Admin** | `superadmin@school.test` | System configuration, all permissions bypass |
| **Principal / Admin** | `principal@school.test` | Academic setup, admissions, exam lifecycle, fee review |
| **Teacher** | `teacher@school.test` | Section 5-A class teacher, mark attendance, enter marks |
| **Accountant** | `accountant@school.test` | Fee structures, invoice generation, payment collection, receipts |
| **Parent** | `parent@school.test` | Isolated view of child Aarav Sharma's attendance, report cards, dues |
| **Student** | `student@school.test` | Student portal view of results and circulars |

---

## 🧪 Testing

The test suite covers unit logic, security isolation, and feature journeys:
```powershell
php artisan test --testdox
```
*(All 18 tests and 40 assertions run and pass cleanly)*
