Nette Addons Portal
===================


Installation
------------

**Run in console**

```
# Clone repository
git clone git://github.com/nette/addons.nette.org.git
cd addons.nette.org

# Install dependencies via Composer
composer install

# Create local config
cp ./app/config/config.local.template.neon ./app/config/config.local.neon

# Edit your local configuration (use your favorite editor)
nano ./app/config/config.local.neon
```

**Set up database**

1. Open Adminer (or whatever tool you use)
2. Create database
3. Import `app/model/db/current-schema.sql` and `app/model/db/data.sql`


Tests
-----
You can run tests via web browser or via CLI.

* **Browser**: Open `http://localhost/addons.nette.org/tests/` and click on "START".
* **CLI**: You should already know what to do.

[![Build Status](https://secure.travis-ci.org/nette/addons.nette.org.png?branch=master)](http://github.com/nette/addons.nette.org)
