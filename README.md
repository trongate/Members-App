# Trongate v2 Members Area Application

## 📺 YouTube Tutorial Series

This repository accompanies my YouTube tutorial series where we build a fully operational members area using Trongate v2. Follow along with the videos to learn how to create a complete membership system from scratch.

## 🚀 About This Project

This is a Trongate v2 PHP framework application that demonstrates how to build a secure members area. The application includes user registration, authentication, and member management features.

## 📋 Prerequisites

Before getting started, ensure you have:

- PHP 7.4 or higher
- MySQL or MariaDB database
- Apache web server (or compatible)
- Composer (for dependency management)

## 🛠️ Installation & Setup

### 1. Clone or Download the Repository

```bash
git clone [repository-url]
cd members_app
```

### 2. Set Up Configuration Files

**Important Note**: The `config` directory is not included in this repository (it has been added to `.gitignore` for security reasons). To get started, you need to:

1. Download a fresh installation of Trongate v2 from the official website
2. Copy the `config` directory from the fresh Trongate v2 installation into this project
3. Configure the database settings in `config/database.php` with your credentials

### 3. Create the Members Table

Run the following SQL in your MySQL/MariaDB database to create the members table:

```sql
--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `username` varchar(65) NOT NULL,
  `first_name` varchar(65) NOT NULL,
  `last_name` varchar(75) NOT NULL,
  `email_address` varchar(75) NOT NULL,
  `date_created` int(11) NOT NULL,
  `num_logins` int(11) NOT NULL DEFAULT 0,
  `last_login` int(11) NOT NULL,
  `password` text NOT NULL,
  `user_token` varchar(32) NOT NULL,
  `confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `trongate_user_id` int(11) NOT NULL,
  `ip_address` varchar(65) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `email_address` varchar(75) NOT NULL,
  `date_created` int(11) NOT NULL,
  `token` varchar(32) NOT NULL,
  `member_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rate_limiter`
--

DROP TABLE IF EXISTS `rate_limiter`;
CREATE TABLE `rate_limiter` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(65) NOT NULL,
  `num_failed_attempts` tinyint(1) NOT NULL,
  `next_attempt_allowed` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rate_limiter`
--
ALTER TABLE `rate_limiter`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2159;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rate_limiter`
--
ALTER TABLE `rate_limiter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
```

### 4. Configure Application

Update the configuration files in the `config` directory as needed:

- `config/config.php` - Application settings
- `config/database.php` - Database connection (update with your credentials)
- `config/constants.php` - Application constants

### 5. Set File Permissions

Ensure proper file permissions:

```bash
chmod 755 -R .
chmod 777 -R writable/
```

### 6. Access the Application

Navigate to your application URL in a web browser:

```
http://localhost/members_app/public/
```

## 📁 Project Structure

```
members_app/
├── config/          # Configuration files (not included - see setup instructions)
├── engine/          # Trongate v2 framework core
├── modules/         # Application modules
├── public/          # Publicly accessible files
├── .gitignore       # Git ignore rules
├── .htaccess        # Apache configuration
├── license.txt      # License information
└── README.md        # This file
```

## 📝 License

This project is open-source and available under the MIT License. See the `license.txt` file for details.

## ❓ Getting Help

If you encounter issues:

1. Check the YouTube tutorial videos for guidance
2. Review the Trongate v2 documentation
3. Search existing issues in this repository
4. Create a new issue with details about your problem

## 🔗 Useful Links

- [Trongate v2 Official Documentation](https://trongate.io/docs)
- [Trongate Framework GitHub](https://github.com/trongate/trongate-framework)
- [PHP Official Website](https://www.php.net/)
- [MySQL Documentation](https://dev.mysql.com/doc/)

-------

*Happy coding! Follow along with the YouTube series to build this application step by step.*
