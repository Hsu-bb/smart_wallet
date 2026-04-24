# Smart Wallet — Smart Budgeting and Expense Tracker

> Final Year Project — Web Application  
> GoalArchivo | Founded 2023  
> Author: **Hsu Lae Yee Phyoe**

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black)

---

## Overview

**Smart Wallet** is a web-based Smart Budgeting and Expense Tracker developed under **GoalArchivo**, founded by David and his team in 2023. The application helps individuals and small business personnel manage their personal finances in real time — tracking income, expenses, savings, debts, and financial goals while providing data-driven insights into spending habits.

The project addresses the limitations of the existing GoalArchivo system, which suffered from manual data entry errors, lack of data visualization, and a basic email notification system with no context or customization. Smart Wallet replaces this with a smarter, more versatile solution that adapts to users' financial behaviour.

---

## Aim

To develop a web application that aids individuals and small business personnel in budgeting their finances and tracking income, expenses, and savings in real time — while helping users make better financial decisions through data visualization, intelligent alerts, and spending recommendations.

---

## Features

- User registration, login, logout and session management
- Forgot password and password reset via email
- Income tracking — add, edit and delete income records
- Expense tracking — log daily expenses by category
- Budget planning — set spending limits per category with overrun alerts
- Budget view — visual breakdown of budget usage per category
- Category management — create and manage custom spending categories
- Savings goals — set, track and monitor progress toward financial goals
- Debt management — track debts with due dates and overdue notifications
- Financial reports — summarized income vs. expense reports
- Notifications — budget overrun and debt due date alerts
- Automated cron job for debt overdue detection
- Sidebar navigation with responsive header

---

## Tech Stack

| Layer           | Technology                          |
|-----------------|-------------------------------------|
| Backend         | PHP (pure, no framework)            |
| Database        | MySQL                               |
| Frontend        | HTML5, CSS3, Vanilla JavaScript     |
| Server          | Apache (XAMPP)                      |
| Version Control | Git + GitHub                        |

---

## Project Structure

```
SmartWallet/
├── dashboard.php              # Main dashboard with financial overview
├── income.php                 # Income management
├── expense.php                # Expense tracking
├── budget.php                 # Budget setup and limits
├── budget_view.php            # Visual budget usage per category
├── category.php               # Category management
├── savings.php                # Savings goals tracking
├── debt.php                   # Debt management
├── report.php                 # Financial reports
├── notifications.php          # Notification centre
├── test_check_overdue.php     # Overdue debt testing utility
│
├── auth/
│   ├── login.php              # User login handler
│   ├── logout.php             # Session logout
│   ├── register.php           # User registration handler
│   ├── forgot_password.php    # Password reset request
│   └── reset_password.php     # Password reset handler
│
├── views/
│   ├── login.php              # Login page view
│   ├── register.html          # Registration page view
│   ├── forgot_password.php    # Forgot password view
│   └── reset_password.php     # Reset password view
│
├── includes/
│   ├── db.php                 # Database connection
│   ├── session.php            # Session management
│   ├── header.php             # Common header
│   └── sidebar.php            # Sidebar navigation
│
├── api/
│   └── notifications.php      # Notifications API endpoint
│
├── cron/
│   └── debt_notifications.php # Cron job: debt overdue checker
│
├── assets/
│   ├── css/
│   │   ├── styles.css         # Global styles
│   │   ├── login.css          # Login page styles
│   │   ├── register.css       # Register page styles
│   │   ├── dashboard.css      # Dashboard styles
│   │   ├── income.css         # Income page styles
│   │   ├── expense.css        # Expense page styles
│   │   ├── budget.css         # Budget page styles
│   │   ├── budget_view.css    # Budget view styles
│   │   ├── category.css       # Category page styles
│   │   ├── savings.css        # Savings page styles
│   │   ├── debt.css           # Debt page styles
│   │   ├── report.css         # Report page styles
│   │   ├── notifications.css  # Notifications styles
│   │   ├── sidebar.css        # Sidebar styles
│   │   ├── header.css         # Header styles
│   │   └── forgot_password.css
│   │
│   └── js/
│       ├── script.js          # Global scripts
│       ├── dashboard.js       # Dashboard charts & logic
│       ├── income.js          # Income page scripts
│       ├── expense.js         # Expense page scripts
│       ├── budget.js          # Budget page scripts
│       ├── budget_view.js     # Budget view scripts
│       ├── category.js        # Category page scripts
│       ├── savings.js         # Savings page scripts
│       ├── debt.js            # Debt page scripts
│       ├── report.js          # Report page scripts
│       ├── notifications.js   # Notifications scripts
│       ├── sidebar.js         # Sidebar scripts
│       └── header.js          # Header scripts
│
└── smart_wallet.sql           # MySQL database schema & seed data
```

---

## Setup & Installation

### Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache server (XAMPP recommended for Windows)
- Web browser

### Local Development (XAMPP)

1. **Clone the repository**
   ```bash
   git clone https://github.com/Hsu-bb/SmartWallet.git
   ```

2. **Move to XAMPP htdocs**
   ```
   C:\xampp\htdocs\SmartWallet\
   ```

3. **Import the database**
   - Start MySQL in XAMPP Control Panel
   - Open `http://localhost/phpmyadmin`
   - Create a new database named `smart_wallet`
   - Click **Import** → select `smart_wallet.sql` → click **Go**

4. **Configure database connection**
   - Open `includes/db.php`
   - Update credentials if needed:
     ```php
     $host = 'localhost';
     $dbname = 'smart_wallet';
     $username = 'root';
     $password = '';
     ```

5. **Start Apache and MySQL** in XAMPP Control Panel

6. **Open in browser**
   ```
   http://localhost/SmartWallet/views/login.php
   ```

---

## Database

The full database schema is included in `smart_wallet.sql`.  
Import it via phpMyAdmin or run:

```bash
mysql -u root -p smart_wallet < smart_wallet.sql
```

---

## Key Modules

| Module         | File              | Description                                      |
|----------------|-------------------|--------------------------------------------------|
| Dashboard      | dashboard.php     | Financial overview with charts                   |
| Income         | income.php        | Add, edit, delete income records                 |
| Expense        | expense.php       | Log and manage daily expenses                    |
| Budget         | budget.php        | Set spending limits per category                 |
| Budget View    | budget_view.php   | Visual breakdown of budget vs. actual spending   |
| Categories     | category.php      | Manage custom expense categories                 |
| Savings        | savings.php       | Create and track savings goals                   |
| Debt           | debt.php          | Track debts with due dates and overdue alerts    |
| Reports        | report.php        | Income vs. expense financial reports             |
| Notifications  | notifications.php | Budget overrun and debt due date alerts          |

---

## Problem Statement

The existing GoalArchivo system had these limitations:

- Manual data entry prone to human error
- No data visualization (no charts or graphs)
- No monthly or category-based spending breakdown on the dashboard
- Basic email notifications with no context or customization
- No savings goal tracking or progress monitoring
- No debt management features
- No financial recommendations based on spending patterns

---

## Solution

Smart Wallet addresses all existing limitations by introducing:

- Structured database-driven tracking for income, expenses, budgets, savings, and debts
- Interactive charts and graphs for spending analysis on the dashboard
- Category-based budget limits with real-time overrun detection
- Customizable notification system for budget and debt alerts
- Automated cron job for debt overdue detection
- Weekly and monthly financial reports
- Password recovery via email

---

## Author

**Hsu Lae Yee Phyoe**  
Final Year Project — GoalArchivo (2023)  
GitHub: [Hsu-bb](https://github.com/Hsu-bb)

---

## License

This project was developed as a Final Year Project for academic purposes.
