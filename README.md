# Hello Laravel

> The purpose of this repository is to try laravel with RESTful concepts and use reddit's api to share a post and retrieve  comments to local db.

## How to
- Create an script application on [Reddit](https://www.reddit.com/prefs/apps) to get the keys
- Rename .env.example to .env and check all vars before continue
- For development add Volume in docker-compose (look for old commits)
```bash
$ docker-compose up --build
$ php artisan migration
$ php artisan db:seed
$ curl localhost:8000
```

## Routes
| Method   | URI                 | Name        | Action                           | Middleware   |
|----------|---------------------|-------------|----------------------------------|--------------|
| GET-HEAD | /                   |             | Closure                          | web          |
| POST     | api/v1/login        |             | App\Http\Controllers\Auth@store  | api          |
| GET-HEAD | api/v1/posts        | posts.index | App\Http\Controllers\Posts@index | api,auth:api |
| POST     | api/v1/posts        | posts.store | App\Http\Controllers\Posts@store | api,auth:api |
| GET-HEAD | api/v1/posts/{post} | posts.show  | App\Http\Controllers\Posts@show  | api,auth:api |
| GET-HEAD | api/v1/users/{user} | users.show  | App\Http\Controllers\Users@show  | api,auth:api |

## Postman collection
- Import the file "hello-laravel.postman.json" to your postman.

## Observation
- All new posts will be created in current user "dashboard"
