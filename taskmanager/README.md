
# 📋 Task Manager

A **minimal, beautiful, and fully functional** personal task organizer — built with vanilla PHP, JavaScript, and CSS. No frameworks. No bloat. Just clarity.

> ✨ **"Stay organized. Stay on top of life."**

![Light and Dark Mode](screenshots/Screenshot%20(26).png)

*(Add more screenshots to /screenshots and update image paths if needed)*

---

## ✨ Features
- 🕒 **Live date & time**: `TUESDAY 14:30` + `01 December : 2025`
- 🔍 **Instant search**: filter by title or category
- 🏷️ **Smart categories**: Personal, Work, Birthday, Anniversary, Meeting, Health
- 📅 **Due date reminders**: 🟦 Today | 🟥 Overdue
- 📊 **Task counter**: `5 tasks • 2 completed`
- 🌓 **Light & dark mode**: one-tap toggle
- 📱 **Fully responsive**

---

## 🚀 Setup (XAMPP)
1. Create database `taskdb` in **phpMyAdmin**
2. Run this SQL:
   ```sql
   CREATE TABLE tasks (
     id INT AUTO_INCREMENT PRIMARY KEY,
     title VARCHAR(255) NOT NULL,
     category VARCHAR(50) NOT NULL DEFAULT 'personal',
     due_date DATE NULL,
     completed TINYINT(1) DEFAULT 0
   );
   ---

MIT License • Built by [chiccukaunda](https://github.com/chiccukaunda)
![License](https://img.shields.io/badge/License-MIT-blue.svg)