<h1 align="center">
  API-FOLIO
</h1>

## About

The API forms the back office of my websites.
It centralizes the management of data and key functionalities, and also serves as my CRM to help me stay organized.
<br />

L’API constitue le back-office de mes sites.
Elle centralise la gestion des données et des fonctionnalités clés, et me sert également de CRM pour m'organiser.

### View

<p>API et BackOffice pour Api-Folio</p>

![home](public/img/home1.png)
![article](public/img/home2.png)
![article](public/img/home3.png)

### 🛠 Installation & Set Up

1. Install dependencies

```sh
composer install
```

2. Run migration and factory

```sh
./vendor/bin/sail migrate
```

```sh
./vendor/bin/sail artisan migrate:fresh --seed
```

3. Start the development server

```sh
./vendor/bin/sail up
```

4. Access the API

```
http://127.0.0.1:8001
```

### 📚 Documentation

[API Documentation](https://api-folio.up.railway.app/api/documentation)
