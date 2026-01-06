# Bindu Namavali Nondani - Government Office Portal

## Overview
A PHP-based portal for the Divisional Commissioner Office Amravati (Backward Class) - "बिंदू नामावली नोंदणी" (Point List Registration System). The application supports user registration, admin management, complaints, feedback, and document management (Shasan Nirnay - Government Decisions).

## Project Structure
```
/                           # Root directory
├── index.php               # Main landing page
├── logo.png                # Logo image
├── 1.jpg, 2.jpg            # Slideshow images
├── ho/hostel/              # Main application
│   ├── index.php           # User login page
│   ├── includes/           # Configuration and common files
│   │   ├── config.php      # Database configuration (PostgreSQL)
│   │   ├── header.php      # Common header
│   │   └── sidebar.php     # Common sidebar
│   ├── admin/              # Admin panel
│   │   ├── index.php       # Admin login
│   │   ├── dashboard.php   # Admin dashboard
│   │   ├── registration.php # User registration
│   │   ├── includes/       # Admin config files
│   │   ├── css/            # Admin stylesheets
│   │   └── js/             # Admin JavaScript files
│   ├── css/                # User stylesheets
│   ├── js/                 # User JavaScript files
│   └── fpdf/               # PDF generation library
```

## Technology Stack
- **Backend**: PHP 8.2
- **Database**: PostgreSQL (Replit database)
- **Frontend**: HTML, CSS, Bootstrap, JavaScript, jQuery
- **PDF Generation**: FPDF library

## Database Configuration
The application uses Replit's PostgreSQL database with a MySQLi compatibility layer. Connection is configured via environment variables:
- `PGHOST` - Database host
- `PGPORT` - Database port  
- `PGDATABASE` - Database name
- `PGUSER` - Database username
- `PGPASSWORD` - Database password

## Database Tables
- `admin` - Administrator accounts
- `userregistration` - Registered users/organizations
- `complaints` - User complaints/posts
- `feedback` - User feedback
- `shasan_nirnay` - Government decisions/documents
- `userlog` - Login activity logs
- `rooms` - Room management
- `courses` - Course information
- `notebook` - User notebooks

## Default Admin Credentials
- Username: `admin`
- Email: `admin@admin.com`
- Password: `admin123`

## Running the Application
The PHP built-in development server runs on port 5000:
```bash
php -S 0.0.0.0:5000
```

## Key Features
- User and Admin login systems
- User registration with district selection
- Complaint/Post submission and management
- Document upload (Shasan Nirnay)
- Feedback system
- User notebook functionality

## Language
The application is primarily in Marathi (मराठी) with English admin interfaces.
