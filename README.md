# Right Hire CRM - Version 2

Right Hire CRM is a web-based lead management and outbound call tracking platform designed for sales administrators and outbound call agents. The system streamlines lead management with geographical assignment and real-time call outcome tracking.

## Features

- **State & City Management**: Hierarchical geographical data management
- **Lead Management**: Advanced filtering, bulk import/export, and comprehensive tracking
- **Employee Management**: User account creation with geographical territory assignments
- **Call Logging**: Complete call history with status updates and follow-up scheduling
- **Dashboard**: Real-time analytics and performance metrics
- **Role-Based Access**: Administrator and Employee access levels

## Technical Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript, jQuery, DataTables, Chart.js
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Security**: HTTPS enforcement, bcrypt password hashing, parameterized queries, RBAC

## Installation

1. **Clone the repository**
   ```
   git clone https://github.com/yourusername/righthire-crm.git
   ```

2. **Create a MySQL database**
   ```sql
   CREATE DATABASE righthire_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Import the database schema**
   ```
   mysql -u username -p righthire_crm < database.sql
   ```

4. **Configure the application**
   - Edit `config/config.php` with your database credentials and application settings

5. **Set appropriate file permissions**
   ```
   chmod 755 -R righthire-crm
   chmod 777 -R righthire-crm/uploads
   chmod 777 -R righthire-crm/exports
   ```

6. **Access the application**
   - Navigate to `http://yourdomain.com/righthire-crm` in your web browser
   - Login with the default administrator account:
     - Email: sales@getrigthhire.com
     - Password: Sales@112233

## System Requirements

- PHP 7.4 or higher with PDO, mbstring, and JSON extensions
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled or Nginx
- Modern web browser (Chrome, Firefox, Safari, Edge)

## Directory Structure

```
/righthire-crm/
├── assets/           # CSS, JavaScript, and image files
├── config/           # Configuration files
├── includes/         # Common include files
├── models/           # Database models
├── controllers/      # Application controllers
├── views/            # View templates
├── api/              # API endpoints
├── uploads/          # File uploads directory
├── exports/          # Export files directory
├── index.php         # Main entry point
└── .htaccess         # Apache configuration
```

## Security Features

- Secure password hashing with bcrypt
- CSRF protection for forms
- Parameterized SQL queries to prevent injection
- Input validation and sanitization
- Role-based access control
- Audit trail for all data modifications

## License

This project is proprietary software. Unauthorized copying, modification, distribution, or use is strictly prohibited.

## Support

For support, please contact support@getrighthire.com
