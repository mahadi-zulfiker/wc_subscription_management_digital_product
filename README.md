# Digital Subscription Plugin

## Description
The **Digital Subscription Plugin** integrates with **WooCommerce** to offer a **subscription-based digital product download system**. Instead of purchasing individual digital products, users must **subscribe** to gain access.  

## Features
✅ Uses **WooCommerce’s default virtual product system** for digital downloads  
✅ **No Add to Cart or Checkout** for digital products  
✅ **Conditional Download Button** (Users must be **logged in** & have an **active subscription**)  
✅ **Subscription Management System** (Tracks subscription status in the database)  
✅ **Admin Panel** for managing subscription plans  

---

## Installation

### 1️⃣ Upload & Activate
1. Download or clone this plugin to your WordPress installation.  
2. Upload the plugin to `wp-content/plugins/` directory.  
3. Activate it from the **WordPress Admin Panel** → **Plugins**  

### 2️⃣ Configure WooCommerce  
1. Navigate to **WooCommerce → Products**  
2. Add a **New Product** and mark it as **Virtual**  
3. Upload the digital file under the **Downloadable Files** section  

### 3️⃣ Setup Subscription System  
1. Go to **Subscriptions** (Custom Post Type)  
2. Add **New Subscription Plan**  
3. Set the subscription details (duration, pricing via WooCommerce, etc.)  

---

## Usage

### ✅ **Granting Download Access**
- Users must **purchase a subscription** to download products.  
- The download button appears **only if the user is logged in** and has an **active subscription**.  
- No need to set prices for digital products manually.  

### ✅ **Hiding Add to Cart & Checkout for Digital Products**
- WooCommerce’s **Add to Cart & Pricing** is **hidden** for virtual products.  
- Users can download directly without adding to cart or checking out.  

---

## Hooks & Filters

### **Action Hooks**
- `dpm_hide_add_to_cart_for_digital_products` → Removes Add to Cart button for digital products  
- `dpm_custom_download_button` → Displays a conditional **Download** button  

### **Filters**
- `woocommerce_product_get_data` → Ensures the product is **virtual** and **price is removed**  

---

## Support & Contributing
Feel free to **report issues** or **contribute** via GitHub.  
For support, contact **amitavrchy01@gmail.com**.  

---

## License
This plugin is licensed under the **GPL-2.0+**.  
