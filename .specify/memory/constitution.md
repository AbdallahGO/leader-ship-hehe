# PHP Backend User Dashboard Constitution

## Core Principles

### I. Secure Backend Architecture

- All user data must be encrypted at rest and in transit
- Authentication and authorization checks required on every endpoint
- Follow principle of least privilege for database access
- Implement rate limiting on all public endpoints
- Use parameterized queries to prevent SQL injection
- Validate and sanitize all inputs
- Security-first design in every layer

### II. Clean Code

- Write self-documenting, readable code
- Follow SOLID principles (Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, Dependency Inversion)
- Use meaningful variable and function names
- Maintain consistent code formatting and style
- DRY principle: Don't Repeat Yourself
- Comments should explain "why," not "what"
- Maximum cyclomatic complexity of 10 per method

### III. REST API Standards

- Use proper HTTP methods (GET, POST, PUT, DELETE, PATCH)
- Correct HTTP status codes (200, 201, 400, 401, 403, 404, 422, 500)
- Resource-based endpoints (/api/users, /api/profiles, /api/notifications)
- No verbs in endpoint paths (use /api/users/123/avatar instead of /api/upload-avatar)
- Version endpoints (/api/v1/...)
- Support pagination, filtering, and sorting on list endpoints

### IV. Performance Optimization

- Database queries optimized with proper indexing
- Eager loading for relationships (avoid N+1 queries)
- Cache frequently accessed data (Redis)
- Implement pagination for large datasets
- Optimize database migrations for minimal downtime
- Use queue workers for long-running tasks
- Monitor query performance and response times

### V. Scalable Architecture

- Service-based architecture for separation of concerns
- Repository pattern for database abstraction
- Dependency injection for loose coupling
- Stateless API design for horizontal scaling
- Support multiple server instances
- Decouple components using event-driven architecture
- Prepare for multi-region deployment

### VI. Automated Testing (NON-NEGOTIABLE)

- Test-Driven Development: Tests written first, then implementation
- Minimum 80% code coverage
- Unit tests for all business logic
- Integration tests for API endpoints
- Authentication and authorization tests
- Security vulnerability tests
- Performance and load tests
- Red-Green-Refactor cycle enforced

### VII. Security-First Development

- CSRF token protection on all state-changing requests
- CORS properly configured
- HTTPS enforced in production
- Password hashing with bcrypt
- Secure session management with expiration
- Login attempt throttling
- Two-factor authentication ready
- Security headers (X-Frame-Options, X-Content-Type-Options, CSP)
- Regular security audits and penetration testing

### VIII. Consistent JSON Responses

- Standard response wrapper for all endpoints
- Consistent error response format
- Include request metadata (timestamp, version)
- Use camelCase for JSON properties
- Document response schema for each endpoint
- Type consistency (strings, numbers, booleans)
- Null values handled consistently

### IX. Modular Services

- Each service has single responsibility
- Clear interfaces between services
- Event-based service communication
- Services independently deployable
- Shared libraries for common functionality
- Service containerization ready
- API contract testing between services

### X. Database Optimization

- Proper indexing strategy on all queries
- Foreign key constraints enforced
- Database migrations tracked in version control
- No direct SQL in application code
- Connection pooling configured
- Query caching where appropriate
- Regular database maintenance and optimization
- Backup strategy implemented

## Development Workflow

### Code Quality Gates

- All code must pass linting (PHPStan, PHP_CodeSniffer)
- Zero security vulnerabilities (checked by Composer)
- All tests must pass before merge
- Code review approval required
- No hardcoded secrets or credentials

### Git Workflow

- Feature branches for all work
- Descriptive commit messages
- Pull requests with detailed descriptions
- Automated testing on all PRs
- Squash commits before merge

### Documentation

- API documentation using OpenAPI/Swagger
- Database schema documentation
- Architecture decision records (ADRs)
- Setup instructions in README
- Troubleshooting guide

## Tech Stack Enforcement

- **Language**: PHP 8.3+
- **Framework**: Laravel 11
- **Database**: MySQL 8+
- **Authentication**: Laravel Sanctum or JWT
- **Caching**: Redis
- **Testing**: PHPUnit, Pest PHP
- **API Style**: REST with consistent JSON responses
- **Deployment**: Docker, Nginx, CI/CD pipeline

## Governance

This constitution supersedes all other practices. All development must comply with these principles. Changes to this constitution require documentation and team approval.

**Version**: 1.0.0 | **Ratified**: May 3, 2026 | **Last Amended**: May 3, 2026
