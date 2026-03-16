-- Create Database
CREATE DATABASE IF NOT EXISTS patil_photography;
USE patil_photography;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin', 'editor') DEFAULT 'user',
    phone VARCHAR(20),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    resetToken VARCHAR(255) DEFAULT NULL,
    resetTokenExpiry DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Clients Table
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    zipCode VARCHAR(20),
    category ENUM('Regular', 'VIP', 'New Inquiry') DEFAULT 'New Inquiry',
    notes TEXT,
    totalBilled DECIMAL(15,2) DEFAULT 0.00,
    totalPaid DECIMAL(15,2) DEFAULT 0.00,
    pendingAmount DECIMAL(15,2) DEFAULT 0.00,
    event VARCHAR(100),
    budget DECIMAL(15,2) DEFAULT 0.00,
    status ENUM('Lead', 'Active', 'Archived') DEFAULT 'Lead',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Gallery Table
CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    image TEXT NOT NULL,
    category VARCHAR(100) DEFAULT 'General',
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Films Table
CREATE TABLE IF NOT EXISTS films (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    youtubeUrl VARCHAR(255) NOT NULL,
    category VARCHAR(100) DEFAULT 'Wedding',
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- System Settings Table
CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY DEFAULT 1,
    businessName VARCHAR(255) DEFAULT "The Patil Photography & Film's",
    primaryLogo TEXT,
    secondaryLogo TEXT,
    backgroundImage TEXT,
    contactEmail VARCHAR(100),
    contactPhone VARCHAR(20),
    primaryMobileNumber VARCHAR(20),
    secondaryMobileNumber VARCHAR(20),
    address TEXT,
    gstNumber VARCHAR(50),
    hideServices BOOLEAN DEFAULT FALSE,
    websiteUrl VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Social Links Table
CREATE TABLE IF NOT EXISTS social_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    settings_id INT DEFAULT 1,
    platform ENUM('WhatsApp', 'Instagram', 'Facebook', 'YouTube', 'Twitter', 'LinkedIn', 'Other') NOT NULL,
    url VARCHAR(255) NOT NULL,
    icon VARCHAR(100) DEFAULT '',
    active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (settings_id) REFERENCES system_settings(id) ON DELETE CASCADE
);

-- Event Types Table
CREATE TABLE IF NOT EXISTS event_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    label VARCHAR(100),
    isActive BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contacts Table
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('New', 'Read', 'Replied') DEFAULT 'New',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Enquiries Table
CREATE TABLE IF NOT EXISTS enquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    groomName VARCHAR(100) NOT NULL,
    brideName VARCHAR(100) NOT NULL,
    phoneNumber VARCHAR(20) NOT NULL,
    eventStartDate DATE NOT NULL,
    eventEndDate DATE NOT NULL,
    budget DECIMAL(15,2),
    location VARCHAR(255) NOT NULL,
    message TEXT,
    status ENUM('New', 'Contacted', 'Booked', 'Closed') DEFAULT 'New',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Enquiry Events Table (Many-to-Many)
CREATE TABLE IF NOT EXISTS enquiry_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enquiry_id INT,
    event_name VARCHAR(100),
    FOREIGN KEY (enquiry_id) REFERENCES enquiries(id) ON DELETE CASCADE
);

-- Enquiry Services Table (Many-to-Many)
CREATE TABLE IF NOT EXISTS enquiry_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enquiry_id INT,
    service_name VARCHAR(100),
    FOREIGN KEY (enquiry_id) REFERENCES enquiries(id) ON DELETE CASCADE
);

-- Services Table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    basePrice DECIMAL(15,2) DEFAULT 0.00,
    category VARCHAR(100),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Invoices Table
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoiceNumber VARCHAR(50) NOT NULL UNIQUE,
    clientId INT,
    quotationId INT DEFAULT NULL,
    eventType VARCHAR(100) NOT NULL,
    invoiceDate DATE NOT NULL,
    eventDate DATE NOT NULL,
    dueDate DATE NOT NULL,
    subtotal DECIMAL(15,2) DEFAULT 0.00,
    discount DECIMAL(15,2) DEFAULT 0.00,
    discountType ENUM('fixed', 'percentage') DEFAULT 'fixed',
    taxPercentage DECIMAL(5,2) DEFAULT 0.00,
    tax DECIMAL(15,2) DEFAULT 0.00,
    grandTotal DECIMAL(15,2) DEFAULT 0.00,
    paymentStatus ENUM('Paid', 'Partially Paid', 'Partial', 'Unpaid', 'Overdue', 'Draft', 'Sent') DEFAULT 'Unpaid',
    amountPaid DECIMAL(15,2) DEFAULT 0.00,
    workflowStage VARCHAR(100) DEFAULT 'Planning',
    paymentMethod VARCHAR(50) DEFAULT 'UPI',
    clientName VARCHAR(100),
    accountName VARCHAR(100),
    accountNumber VARCHAR(50),
    ifscCode VARCHAR(20),
    upiId VARCHAR(100),
    notes TEXT,
    thankYouMessage TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Invoice Items Table
CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT,
    service_id INT,
    serviceName VARCHAR(255),
    quantity INT DEFAULT 1,
    days INT DEFAULT 1,
    ratePerDay DECIMAL(15,2) NOT NULL,
    total DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

-- Quotations Table
CREATE TABLE IF NOT EXISTS quotations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotationNumber VARCHAR(50) NOT NULL UNIQUE,
    clientId INT,
    eventType VARCHAR(100) NOT NULL,
    quotationDate DATE NOT NULL,
    eventDate DATE NOT NULL,
    validityDate DATE NOT NULL,
    subtotal DECIMAL(15,2) DEFAULT 0.00,
    discount DECIMAL(15,2) DEFAULT 0.00,
    discountType ENUM('fixed', 'percentage') DEFAULT 'fixed',
    taxPercentage DECIMAL(5,2) DEFAULT 0.00,
    tax DECIMAL(15,2) DEFAULT 0.00,
    grandTotal DECIMAL(15,2) DEFAULT 0.00,
    paymentTerms TEXT,
    notes TEXT,
    thankYouMessage TEXT,
    status ENUM('Draft', 'Sent', 'Accepted', 'Rejected', 'Expired', 'Negotiation') DEFAULT 'Draft',
    clientName VARCHAR(100),
    email VARCHAR(100),
    whatsapp_no VARCHAR(20),
    location VARCHAR(255),
    retainerAmount DECIMAL(15,2) DEFAULT 0.00,
    stage VARCHAR(100) DEFAULT 'Concept',
    moodboard TEXT,
    channel ENUM('Email', 'WhatsApp', 'Call', 'Other') DEFAULT 'Email',
    followUpDate DATE DEFAULT NULL,
    convertedToInvoice BOOLEAN DEFAULT FALSE,
    invoiceId INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Quotation Items Table
CREATE TABLE IF NOT EXISTS quotation_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_id INT,
    service_id INT,
    serviceName VARCHAR(255),
    quantity INT DEFAULT 1,
    days INT DEFAULT 1,
    ratePerDay DECIMAL(15,2) NOT NULL,
    total DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE
);

-- Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoiceId INT NOT NULL,
    clientId INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    paymentDate DATE NOT NULL,
    paymentMethod ENUM('Cash', 'Bank Transfer', 'UPI', 'Credit Card', 'Cheque', 'Other') NOT NULL,
    transactionId VARCHAR(100),
    notes TEXT,
    isRecorded BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (invoiceId) REFERENCES invoices(id) ON DELETE CASCADE
);

-- Sliders Table
CREATE TABLE IF NOT EXISTS sliders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    image TEXT NOT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Team Members Table
CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100),
    image TEXT,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Testimonials Table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clientName VARCHAR(100) NOT NULL,
    review TEXT NOT NULL,
    rating INT DEFAULT 5,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Love Stories Table
CREATE TABLE IF NOT EXISTS love_stories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    thumbnail TEXT NOT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    display_order INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Love Story Gallery Table
CREATE TABLE IF NOT EXISTS love_story_gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    love_story_id INT,
    image TEXT NOT NULL,
    FOREIGN KEY (love_story_id) REFERENCES love_stories(id) ON DELETE CASCADE
);

-- Popups Table
CREATE TABLE IF NOT EXISTS popups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT,
    image TEXT,
    isActive BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Initial User
INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@example.com', '$2y$10$YourHashedPasswordHere', 'admin');

-- Initial System Settings
INSERT INTO system_settings (id, businessName) VALUES (1, "The Patil Photography & Film's");
