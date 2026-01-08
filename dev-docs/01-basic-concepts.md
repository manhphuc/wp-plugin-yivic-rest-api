# WordPress Plugin: Yivic REST API User - Authentication & Access Control
## Base concepts
This WordPress plugin provides a secure and efficient way to handle REST API authentication and user access control. It generates unique access tokens for authenticated users, which can be used to authorize actions such as editing posts, pages, and other restricted operations within the WordPress environment.

## Key Features
### User Authentication via Yivic REST API
- Implements an API endpoint for user login.
- Authenticates users based on their WordPress credentials (username & password).
- Returns a unique token upon successful authentication.

### Token-Based Authentication
- Generates a secure, time-limited access token for each user.
- Stores tokens in the database with expiration timestamps.
- Supports token refresh and revocation for security.

### Role-Based Access Control (RBAC)
- Maps WordPress user roles to different levels of API permissions.
- Ensures only authorized users can perform specific actions (e.g., only Editors and Admins can edit posts).

### API Endpoints for Authentication & Authorization
- /wp-json/yivic-auth/v1/login → Authenticates users and returns an access token.
- /wp-json/yivic-auth/v1/validate → Validates tokens for API requests.
- /wp-json/yivic-auth/v1/logout → Invalidates tokens upon user logout.

### Integration with WordPress Core
- Compatible with WordPress’s built-in user management system.
- Supports default WordPress user roles and capabilities.

### Security Measures
- Uses hashed tokens for storage.
- Implements token expiration and refresh mechanisms.
- Protects against brute force attacks with rate limiting.

## Use Case Scenarios
- Allowing external applications to authenticate users securely via REST API.
- Restricting access to specific WordPress REST API routes based on user roles.
- Enabling frontend applications (e.g., React, Vue.js) to interact with WordPress securely.
- Enhancing API security without relying on basic authentication or cookies.
