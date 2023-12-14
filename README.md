## 1. Project Requirements and Goals
- **Objective**: Develop a WordPress plugin add-on for the HD Quiz plugin.
- **Functionality**: Integrate Bitcoin Lightning rewards, allowing quiz participants to earn Satoshis based on performance.

## 2. Current Functionality
- **Database**: Custom WordPress table for storing quiz results.
- **Data Management**: Functions for inserting, retrieving, and displaying quiz data.
- **Front-End Integration**: JavaScript for validating lightning addresses and managing quiz submissions.
- **Features**: Tracking budget, limiting retries based on lightning addresses, and managing rewards.

## 3. Known Issues and Challenges
- **Main Challenge**: Implementing a feature to limit quiz retries based on a user's lightning address and correctly using the quiz ID.

## 4. Technology Stack Details
- **Environment**: WordPress with custom PHP scripts.
- **Front-End**: JavaScript for user interactions.
- **Database**: SQL for data handling.
- **Payments Integration**: Bitcoin Lightning Network for distributing rewards.

## 5. User Flow and Interaction
- **Process**: Users participate in quizzes, submit lightning addresses, and receive Satoshis based on quiz outcomes.

## 6. Admin Configuration and Interface
- **Settings**: Admins can set parameters like maximum retries, Satoshis per answer, and maximum Satoshi budget.
- **Admin UI**: Interface for managing rewards and viewing quiz results.

## 7. Data Handling and Security Considerations
- **Security**: Emphasis on data validation, sanitization, and secure handling of sensitive information.

## 8. Integration Points
- **Core File**: `index.php` acts as the primary integration point, setting up the plugin and including necessary files.

## 9. Testing and Development Environment
- **Local Development**: Using "Local" by WP Engine with NGINX, PHP 8.2.10, MySQL 8.0.16, and WordPress 6.4.1.
- **OS and Tools**: Developed on Windows 11 using Visual Studio Code.
- **Theme**: Standard WordPress Twenty Twenty-Three theme.

## 10. Feedback and User Insights
- **Current Status**: No user feedback yet, as the focus is still on backend and admin development.

## 11. Future Plans and Features
- **Front-End Development**: Enhance user experience with an engaging and informative interface.
- **Feedback and Iteration**: Post-deployment feedback collection for improvements.
- **Additional Integrations**: Plans to include JoltzRewards API along with the current BTCPayServer's Greenfield API.
