Harvester
=========

Welcome to Harvester, a service which fetch data from HarvestApp and have a simple API to get data out. This is so much
faster than the actual HarvestApp API since everything is stored locally.

1) Installing
-------------

When it comes to installing Harvester you will need to clone the repository from GitHub.

    `git clone https://github.com/reload/harvester.git`

    `cd harvester/`

    `docker-compose run  composer install`

Composer will install Harvester and all its dependencies. And you will need to configure database and the system.

Install the database

    app/console doctrine:database:create

Import the database schema

    app/console doctrine:schema:create

Setup your apache/nginx virtualhost, and a few urls is now available

[http://yourho.st/api/doc]()

[http://yourho.st/admin]()

2) Configuration
----------------

For the HarvestApp API fetcher to work you need to configure the API info in app/config/

    vim app/config/parameters.yml

3) Usage

There is a commmand line command available for fetching data from the HarvestApp API.
This could be executed from crontab

To see the command

    app/console help harvester:fetch

To run the command without parameters, will fetch everything from Harvest (could tak several minutes)

    app/console harvester:fetch

To fetch updated data from this month

    app/console harvester:fetch `date "+%Y%m01"`

To fetch that has been updated since yesterday

    app/console harvester:fetch --updated-yesterday

To delete entries and repopulate within a given timespan

    app/console harvester:refresh `date "+%Y%m01"`

To delete entries and repopulate within a static amount of days

    app/console harvester:refresh --days=30
