## 1. Project Requirements and Goals
- **Objective**: Develop a standalone WordPress plugin that makes it easy for site operators to configure bitcoin rewards for quizzes and surveys.

- **Functionality**: Integrate Bitcoin Lightning rewards, allowing quiz participants to earn Satoshis based on performance.  This boosts engagement and offers a unique incentive for users to learn and to engage.

## 2. Current Functionality
**Note that this is still actively being developed and should be considered ALPHA software.  Please use with caution and report any bugs!**

- **Overall**: This plugin is completely functional and can currently be implemented on any WordPress site by downloading a zip file of this source code. 

- **Database**: Custom WordPress table for storing quiz and survey.

- **Data Management**: Functions for inserting, retrieving, and displaying quiz data.

- **Front-End Integration**: JavaScript for validating lightning addresses and managing quiz submissions.

- **Features**: Tracking budget, limiting retries based on lightning addresses, and managing rewards, integrated with BTCPay Server and Alby Wallet.

## 3. Known Issues and Challenges
- **Main Challenge**: Improving the WordPress admin backend for easy configuration by site operators.

## 4. Technology Stack Details
- **Environment**: WordPress with custom PHP scripts.
- **Front-End**: JavaScript for user interactions.
- **Database**: SQL for data handling.
- **Payments Integration**: Bitcoin Lightning Network for distributing rewards using BTCPay Server or Alby Wallet.

## 5. User Flow and Interaction
- **Process**: Users participate in quizzes and surveys, submit their lightning addresses, and receive Satoshis based on quiz outcomes.

## 6. Admin Configuration and Interface
- **Settings**: Admins can set parameters like maximum retries, Satoshis per answer, and maximum Satoshi budget.

- **Admin UI**: Interface for managing rewards and viewing quiz results.  Results are available for export in CSV format for data analysis.

## 7. Data Handling and Security Considerations
- **Security**: Emphasis on data validation, sanitization, and secure handling of sensitive information.

## 8. Integration Points
- **Core File**: `index.php` acts as the primary integration point, setting up the plugin and including necessary files.

## 9. Testing and Development Environment
- **Local Development**: Using "Local" by WP Engine with NGINX, PHP 8.2.10, MySQL 8.0.16, and WordPress 6.4.1.  Also developed with XAMPP
- **OS and Tools**: Developed on Windows 11 using Visual Studio Code.
- **Theme**: This has been tested on a variety of themes.

## 10. Feedback and User Insights
- **Current Status**: We have received reqests to make it easy for a user to sign up for a Lightning Address if they don't already have one.  We have also receive many requests to host quizzes on behalf of users.  These functionalities are currently being explored. 

## 11. Future Plans and Features
- **Monetization**: Implement payment splits for self-hosted plugins that will help fund maintenance and further development.  Implement a custom website that makes hosted quizzes and surveys easy to configure in exchange for bitcoin.

- **Front-End Development**: Continue testing on various sites and themes to ensure uniformly attractive UX.

- **Feedback and Iteration**: Using our self-hostedd surveys to gather post-deployment feedback collection for improvements.

- **Additional Integrations**: Plans to include JoltzRewards API when they have Lightning Address functionality available.  Researching additional integration that will allow our users to easily generate a Lightning Address.
