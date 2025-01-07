# Product filter

To configure the project, specify the database communication settings in the `.env` configuration file.

## Project setup

```sh
composer install
```

### Creating all models and migrations

```sh
php artisan make:model Category -m
```
```sh
php artisan make:model Product -m
```
```sh
php artisan make:model Attribute -m
```
```sh
php artisan make:model AttributeValue -m
```
```sh
php artisan make:migration create_product_attributes_table
```
```sh
php artisan make:migration create_category_attributes_table
```

### Running migrations

```sh
php artisan migrate
```

### Start your local development server

```sh
php artisan serve
```

### Testing in Postman

Get parent categories
```sh
GET http://127.0.0.1:8000/api/categories
```

Navigation by category
```sh
GET http://127.0.0.1:8000/api/categories/1/subcategories?offset=0&limit=10

Key: offset, Value:0
Key: limit, Value:10
```

Sorting products based on filters
```sh
GET http://127.0.0.1:8000/api/products/2/subcategories/filter?filters[1][]=1&filters[1][]=2&offset=0&limit=10

Key: filters[1][], Value:1
Key: filters[1][], Value:2
Key: offset, Value:0
Key: limit, Value:10
```
