# Recruitment Management System

A full-stack recruitment management system built to simplify the process of creating vacancies, receiving applications, screening candidates, managing interviews, and tracking recruitment progress.

The system is designed for organizations that manage recruitment across a company and its subsidiaries. Applicants can create a reusable profile and use it when applying for different vacancies instead of entering the same information repeatedly.

## Overview

The Recruitment Management System provides a centralized platform for both applicants and recruitment teams.

Applicants can:

* Create an account and manage their profile
* Add education, work experience, and skills
* Upload their CV and other required documents
* Browse available vacancies
* Search and filter vacancies
* Apply for vacancies online
* Save an application as a draft and continue later
* Track the status of their applications

Recruitment teams can:

* Create and manage vacancies
* Assign vacancies to departments and subsidiaries
* Review applications
* Screen and shortlist candidates
* Manage interview schedules
* Record interview evaluations
* Track candidates through the recruitment process
* View recruitment information and reports

## Main Features

### Applicant Management

* Simple applicant registration
* Reusable applicant profile
* Education history
* Work experience
* Skills
* CV and document upload
* Application history
* Application status tracking

### Vacancy Management

* Create, edit, and manage vacancies
* Assign vacancies to subsidiaries and departments
* Define required education and experience
* Define required skills
* Add vacancy-specific questions
* Publish and close vacancies
* Manage vacancy status

### Application Management

* Online vacancy application
* Reuse applicant profile information
* Save applications as drafts
* Submit completed applications
* Application review
* Candidate screening
* Shortlisting
* Recruitment stage tracking
* Internal recruiter notes

### Interview Management

* Schedule interviews
* Add interview date and time
* Define interview type and location
* Assign interview panel members
* Record interview evaluations
* Add scores and comments
* Track interview results

### Role-Based Access Control

The system provides different permissions based on the user's role.

| Role                   | Main Responsibilities                                                                       |
| ---------------------- | ------------------------------------------------------------------------------------------- |
| Applicant              | Manage profile, browse vacancies, apply, and track applications                             |
| Recruiter / HR Officer | Manage vacancies, review applications, screen candidates, and manage recruitment activities |
| Hiring Manager         | Review candidates and participate in shortlisting and interviews                            |
| HR Manager             | Manage recruitment activities and monitor recruitment processes                             |
| System Administrator   | Manage users, roles, system settings, and access                                            |

## Recruitment Workflow

The recruitment process follows a simple workflow:

```
Vacancy Draft
     ↓
Pending Approval
     ↓
Approved
     ↓
Published
     ↓
Applications Received
     ↓
Under Review
     ↓
Shortlisted
     ↓
Interview
     ↓
Selected / Not Selected
     ↓
Closed
```

An application can also remain in **Draft** before the applicant submits it.

Applicant Workflow

```
Register
   ↓
Create Profile
   ↓
Add Education / Experience / Skills
   ↓
Upload CV
   ↓
Browse Vacancies
   ↓
View Vacancy
   ↓
Apply
   ↓
Answer Vacancy Questions
   ↓
Review Application
   ↓
Submit
   ↓
Track Application Status
```

The application process is designed to minimize repeated data entry. Once an applicant has created their profile, the information can be reused for future applications.

System Architecture

The application follows a simple three-layer architecture:

```
┌──────────────────────────────┐
│          Frontend            │
│                              │
│ Vue 3 + Tailwind CSS         │
│ Applicant & HR Interfaces    │
└──────────────┬───────────────┘
               │
               │ REST API
               ▼
┌──────────────────────────────┐
│          Backend             │
│                              │
│ Laravel                      │
│ Authentication              │
│ Business Logic              │
│ Authorization               │
│ API Endpoints               │
└──────────────┬───────────────┘
               │
               │ Eloquent ORM
               ▼
┌──────────────────────────────┐
│           Database           │
│                              │
│ MySQL                       │
│ Users                       │
│ Applicants                  │
│ Vacancies                   │
│ Applications                │
│ Interviews                  │
│ Documents                   │
└──────────────────────────────┘
```

Technology Stack

Frontend

* Vue 3
* Tailwind CSS
* JavaScript
* Axios
* Vue Router

Backend

* Laravel
* PHP
* Laravel API
* Laravel Authentication and Authorization

Database

* MySQL

Development Tools

* Git
* GitHub
* Composer
* npm
* Vite

## Project Structure

A typical project structure is organized as follows:

```
recruitment-management-system/
│
├── frontend/
│   ├── src/
│   │   ├── components/
│   │   ├── views/
│   │   ├── layouts/
│   │   ├── router/
│   │   ├── services/
│   │   └── stores/
│   ├── public/
│   ├── package.json
│   └── vite.config.js
│
├── backend/
│   ├── app/
│   │   ├── Http/
│   │   ├── Models/
│   │   ├── Services/
│   │   └── Policies/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── routes/
│   │   └── api.php
│   ├── storage/
│   ├── .env.example
│   └── composer.json
│
└── README.md
```

> Adjust the folder structure above to match the actual structure of your repository.

## Database Design

The main entities in the system include:

```
Users
  │
  ├── Applicant Profile
  │      ├── Education
  │      ├── Work Experience
  │      ├── Skills
  │      └── Documents
  │
  └── Roles / Permissions

Subsidiary
  │
  └── Vacancy
          │
          ├── Required Skills
          ├── Questions
          └── Applications
                    │
                    ├── Answers
                    ├── Documents
                    ├── Recruitment Stages
                    └── Interviews
```

The database is designed around reusable applicant information so that the same profile can be used across multiple applications.

## Installation

### Prerequisites

Make sure the following are installed on your machine:

* PHP 8.2 or later
* Composer
* Node.js and npm
* MySQL
* Git

You can verify the installations with:

```
php -v
composer -V
node -v
npm -v
mysql --version
```

### 1. Clone the Repository

```
git clone https://github.com/maggie-ghub/Recruitment-Management-System
cd recruitment-management-system
```

### 2. Backend Setup

Go to the Laravel backend:

```
cd backend
```

Install PHP dependencies:

```
composer install
```

Create the environment file:

```
cp .env.example .env
```

For Windows PowerShell, you can use:

```
Copy-Item .env.example .env
```

Generate the Laravel application key:

```
php artisan key:generate
```

### 3. Configure the Database

Create a MySQL database, for example:

```sql
CREATE DATABASE recruitment_management;
```

Update the database settings in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=recruitment_management
DB_USERNAME=root
DB_PASSWORD=
```

Use your own MySQL username and password if they are different.

### 4. Run Database Migrations

```
php artisan migrate
```

If the project contains seeders:

```
php artisan db:seed
```

Or run migrations and seeders together:

```
php artisan migrate --seed
```

### 5. Start the Laravel Backend

```
php artisan serve
```

The backend will normally be available at:

```
http://127.0.0.1:8000
```

### 6. Frontend Setup

Open another terminal and navigate to the frontend:

```
cd frontend
```

Install JavaScript dependencies:

```
npm install
```

If the frontend uses environment variables, create the environment file:

```
cp .env.example .env
```

Example API configuration:

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

Start the Vue development server:

```
npm run dev
```

The frontend will normally be available at:

```
http://localhost:5173
```

## Environment Configuration

Do not commit your real `.env` files to GitHub.

The repository should contain an `.env.example` file with the required variables, for example:

```env
APP_NAME="Recruitment Management System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=recruitment_management
DB_USERNAME=root
DB_PASSWORD=

VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

Replace the values with the configuration required for your environment.

## Screenshots

Screenshots will be added here as the interface is finalized.

### Applicant Portal

![Applicant Dashboard](docs/screenshots/applicant-dashboard.png)

### Vacancy Listing

![Vacancy Listing](docs/screenshots/vacancy-list.png)

### Vacancy Details

![Vacancy Details](docs/screenshots/vacancy-details.png)

### Application Form

![Application Form](docs/screenshots/application-form.png)

### Recruiter Dashboard

![Recruiter Dashboard](docs/screenshots/recruiter-dashboard.png)

### Candidate Screening

![Candidate Screening](docs/screenshots/candidate-screening.png)

### Interview Management

![Interview Management](docs/screenshots/interview-management.png)

> Store screenshots in `docs/screenshots/` and update the filenames above to match the actual screenshots in the repository.

## User Roles and Permissions

### Applicant

Applicants can:

* Register and log in
* Manage their personal profile
* Add education and work experience
* Manage skills
* Upload documents
* Browse published vacancies
* Submit applications
* Save applications as drafts
* Track submitted applications

Applicants cannot access recruitment administration functions or other applicants' information.

### Recruiter / HR Officer

Recruiters can:

* Create and manage vacancies
* Review applications
* Search and filter candidates
* Screen applications
* Shortlist candidates
* Manage recruitment stages
* Schedule interviews
* Record recruitment notes

### Hiring Manager

Hiring managers can:

* Review candidates for assigned vacancies
* Participate in shortlisting
* View relevant candidate information
* Participate in interviews
* Submit interview evaluations

### HR Manager

HR managers can:

* Monitor recruitment activities
* Review vacancies and applications
* Monitor recruitment progress
* Manage recruitment decisions within their assigned scope
* Access recruitment reports

### System Administrator

System administrators can:

* Manage users
* Manage roles and permissions
* Manage subsidiaries and departments
* Configure system settings
* Monitor system activity
* Manage access to the system

## Security

Security is an important part of the system because applicant information and recruitment documents are sensitive.

The application includes or is designed to include:

* Authentication
* Role-based access control
* Authorization policies
* Password hashing
* Protected applicant documents
* Input validation
* CSRF protection
* Protection against SQL injection through Laravel's database layer
* Protection against unauthorized record access
* Audit logging for important actions
* Secure environment configuration

Sensitive configuration such as database credentials, application keys, and production secrets should never be committed to the repository.

## API

The Laravel backend exposes API endpoints for communication with the Vue frontend.

Examples of API areas include:

```
/api/auth
/api/users
/api/applicants
/api/vacancies
/api/applications
/api/interviews
/api/documents
/api/notifications
```

The exact endpoints may change as development continues.

## Development Guidelines

When contributing to the project, keep the following principles in mind:

* Keep controllers focused on HTTP handling.
* Put complex business logic in appropriate service classes.
* Use Form Requests for validation.
* Use Laravel Policies for authorization.
* Keep Vue components focused and reusable.
* Keep API communication inside dedicated service modules where appropriate.
* Avoid duplicating applicant information unnecessarily.
* Validate uploaded files before storing them.
* Never commit credentials or secrets.

## Current Scope

The first version focuses on the core recruitment process:

* Applicant registration
* Applicant profiles
* Vacancy management
* Online applications
* Candidate screening
* Shortlisting
* Interview management
* Application status tracking
* Role-based access control
* Basic recruitment reporting

## Future Improvements

Some features can be introduced in later versions without making the first release unnecessarily complex.

Planned possibilities include:

* CV parsing
* Improved candidate search
* Advanced recruitment analytics
* Online assessments
* More configurable recruitment workflows
* Additional notification options
* Improved reporting and data export

AI-based candidate matching and ranking are intentionally not part of the initial version. The first version focuses on providing a reliable and straightforward recruitment workflow.

## Contributing

Contributions and suggestions are welcome.

If you would like to contribute:

1. Fork the repository.
2. Create a feature branch.

```
git checkout -b feature/your-feature
```
3. Make your changes.
4. Test the changes locally.
5. Commit your changes.

```
git commit -m "Add your feature"
```

6. Push the branch.

```bash
git push origin feature/your-feature
```

7. Open a Pull Request.

License

This project is currently intended for internal use.

If a specific open-source license is required, add the appropriate license file and update this section.

Author
Mearg Gebremedhn

Software Engineer / Full-Stack Developer

* GitHub: https://github.com/maggie-ghub
* Portfolio: https://meargportfolio.netlify.app/
* LinkedIn: www.linkedin.com/in/mearggebremedhn

This project is being developed to simplify recruitment workflows and provide a better experience for both applicants and recruitment teams.
