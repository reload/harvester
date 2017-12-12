Harvester
=========

Harvester serves as a backup of all our Harvest data and also features a neat API that Timelord (for one) fetches data from.


1) Installing
-------------

Clone the repo

    git clone https://github.com/reload/harvester.git

Setup with Docker compose

    docker-compose up

This also fetches all data from Harvest if everything is set up correctly in the `docker-compose` process which may take up to 5 minutes.


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
