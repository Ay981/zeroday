<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## ZeroDay API Notes

This project exposes an authenticated bug-reporting API using Laravel Sanctum.

## Daily Starter

Use these commands each day to run local development:

### Backend API (this repo)

```bash
composer run dev:api-https
```

This starts:

- Laravel API at `http://127.0.0.1:8000`
- HTTPS proxy at `https://api.zeroday.test`

### Frontend (separate repo)

```bash
npm run dev
```

Expected frontend URL:

- `https://app.zeroday.test:5173`

### Auth

- `GET /sanctum/csrf-cookie` initializes the CSRF + session cookies.
- `POST /api/v1/register` registers and starts a session.
- `POST /api/v1/login` starts a session.
- `POST /api/v1/logout` destroys the current session (requires `auth:sanctum`).


### Core Endpoints

- `GET /api/v1/user` returns the authenticated user via `UserResource`.
- `GET /api/v1/user/stats` returns aggregate report stats for the authenticated user.
- `GET /api/v1/reports` lists reports.
- `POST /api/v1/reports` creates a report.
- `GET /api/v1/reports/{report}` returns a single report.
- `PATCH /api/v1/reports/{report}` updates a report.
- `DELETE /api/v1/reports/{report}` deletes a report.
- `GET /api/v1/programs` returns available programs.

### API Response Shape

Resources use the standard `data` wrapper.

Example report response:

```json
{
	"data": {
		"id": 195,
		"title": "...",
		"slug": "...",
		"program_id": 1,
		"program": {
			"id": 1,
			"name": "Tesla Security",
			"multiplier": 2.5,
			"description": "..."
		},
		"severity": "Low",
		"description": "...",
		"status": "Open",
		"created_at": "2026-04-10T15:10:17.000000Z",
		"submitted_by": {
			"id": 10,
			"name": "Mrs. Elvera Morar",
			"email": "hacker@example.com",
			"reputation": 100,
			"level": 1
		}
	}
}
```

## Frontend Integration (`/zeroday-frontend`)

If your frontend app lives in `/zeroday-frontend`, configure it to call this API using Sanctum cookie auth and handle Laravel's `data` wrapper.

### Base API Setup

- API origin: `https://api.zeroday.test`
- API base URL: `https://api.zeroday.test/api/v1`
- Always send credentials (`withCredentials: true`)
- Call `/sanctum/csrf-cookie` before login/register

Example Axios client:

```ts
import axios from 'axios';

export const apiClient = axios.create({
	baseURL: 'https://api.zeroday.test/api/v1',
	withCredentials: true,
	withXSRFToken: true,
	headers: {
		Accept: 'application/json',
		'X-Requested-With': 'XMLHttpRequest',
	},
});

export const getCsrfCookie = async () => {
	await axios.get('https://api.zeroday.test/sanctum/csrf-cookie', {
		withCredentials: true,
		headers: {
			Accept: 'application/json',
			'X-Requested-With': 'XMLHttpRequest',
		},
	});
};
```

Typical auth flow:

1. `await getCsrfCookie()`
2. `await apiClient.post('/login', credentials)`
3. `await apiClient.get('/user')`

### Reading Wrapped Resources

For endpoints that return Laravel Resources, read from `response.data.data`.

```ts
const res = await apiClient.get('/user');
const user = res.data.data;
```

```ts
const res = await apiClient.get('/reports');
const reports = res.data.data;
```

### Safe Fallback for Mixed Payloads

If an endpoint might return either wrapped or flat JSON:

```ts
const payload = response.data?.data ?? response.data;
```

### Level Display Logic

Backend now returns `level` on `/api/v1/user`. If missing in older payloads, derive from reputation:

```ts
const level = user.level ?? Math.floor(Number(user.reputation ?? 0) / 100);
```

## Project Docs

- DeepWiki: [https://deepwiki.com/Ay981/zeroday](https://deepwiki.com/Ay981/zeroday)



Recommended command for clean local reset:

```bash
php artisan migrate:fresh --seed
```

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
