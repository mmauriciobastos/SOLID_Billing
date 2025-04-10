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

### Testing Guidelines

### Test Structure and Organization

1. **Test Constants**
   - Define test data as private constants at the class level
   - Use descriptive constant names with appropriate prefixes (VALID_, INVALID_)
   - Group related constants together
   ```php
   private const VALID_EMAIL = 'john@example.com';
   private const INVALID_EMAIL = 'invalid-email';
   ```

2. **Test Method Naming**
   - Follow the pattern: should_[expected behavior]_when_[condition]
   - Make names clear and descriptive
   - Examples:
     ```php
     public function should_return_auth_token_when_credentials_are_valid()
     public function should_throw_invalid_credentials_when_password_is_wrong()
     ```

3. **AAA Pattern**
   - Structure tests using the Arrange-Act-Assert pattern
   - Use comments to clearly separate sections
   ```php
   // Arrange
   $command = new LoginCommand($email, $password);
   
   // Act
   $result = $handler->__invoke($command);
   
   // Assert
   $this->assertEquals($expected, $result);
   ```

4. **Test Annotations**
   - Use PHPUnit annotations for better organization
   - Group related tests using @group
   - Mark test methods with @test
   ```php
   /**
    * @test
    * @group authentication
    * @group login
    */
   public function should_validate_user_credentials(): void
   ```

### Test Implementation Guidelines

1. **Mock Dependencies**
   - Create mocks for all dependencies in setUp()
   - Configure mock behavior specifically for each test
   - Use type-hinting for better IDE support

2. **Test Coverage**
   - Test both successful and failure scenarios for each use case
   - Include edge cases and boundary conditions (null values, empty strings, invalid formats)
   - Test one behavior per test method following Single Responsibility Principle
   - Write unit tests for all layers:
     - Domain: Test entities, value objects, and domain services
     - Application: Test use cases and application services
     - Infrastructure: Test repositories and external service integrations
     - UserInterface: Test API endpoints and request/response handling
   - Test error scenarios and exception handling
   - Test validation rules and business constraints

3. **Assertion Best Practices**
   - Use specific assertions that clearly convey intent:
     ```php
     // Instead of:
     $this->assertTrue($result === $expected);
     // Use:
     $this->assertSame($expected, $result);
     ```
   - Test exceptions with expectException() and verify exception messages:
     ```php
     $this->expectException(InvalidCredentials::class);
     $this->expectExceptionMessage('The password is incorrect');
     ```
   - Provide meaningful assertion messages for better debugging:
     ```php
     $this->assertEquals(
         $expected,
         $result,
         'Authentication should return valid JWT token'
     );
     ```
   - Use assertInstanceOf() for type checking
   - Test collections with assertCount() and assertContains()
   - Use data providers for testing multiple scenarios

4. **Test Data**
   - Use meaningful test data that represents real business scenarios
   - Create constants for common test values:
     ```php
     private const VALID_CREDENTIALS = [
         'email' => 'john@example.com',
         'password' => 'StrongP@ss123'
     ];
     ```
   - Create helper methods for complex test data setup:
     ```php
     private function createValidClient(): Client
     {
         return Client::create(
             FirstName::fromString(self::VALID_FIRST_NAME),
             LastName::fromString(self::VALID_LAST_NAME),
             Email::fromString(self::VALID_EMAIL)
         );
     }
     ```
   - Use data providers for testing multiple scenarios:
     ```php
     /**
      * @dataProvider invalidEmailProvider
      */
     public function testInvalidEmails(string $email)
     ```
   - Keep test data isolated between tests using setUp() and tearDown()
   - Use factories or builders for complex object creation

### Symfony and API Platform Best Practices

1. **Service Configuration**
   - Use Symfony's dependency injection for service management
   - Configure services in `services.yaml` following conventions:
     ```yaml
     services:
         _defaults:
             autowire: true
             autoconfigure: true
         
         App\BoundedContext\:
             resource: '../src/BoundedContext/*'
             exclude:
                 - '../src/BoundedContext/Domain/Entity'
                 - '../src/BoundedContext/Domain/ValueObject'
     ```
   - Use constructor injection over setter injection
   - Tag services appropriately for message handlers and event subscribers

2. **API Platform Configuration**
   - Use attributes for API resource configuration:
     ```php
     #[ApiResource(
         shortName: 'Resource',
         operations: [
             new GetCollection(),
             new Post(
                 validationContext: ['groups' => ['create']],
                 security: "is_granted('ROLE_USER')"
             )
         ]
     )]
     ```
   - Configure serialization groups properly:
     ```php
     #[Groups(['read', 'write'])]
     private string $property;
     ```
   - Use custom providers and processors for complex operations
   - Implement proper security with voters and attributes

3. **Doctrine Integration**
   - Use XML mapping files for entities:
     ```xml
     <entity name="App\Domain\Entity\Example">
         <id name="id" type="string" length="36"/>
         <field name="name" type="string" length="255"/>
     </entity>
     ```
   - Configure custom types for value objects
   - Use repositories for complex queries
   - Configure proper indexing and constraints

### Notes and Best Practices
- Follow SOLID principles in all layers
- Keep the domain layer pure and independent
- Use value objects for domain concepts
- Handle errors and exceptions appropriately
- Document API endpoints and data structures
- Follow security best practices
- Use proper logging and monitoring
- Maintain backwards compatibility
- Write clear and meaningful commit messages