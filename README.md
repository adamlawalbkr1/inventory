# 📦 Native PHP Inventory Management System (POS)

A robust, secure, and responsive Point of Sale (POS) and Inventory System built for small-to-medium retail businesses (e.g., Supermarkets, Pharmacies, Drink Stores).

**Academic Project:** Computer Science Final Year Project (Level 400).
**Focus:** Automating manual sales, preventing shrinkage, and tracking expiry dates.

## 🚀 Key Features

### 1. 📊 Visual Analytics Dashboard
*   **Real-time Charts:** Interactive Line Chart showing sales trends over the last 7 days (Chart.js).
*   **Live Stats:** Instant counters for Total Revenue, Total Products, Low Stock Alerts, and Expiring Soon items.

### 2. 🛒 Intelligent Point of Sale (POS)
*   **Quick-Pick Interface:** Auto-loads top-selling products for instant access.
*   **AJAX Search:** Search for products instantly without page reloads.
*   **Dual Checkout:** Options to "Complete Sale" (Fast) or "Complete & Print" (Receipt).
*   **Stock Validation:** Prevents selling more items than available in stock.
*   **Expiry Guard:** Automatically blocks the sale of any expired product.

### 3. 📦 Advanced Inventory Management
*   **Expiry Tracking:** Dedicated alerts and filters for goods expiring within 30 days.
*   **Low Stock Alerts:** Visual warnings (Red/Yellow badges) when stock dips below the minimum level.
*   **Product Categorization:** Organize items efficiently by category (e.g., Drinks, Snacks, Essentials).

### 4. 🛡️ Security & User Management
*   **Role-Based Access Control (RBAC):** Distinct permissions for Admin (Full Access) and Staff (Sales Only).
*   **Secure Authentication:** Professional password hashing (Bcrypt) and session management.
*   **Profile Management:** Users can securely update their own passwords.
*   **User CRUD:** Admins can Create, Edit, and Delete system users via a responsive UI.

## 🛠️ Technology Stack
*   **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript (ES6), Chart.js.
*   **Backend:** Native PHP 8.0+ (PDO Driver).
*   **Database:** MySQL / MariaDB (Relational).
*   **Architecture:** MVC Pattern (Model-View-Controller) separation.

## ⚙️ Installation Guide (XAMPP/WAMP)

Follow these steps to run the application locally:

### Step 1: Database Setup
1.  Open phpMyAdmin (http://localhost/phpmyadmin).
2.  Create a new database named `inventory_system`.
3.  Import the provided `seed_demo.sql` file OR open your browser to `http://localhost/abstock/install.php` to generate the schema automatically.

### Step 2: Project Setup
1.  Download or Clone this project.
2.  Copy the project folder to your XAMPP `htdocs` directory (e.g., `C:\xampp\htdocs\abstock`).
3.  Open `config/db.php` and ensure settings match your server:
```php
$host = 'localhost';
$db   = 'inventory_system';
$user = 'root';
$pass = '';
```

### Step 3: Run the Seeder (Demo Data)
To populate the system with dummy data for presentation/defense:
1.  Open your browser and visit: `http://localhost/abstock/seed_demo.php`
2.  You will see a "SUCCESS! Database is Golden" message.

## 🔐 Default Login Credentials

Use these accounts to test the Role-Based Access Control:

| Role | Username | Password | Access Level |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `password` | Full Control (Settings, Users, Reports, Inventory) |
| **Staff / Cashier** | `staff` | `password` | POS & Sales History Only |

## 📂 Project Structure
```plaintext
/abstock
├── /actions       # Backend Logic (Add, Update, Delete, Auth)
├── /assets        # CSS (Design System), JS (POS Logic), and Images
├── /config        # Database Connection Configuration
├── /includes      # Reusable Components (Sidebar, Header, Auth Middleware)
├── /views         # Frontend Pages (Dashboard, POS, Inventory, History)
├── install.php    # System Installation/Migration Script
├── seed_demo.php  # Automated Data Generator
└── README.md      # Documentation
```

## 📜 License
This project is developed for educational purposes as a requirement for the award of B.Sc. Computer Science.
