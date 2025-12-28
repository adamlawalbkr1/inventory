# PROJECT REPORT DOCUMENTATION

## 3.10 Database Design (Logical Schema)

The system utilizes a relational database management system (MySQL) with a normalized structure to ensure data integrity and minimize redundancy. Below are the detailed table descriptions:

### Table 1: users
Key table containing system access credentials and administrative roles.
| Column | Data Type | Description |
| :--- | :--- | :--- |
| id | INT (PK, AI) | Unique identifier for each user. |
| username | VARCHAR(50) | Unique login identifier. |
| password | VARCHAR(255) | Hashed credential (using password_hash). |
| role | ENUM | Access level ('admin' or 'staff'). |
| created_at | DATETIME | Timestamp of account creation. |

### Table 2: categories
Enables grouping of products for easier inventory management.
| Column | Data Type | Description |
| :--- | :--- | :--- |
| id | INT (PK, AI) | Unique identifier for the category. |
| name | VARCHAR(50) | Descriptive name of the category (e.g., Soft Drinks). |

### Table 3: products
The core inventory repository containing pricing and stock levels.
| Column | Data Type | Description |
| :--- | :--- | :--- |
| id | INT (PK, AI) | Unique identifier for the product. |
| category_id | INT (FK) | Reference to the Categories table. |
| name | VARCHAR(100) | Full name of the product. |
| cost_price | DECIMAL(10,2) | Purchase cost from suppliers. |
| selling_price | DECIMAL(10,2) | Retail price for customers. |
| stock_quantity | INT | Current available units in store. |
| min_stock_level | INT | Threshold for low stock alerts. |
| expiry_date | DATE | Expiration date for quality control. |

### Table 4: sales (Receipt Headers)
Maintains the master record of every transaction performed in the system.
| Column | Data Type | Description |
| :--- | :--- | :--- |
| id | INT (PK, AI) | Unique transaction identifier. |
| user_id | INT (FK) | Reference to the User (Cashier) who sold items. |
| receipt_number | VARCHAR(20) | Unique alphanumeric tracking code. |
| total_amount | DECIMAL(10,2) | Grand total of the transaction. |
| sale_date | DATETIME | Precise date and time of the sale. |

### Table 5: sale_items (Line Items)
Maps specific products and quantities to a parent sale record (Child table of 'sales').
| Column | Data Type | Description |
| :--- | :--- | :--- |
| id | INT (PK, AI) | Primary Key. |
| sale_id | INT (FK) | Reference to the parent Sales ID. |
| product_id | INT (FK) | Reference to the specific Product sold. |
| quantity | INT | Number of units purchased. |
| price_at_sale | DECIMAL(10,2) | Price of the item at the moment of sale. |

---

## 4.2 Software Requirements (Updated Specifications)

To support the modernization and security features of the Inventory Management System, the following technologies were utilized:

*   **Frontend Technologies:**
    *   **HTML5 & CSS3:** For semantic structure and advanced styling.
    *   **Bootstrap 5:** For a responsive, mobile-first professional user interface.
    *   **JavaScript (ES6):** Powering the dynamic POS cart and AJAX product filtering.
    *   **Chart.js:** For rendering real-time business performance analytics on the dashboard.
*   **Backend Engineering:**
    *   **PHP 8.0+:** Utilizing an Object-Oriented approach and modern syntax.
    *   **PDO (PHP Data Objects):** Ensuring secure, parameter-protected database interactions to prevent SQL Injection.
*   **Database Management:**
    *   **MySQL / MariaDB:** A robust relational database engine for handling persistent data storage.
*   **Development & Deployment Environment:**
    *   **Web Server:** Apache (via XAMPP/WAMP distribution).
    *   **IDE:** Visual Studio Code (Modern environment with IntelliSense and Git integration).

---

## 4.3 Deployment Procedure (User Manual)

Follow these steps to deploy and run the application on a local server environment:

1.  **Server Initialization:** Launch the **XAMPP Control Panel** and start both the **Apache** and **MySQL** modules.
2.  **Files Setup:** Ensure the project folder (`abstock`) is located within the `htdocs` directory of your XAMPP installation.
3.  **Database Configuration:**
    *   **Option A (Recommended):** Open your browser and navigate to `localhost/abstock/install.php`. The system will automatically create the database, tables, and seed the default admin account.
    *   **Option B (Manual):** Import the SQL schema using `phpMyAdmin`.
4.  **Accessing the System:** Open the browser and visit `http://localhost/abstock`.
5.  **Login Credentials:**
    *   **Username:** `admin`
    *   **Password:** `password` (Recommended to change this immediately in the My Profile section).
6.  **Demo Preparation:** Run `seed_demo.php` once logged in to populate the system with professional sample data for the project presentation.
