# Copilot Instructions

## Project Structure

This project follows the principles of Domain-Driven Design (DDD), Test-Driven Development (TDD), Clean Architecture, and SOLID. It is built using Symfony and Doctrine ORM. Below is the general structure of the project and how each layer should be organized:

### Folder Structure
- **<Bounded Context>\Domain**: Contains the core business logic and entities. Should not depend on external libraries.
- **<Bounded Context>\Application**: Contains use cases and application-specific logic. Orchestrates the domain layer.
- **<Bounded Context>\Infrastructure**: Handles external concerns such as database access and APIs. Uses Doctrine ORM for database interactions.
- **<Bounded Context>\UserInterface**: Handles API requests and responses. Should delegate logic to the application layer.

### Layers and Responsibilities
- **Domain Layer**:
  - Contains entities, value objects, and business rules.
  - Must be independent of other layers and external libraries.
- **Application Layer**:
  - Contains use cases and application logic.
  - Orchestrates interactions between the domain layer and other layers.
- **Infrastructure Layer**:
  - Implements repositories, external services, and integrations.
  - Uses Doctrine ORM for data persistence.
- **UserInterface Layer**:
  - Implements controllers and processors to handle requests and responses.
  - Should remain thin, delegating logic to the application layer.

### Design Principles
- **DDD**: Keep the domain layer independent and focused on business logic.
- **TDD**: Write tests before implementing features.
- **Clean Architecture**: Ensure dependencies point inward.
- **SOLID**: Apply principles to ensure scalable and maintainable code.

### Testing
- Write unit tests for all layers.
- Use PHPUnit for testing.
- Mock dependencies to isolate the unit under test.

### Symfony and API Platform
- Use Symfony's dependency injection to manage services.
- Configure services in `services.yaml` or use autowiring.
- Use Doctrine mapping files for entity mapping.

### Notes
- Validate suggestions against the project's principles.
- Ensure suggestions align with Symfony and API Platform best practices.