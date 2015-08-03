Nette Addons Portal
===================


Installation
------------

**Run in console**

```
# Clone repository
git clone https://github.com/nette/web-addons.nette.org.git
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
3. Run migrations `php migrations/run.php structures test-data --reset`


Tests
-----
You can run tests via CLI `./tests/run-tests.sh tests/cases`.

[![Dependencies Status](https://www.versioneye.com/user/projects/5356938ffe0d078a76000257/badge.png)](https://www.versioneye.com/user/projects/5356938ffe0d078a76000257)
[![Build Status](https://travis-ci.org/nette/web-addons.nette.org.png?branch=master)](https://travis-ci.org/nette/web-addons.nette.org)
