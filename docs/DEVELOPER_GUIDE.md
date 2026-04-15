# 🛠️ CodeIgniter 4 Unified Project - Developer & Database Guide

This project is a high-performance support ticketing system shared between Web and Mobile applications.

## 💾 Unified Database Architecture
We use a **single industry-grade PostgreSQL schema** for both platforms. All media (photos, attachments, chat replies) is stored directly in the database as **BYTEA binary data**.

### 🔗 Table Relationships
1. **`users`**: Central hub. Contains `rollno` and `course` for mobile integration.
2. **`support_tickets`**: Linked to users. Contains `image_url` for mobile cloud links.
3. **`ticket_replies`** (Threaded Chat): Allows back-and-forth discussion with multiple attachments.
4. **`support_ticket_history`**: Audit log for all ticket status changes.

---

## 📂 Project Structure & Fixes
* **Binary Media Storage:** No local `uploads/` folders are needed for production. Images are streamed directly from the DB via the `Media` controller.
* **Ready for Collaboration:** The `writable/` folder structure is included in Git via `.gitkeep` files to prevent permission errors on new clones.
* **Config Template:** Use `.env.example` to create your local `.env` file.

---

## 🚀 Quick Setup (for New Clones)
Anyone cloning this repo should follow these steps:

1. **Composer Install:**
   ```bash
   composer install
   ```

2. **Environment Configuration:**
   * Copy `.env.example` to `.env`.
   * Configure your **PostgreSQL** details in `.env`.

3. **Database Setup:**
   * Run `php spark migrate` to build the unified schema.
   * Run `php spark auth:seed-admin` to create a default admin (`admin@example.com` / `password123`).

4. **Serve:**
   ```bash
   php spark serve
   ```

---
*Created by Antigravity AI Assistant*
