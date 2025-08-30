# Right Hire CRM

A web-based lead management and outbound call tracking platform designed for sales administrators and outbound call agents.

## Overview

Right Hire CRM streamlines lead management with geographical assignment and real-time call outcome tracking. The system allows administrators to manage leads, assign them to employees based on geographical territories, and track call outcomes in real-time.

## Features

- **State & City Management**: Hierarchical data structure for geographical organization
- **Lead Management**: Advanced filtering, bulk import/export, and comprehensive tracking
- **Call Logging**: Detailed call outcome tracking with status workflow
- **Employee Management**: User account creation with geographical territory assignments
- **Dashboard**: Real-time analytics and performance metrics
- **Role-Based Access**: Administrator and Employee access levels

## Technology Stack

- **Frontend**: Bootstrap 5, jQuery, DataTables, Chart.js
- **Backend**: PHP 8.0+, CodeIgniter 4
- **Database**: MySQL 8.0+

## Installation

### Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- mod_rewrite enabled

### Setup Instructions

1. **Clone the repository**
   ```
   git clone https://github.com/ZixisoftTech/TestCRM.git
   cd TestCRM
   ```

2. **Database Setup**
   - Create a MySQL database
   - Import the database schema:
     ```
     mysql -u username -p your_database_name < righthire-crm/database/schema.sql
     ```
   - (Optional) Import sample data:
     ```
     mysql -u username -p your_database_name < righthire-crm/database/sample_data.sql
     ```

3. **Configuration**
   - Copy `config/config.sample.php` to `config/config.php`
   - Update the database connection settings in `config/config.php`
   - Set the base URL in `config/config.php`

4. **Web Server Configuration**
   - Point your web server to the `righthire-crm` directory
   - Ensure the `.htaccess` file is properly configured for URL rewriting

5. **File Permissions**
   - Set proper permissions for directories:
     ```
     chmod 755 -R uploads/
     chmod 755 -R exports/
     ```

## Default Login Credentials

- **Administrator**
  - Email: sales@getrighthire.com
  - Password: Sales@112233

## Usage

### Administrator Functions

- Manage states and cities
- Create and manage employee accounts
- Assign geographical territories to employees
- Import and export leads
- View comprehensive reports and analytics

### Employee Functions

- View and manage assigned leads
- Log call outcomes
- Schedule follow-ups
- Track personal performance

## Lead Status Workflow

The system implements the following lead status workflow:

```
New → Follow-up → [Not Attend | Wrong Number | Other | Dead | Interested] → Win
```

- Follow-up status requires date/time scheduling
- All status changes are logged with timestamps and user attribution

## Security Features

- HTTPS enforcement
- bcrypt password hashing
- SQL injection prevention
- Role-based access control
- Secure session management

## Development

### Directory Structure

```
righthire-crm/
├── api/                # API endpoints
├── assets/             # CSS, JS, images
├── config/             # Configuration files
├── controllers/        # Controller classes
├── database/           # Database schema and sample data
├── exports/            # Export files directory
├── includes/           # Helper functions and utilities
├── models/             # Model classes
├── uploads/            # Upload directory
└── views/              # View templates
```

### Key Files

- `index.php`: Main entry point
- `config/config.php`: Configuration settings
- `config/database.php`: Database connection settings
- `models/Model.php`: Base model with audit trail implementation

## License

Proprietary - All rights reserved

## Support

For support, please contact sales@zixisoft.com

