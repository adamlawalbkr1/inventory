Task List: Native PHP Inventory System (MVP)
 Phase 1: Foundation & Infrastructure

 Create Directory Structure
 Implement 
config/db.php
 Implement 
install.php
 Verification
 Phase 2: Authentication & Core Layouts

 Create Reusable Layouts (includes/)
 Create Login Interface (
views/login.php
)
 Implement Login Logic (
actions/login.php
)
 Implement Logout Logic (
actions/logout.php
)
 Create Dashboard (
views/dashboard.php
)
 Verification
 Phase 3: Inventory Management (Admin Side)

 Update Dashboard Stats (
views/dashboard.php
)
 Create Inventory Interface (
views/inventory.php
)
 Implement Add Product (
actions/add_product.php
)
 Implement Update Product (
actions/update_product.php
)
 Implement Delete Product (
actions/delete_product.php
)
 Verification
 Phase 4: Point of Sale (POS) & Chat Core

 Create Search Endpoint (
actions/search_product.php
)
 Create POS Interface (
views/pos.php
)
 Implement Client-Side Cart Logic (
assets/js/pos.js
)
 Implement Transaction Backend (
actions/save_sale.php
)
 Verification
 Phase 4.5: POS Enhancement (Dual Checkout)

 Update POS UI Buttons (
views/pos.php
)
 Update Client Logic (
assets/js/pos.js
)
 Verification
 Phase 5: Reporting & Output (Receipts & History)

 Create Receipt Page (
views/receipt.php
)
 Implement Print Styles
 Create History Interface (
views/history.php
)
 Verification
 Phase 6: Security, Validation & Polish

 Create Authorization Middleware (
includes/auth_check.php
)
 Secure Admin Pages (
inventory.php
, Actions)
 Implement Input Validation (
pos.php
, 
add_product.php
)
 UI Polish (Sidebar, Empty States)
 Verification
 Phase 7: UI/UX Overhaul (Visual Polish)

 Install Global Assets (
includes/header.php
)
 Define Design System (
assets/css/style.css
)
 Modernize Navigation (
includes/sidebar.php
)
 Polish Dashboard (
views/dashboard.php
)
 Enhance Data Tables (
views/inventory.php
, 
views/history.php
)
 Verification
 Phase 8: Low Stock Alert System

 Update Dashboard Logic (
views/dashboard.php
)
 Update Inventory Filters & Styling (
views/inventory.php
)
 Update POS Search (
actions/search_product.php
, 
assets/js/pos.js
)
 Verification
 Phase 9: Demo Data Seeding (Defense Prep)

 Create Seeding Script (
seed_demo.php
)
 Run and Verify Data
 Phase 10: POS Auto-Load Strategy

 Update Search Endpoint (
actions/search_product.php
)
 Update Client Logic (
assets/js/pos.js
)
 Verification
 Phase 11: Dashboard Visual Analytics

 Include Chart.js (
includes/header.php
)
 Fetch Sales Data (
views/dashboard.php
)
 Create Chart Container & Init Script (
views/dashboard.php
)
 Verification
 Phase 12: Unified Dashboard Integration

 Combine Dashboard Logic (Stats & Chart)
 Implementation of 2-Row Grid Layout
 Final UI Polish & Verification
 Phase 13: User Management & Password Security

 Create Profile View (
views/profile.php
)
 Implement Change Password Logic (
actions/update_profile.php
)
 Create User Management View (
views/users.php
)
 Implement Add User Logic (
actions/add_user.php
)
 Update Sidebar Navigation (
includes/sidebar.php
)
 Verification
 Phase 14: Expiry Tracking & Alerts

 Add Expiry Card to Dashboard (
views/dashboard.php
)
 Implement Inventory Expiry Filter (
views/inventory.php
)
 Implement POS Sale Prevention for Expired Items (
actions/save_sale.php
)
 Verification with Seed Data