# MyPosts

![CodeSniffer-PSR-12](https://github.com/IlyaMur/myposts_app/workflows/CodeSniffer-PSR-12/badge.svg)
![PHPUnit-Tests](https://github.com/IlyaMur/myposts_app/workflows/PHPUnit-Tests/badge.svg)
[![Maintainability](https://api.codeclimate.com/v1/badges/0273c0de648a6f356cf3/maintainability)](https://codeclimate.com/github/IlyaMur/myposts_app/maintainability)

**[🇷🇺 Russian readme](https://github.com/IlyaMur/myposts_app/blob/master/README.md)**

**Table of contents**
  - [Overview](#Overview)
  - [Install](#Install)
    - [Docker build](#docker-build)
    - [Local installation](#local-installation)
    - [Configuration](#configuration)
    - [Used libraries](#used-libraries)
  - [How It Works](#how-it-works)
    - [Authentication and authorization system](#authentication-and-authorization-system)
    - [Posts](#posts)
    - [Comments](#comments)
    - [Hashtags](#hashtags)
    - [Image storing](#image-storing)
    - [Templates](#templates)
    - [Administration](#administration)
    - [Errors](#errors)

## Overview  

**MyPosts** is a blog application based on the [PHP On Rails](https://github.com/IlyaMur/php_on_rails_mvc) framework.
The blog was created in the learning process, but has rich functionality.

The application was deployed to the Heroku service. 
MyPosts is available at http://myposts-app.herokuapp.com.

The application currently has:
- Full-fledged systems of authentication and authorization of users.
- Account activation and password reset by mail (based on the MailJet service).
- Storage of user data, both locally and on a third-party service (AWS S3).
- Comment system.
- Hashtags.
- User profiles.
- CAPTCHA and basic spam protection.
- Administrative component.

Most of the functionality was written from scratch, ready-made solutions were avoided whenever possible.
The template engine [Twig](https://twig.symfony.com/) was chosen as the basis for the views, as a flexible and safe solution with a concise syntax.

![Main page](https://i.imgur.com/AUtFld3.png)   

## Install  

First of all, you need to clone the repository  

    $ git clone https://github.com/IlyaMur/myposts_app.git  
    $ cd ,my_posts_app

And prepare `.env` file  

    $ make env-prepare

### Docker build

Optionally, change the connection parameters in the `.env` file.

```dotenv
MYSQL_USER='user'
MYSQL_HOST='mariadb'
APACHE_DEFAULT_PORT='80'
MYSQL_PASSWORD='testpassword'
```

Build and run the application

    $ make docker-start # build the project and upload the db dump to it  
    $ make docker-stop  # stop and remove containers  
    $ make docker-bash  # bash session in docker container
    $ make docker-test  # run tests in docker container

By default the app will be available at `http://localhost`

### Local installation

`PHP >= 8.0`

To install dependencies:  

    $ make install

Configure your web server to have the `public/` folder as the web root.

Import SQL from the `database/myposts.sql` file into the selected DBMS 

Pretty URLs are enabled using web server rewrite rules. An [.htaccess](public/.htaccess) file is included in the `public` folder.

### Used Libraries

Despite the fact that in the process of writing the application the goal was to create functionality from scratch, there are some dependencies in the MyPosts app:

- Twig Template Engine
- SDK Mailjet
- SDK AWS
- Gregwar/Captcha
- vlucas/phpdotenv

## How It Works

### Configuration  

Configuration settings are available in the file [config.php](config/config.php)

Default settings include:

- Database connection data.
- Error logging settings.
- Settings to choose between local and remote storage of user's upload images.
- Settings for displaying/hiding error details.
- A secret key for hashing tokens.

The corresponding constants are available to override these settings in the configuration file.

### Authentication and authorization system

MyTasks implements a user registration and authentication system from scratch.

The authentication form is validated both on the server side and on the client side.  
After registration, an email with an activation link is sent to the user's email address.  
Created the ability to reset an existing password by mail. The reset token has an expiration date.  

Emails were sent using the MailJet service and the service class [Mail](src/Service/Mail.php), templates for working with passwords and user tokens are available in [src/Views/Password](src/Views/Password)

The user login is available via [RememberedLogin](src/Models/RememberedLogin.php). The login token also has an expiration date.  
To interact with sessions and cookies, the [Auth](src/Service/Auth.php) service class is used, which provides tools for working with user authorizations.

### Posts

The basis of the blog - user posts. They are conveniently displayed on the main page, pagination is also available.  
Users can add images to posts, edit their own posts, and comment on other people's and their own posts.  
Post content validation occurs both on the client (using the JS library and HTML5 validation) and on the server in the [Post](src/Models/Post.php) model, validation errors are returned to the user.

### Comments

Users can leave comments under their own and other people's posts. To work with comments, the [Comment](src/Models/Comment.php) model and the [Comments](src/Controllers/Comments.php) controller have been created.  
Anonymous sending of comments is also implemented, but for this it is necessary to pass the verification through the CAPTCHA.  
User comments are available in his profile, where they are conveniently displayed through pagination.  
Comments are also validated and checked against XSS.

### Hashtags

Like any modern blog, MyPosts supports hashtags. Hashtags are available in posts and are automatically assigned to them.
The logic for working with hashtags is in the [Hashtag](src/Controllers/Hashtag.php) model.
The last 10 added hashtags are available on the main page. When you click on a hashtag, posts related to them are displayed.

### Image storing

There are two options for storing images.
- Image storage based on AWS S3 service. Using the AWS SDK.
- Store images locally.

To use AWS S3, you need to enter your account credentials in the [config.php](config/config.php) file and set the `AWS_STORING` constant to `true`.
To work, use the [S3Helper](src/Service/S3Helper.php) class, which uses the SDK and provides an interface for interacting with cloud storage.

To store data locally, you need to set `AWS_STORING` to `false`. Pictures will be saved to the `public/upload/` directory.

All images are validated before uploading to the server.

### Templates

Views are organized using the [Twig](https://twig.symfony.com/) templating engine, which supports template inheritance.  
The templates are in the `src/Views` directory, the base template is [base.html.twig](src/Views/base.html.twig).  
To reuse code and improve readability, frequently used View elements have been moved to the `src/Views/partials` directory.

For a nice and concise appearance, the CSS Framework - Bootstrap was chosen.

### Administration

The blog administrator has his own control panel, interaction with which takes place in a separate namespace `Ilyamur\PhpMvc\Controllers\Admin`.
The administrator sees detailed information about the created posts and can moderate them.

### Errors

Errors are converted to exceptions. The handlers are:
```
set_error_handler('Ilyamur\PhpMvc\Service\ErrorHandler::errorHandler');
set_exception_handler('Ilyamur\PhpMvc\Service\ErrorHandler::exceptionHandler');
```

If the `SHOW_ERRORS` constant (configurable in [config.php](config/config.php)) is set to `true`, full error's details will be displayed in the browser in case of an exception or error.  
If `SHOW_ERRORS` is set to `false` only the generic message from the templates [404.html.twig](src/Views/404.html.twig) or [500.html.twig](src/Views/500.html.twig) will be shown depending on the error.  
Detailed information in this case will be logged in the `logs/` directory.  
