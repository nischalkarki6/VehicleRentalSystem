# Vehicle Rental System (VRS)

## Project Overview

The Vehicle Rental System is a PHP-based web application designed to facilitate online vehicle bookings. It features user authentication, a premium vehicle fleet browsing interface, a dynamic booking system, and a comprehensive administration panel for managing users and rentals.

---

## What Has Been Completed So Far

### 1. Architecture & Setup

- **MVC Architecture:** Established a clear Model-View-Controller structure (`controllers/`, `models/`, `view/`) to separate business logic, data, and presentation.
- **Database Schema (`database.sql`):** Designed and finalized the database with tables for `Users`, `Vehicles`, `Rentals`, `ContactMessages`, and `PasswordResets`. Streamlined the `Vehicles` table by removing redundant fields (`FuelType`, `EngineCC`) to focus on premium EV and luxury models.
- **Project Structure:** Cleaned up the root directory by moving assets (`public/css`, `public/js`) and removing redundant files (e.g., placeholder images).

### 2. User Authentication & Security

- **Registration & Login:** Fully implemented secure user signup and login processes with password hashing.
- **Session Management:** Verified robust user session handling (`session_start()`) across all application pages.
- **Password Reset:** Implemented logic for password resets via token-based validation.
- **Access Control:** Differentiated roles (`user` vs `admin`), restricting access to admin-specific pages.

### 3. Frontend & UI

- **Styling Refactoring:** Transitioned from inline CSS to external stylesheets, ensuring a cleaner codebase and consistent UI/UX.
- **Premium Fleet Interface:** Developed a high-end, dynamic search interface for the fleet (`fleet.php`), featuring real-time price sliders and dynamic transmission filters.
- **Single Vehicle Booking Details:** Built a state-of-the-art vehicle details page (`bookings.php`) with dynamic technical specifications, safety overviews, and an interactive pricing calculator.
- **Admin Dashboard UI:** Built a standalone admin dashboard, isolating it from the public website navigation. Refined the admin sidebar and excluded administrators from the general registered users list view.

### 4. Core Features

- **Admin CRUD Operations:** Successfully completed full Create, Read, Update, and Delete capabilities for Vehicles, Users, and Bookings within the Admin Panel. Action buttons are fully wired to backend controllers.
- **Vehicle Fleet Management:** Admins can seamlessly add, edit, and toggle vehicle availability.
- **Booking System:** Complete frontend-to-backend flow allowing users to select dates, see an estimated total, and submit a rental request to the database.

---

## What is Incomplete / Pending Tasks

### 1. Advanced Integrations

- **Payment Gateway Integration:** Implement a third-party service (e.g., Stripe, PayPal) to process actual booking payments securely instead of relying solely on "pay later/pending" states.
- **Email Notifications:** Integrate a mail server/SMTP (like PHPMailer) to send real emails for Password Resets, Booking Confirmations, and Contact Form submissions.

### 2. System Enhancements

- **User Profile Management:** Create a dedicated page for users to update their personal information, address, and change their password.
- **Image Upload Handling:** Build secure file upload functionality so admins can upload actual image files for new vehicles rather than providing an image URL.
- **Pagination & Search Filters:** Add robust pagination and filtering to the admin tables (Users, Vehicles, Bookings) to handle large datasets effectively as the platform scales.

### 3. Security & Polish

- **Input Validation & Sanitization:** Perform a comprehensive audit to ensure all user inputs are strictly validated and sanitized to prevent SQL Injection and XSS attacks.
- **Form Error Handling:** Improve user feedback on forms (e.g., displaying specific error messages directly beneath fields rather than generic alerts).
- **Responsive Design Testing:** Conduct thorough testing to ensure all custom grid layouts (especially the new premium booking pages) adapt flawlessly across mobile, tablet, and desktop viewports.
