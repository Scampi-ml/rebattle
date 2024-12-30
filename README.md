# ReBattle v1.0.0 documentation

```
4 September 2021
```

# Requirements

### ReBattle some server requirements for web hosting:

### 1. PHP version 7.2.9 or higher

### 2. Composer 1.0 or higher

### 3. PDO PHP Extension (and relevant driver for the database you want to connect to)

### 4. cURL PHP Extension

### 5. OpenSSL PHP Extension

### 6. Mbstring PHP Extension

### 7. ZipArchive PHP Extension

### 8. GD PHP Extension

### 9. SimpleXML PHP Extension

### 10. Apache Mod Rewrite module

### 11. Hosting with valid SSL certificate (for stripe payments)


# Installation

### 1. Copy all files from folder web to / web direcetory.

### 2. Set Chmod permissions to 755 for /web_directory/storage (also all sub-directories

### and files)

### 3. Import SQL dump (file: mysqldump.sql) into your MySQL database.

### 4. Update /your_web_directory/.env file (more information in section Configuration ).

### 5. Go to https://yourwebgame/control login as admin and password changeme

### 6. Change your username , email and password Your account

### https://yourwebgame/control/backend/users/myaccount

### 7. Delete demo users or other demo content that you don’t need.

## CMS (BASE) CONFIGURATION

### ReBattle uses WinterCMS (Forked from OctoberCMS), WinterCMS is Laravel based CMS

### system. You can use a lot of features from Laravel, as well you can check WinterCMS and

### OctoberCMS for documentation for more info, and also you can install 3rd party plugins, to

### easily and fast extend your project even more.


# Configuration

To change game configuration you can edit .env file.

## GENERAL

To generate key you must run artisan command: **php artisan key:generate**

EXAMPLE (do not copy-paste from image):

APP_DEBUG=false
APP_URL=https://your.domain
APP_KEY=generated_key

## DATABASE

For example, the database connection can be specified with these variables.

DB_CONNECTION=mysql

DB_HOST=127.0.0.
DB_PORT= 3306
DB_DATABASE=database
DB_USERNAME=root
DB_PASSWORD=


## GAME CONTENT

All game-related configuration can be edited in **/config/re.php** file as well you can use **.env** file environment
variables. More information about stripe setup can be found in section “Stripe integration”.

Game configuration example:


# Payments (Stripe integration)

## INTRODUCTION

We have made stripe integration as easy as possible, that's why we offer only two “products” that can be sold
to payers: Coins and Gems - two in-game currencies. All other special/premium digital goods can be sold on
the game market. To increase player orders you can limit game coin and gem awards.

## STRIPE WEBHOOK

Sign in into your Stripe account, and go to Developers -> Webhooks section, create new webhook with
following data:

**URL:** https://your.domain/stripe-webhook

**Events:** checkout.session.completed

Once you have created webhook copy Webhook
signing secret (key)

Paste webhook secret in **.env** file as

RE_STRIPE_WEBHOOK_SECRET= **_wh_secret_**


## STRIPE PIRVATE KEY

Go to Developers -> API Keys and click **show key**

Copy secret key

Paste secret key in **.env** file as

RE_STRIPE_PRIVATE_KEY= **_secret_key_**


## STRIPE PRODUCTS (COINS & GEMS)

Go to Products section and click **+ Add product button**

Paste product details in **.env** file

For Coins:

RE_COINS_PRICE_ID= **_price_id_from_stripe_**
RE_COINS_BUNDLE_PRICE=5.
RE_COINS_BUNDLE_SIZE=

or for Gems:

RE_GEMS_PRICE_ID= **_price_id_from_stripe_**
RE_GEMS_BUNDLE_PRICE=5.
RE_GEMS_BUNDLE_SIZE=


# Licence, Support & Extras



## SUPPORT

This game is developed by CodeTool team and if you need any support, please contact us.

## GAME ASSETS

ReBattle includes 10 avatars, 40 craft assets, 35 wearable assets + 150 wearable color variations
In total 235 game assets can be used in-game.

ReBattle official game artist is [Timur](https://www.fiverr.com/timurkhafzet)


