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

### Auth

- `POST /api/register` registers a user and returns a token.
- `POST /api/login` returns a Sanctum token.
- `POST /api/logout` revokes the current token (requires `auth:sanctum`).

### Core Endpoints

- `GET /api/user` returns the authenticated user via `UserResource`.
- `GET /api/user/stats` returns aggregate report stats for the authenticated user.
- `GET /api/reports` lists reports.
- `POST /api/reports` creates a report.
- `GET /api/reports/{report}` returns a single report.
- `PATCH /api/reports/{report}` updates a report.
- `DELETE /api/reports/{report}` deletes a report.
- `GET /api/programs` returns available programs.

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

If your frontend app lives in `/zeroday-frontend`, configure it to call this API with a bearer token and handle Laravel's `data` wrapper.

### Base API Setup

- API base URL: `http://localhost:8000/api`
- Auth header: `Authorization: Bearer <token>`

Example Axios client:

```ts
import axios from 'axios';

export const apiClient = axios.create({
	baseURL: 'http://localhost:8000/api',
});

apiClient.interceptors.request.use((config) => {
	const token = localStorage.getItem('token');

	if (token) {
		config.headers.Authorization = `Bearer ${token}`;
	}

	return config;
});
```

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

Backend now returns `level` on `/api/user`. If missing in older payloads, derive from reputation:

```ts
const level = user.level ?? Math.floor(Number(user.reputation ?? 0) / 100);
```

## Project Docs

- DeepWiki: [https://deepwiki.com/Ay981/zeroday](https://deepwiki.com/Ay981/zeroday)

## Security Hardening Checklist

The following items should be addressed before production deployment:

- Restrict report visibility in `ReportPolicy` (`view` and `viewAny` are currently permissive).
- Avoid exposing reporter email in report payloads unless explicitly required.
- Add rate limiting to `POST /api/login` to reduce brute-force risk.
- Set a non-null Sanctum token expiration in `config/sanctum.php`.
- Review whether `GET /api/programs` should remain public.

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
