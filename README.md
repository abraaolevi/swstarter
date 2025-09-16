# SWStarter Project

## Project Overview
SWStarter is a web application designed to provide efficient search and management of resources. The project is built using Laravel for the backend and React with Vite for the frontend.

## How to Run the Project

### Production Environment
To run the project in production mode:
1. Use the following command:
   ```bash
   docker compose -f docker-compose.prod.yaml up --build
   ```
2. Verify that the backend is running on `http://localhost:8000` and the frontend on `http://localhost` (port 80).

### Development Environment
1. Ensure you have Docker installed on your machine.
2. Clone the repository and navigate to the project directory.
3. Start the development environment:
   ```bash
   docker compose up -d
   ```
4. Verify that the backend is running on `http://localhost:8000` and the frontend on `http://localhost:5173`.
5. Install backend dependencies:
   ```bash
   docker-compose exec backend composer install
   ```
6. Run database migrations:
   ```bash
   docker-compose exec backend php artisan migrate
   ```
7. Install frontend dependencies:
   ```bash
   docker-compose exec frontend npm install
   ```
8. Access the application at `http://localhost:5173` (frontend) and `http://localhost:8000` (backend).


### Stats Endpoint
To test the stats endpoint, you can use the following `curl` command:
```bash
curl -X GET http://localhost:8000/api/stats
```

## How to Run Tests

To execute the tests for the backend:
1. Ensure the Development backend container is running.
2. Run the following command:
   ```bash
   docker-compose exec backend php artisan test
   ```

## Project Architecture

### Backend
- **Framework**: Laravel
- **Database**: PostgreSQL
- **Caching**: Used for search results and details.
- **Worker and Scheduler**: A worker is implemented to process background jobs, and a scheduler is used to periodically calculate and update statistics.

### Frontend
- **Framework**: React with Vite.
- **Styling**: CSS Modules for scoped styling and pure CSS for simplicity.
- **State Management**: TanStack Query for state and server data management.
- **Layout**: Responsive design for usability on desktops, tablets, and mobile devices.

## Technical Decisions

**BaseSearchController**: A base controller was created to standardize the logic for search and detail endpoints for both Films and People, reducing code duplication and centralizing validation, logging, and error handling.

**StarWars Service & DTOs**: All Star Wars-related Data Transfer Objects (DTOs) and service logic are grouped in the `Services/StarWars` folder. This keeps the domain logic organized and makes it easier to maintain and extend features related to the Star Wars API.

**Stats Caching Strategy**: The stats endpoint uses caching to avoid recalculating statistics on every request. If the stats are not available or outdated, a background job is triggered to refresh them, and the endpoint returns a 202 status until the new stats are ready. This improves performance and scalability for frequent stats queries.