![https://i.imgur.com/jdTgyyI.png](https://i.imgur.com/jdTgyyI.png)

# Harvester
![Docker Badge](https://img.shields.io/docker/automated/reload/harvester.svg) ![Docker Badge](https://img.shields.io/docker/build/reload/harvester.svg)

Welcome to Harvester, a service which fetch data from HarvestApp and have a simple API to get data out.


## Get started

#### 1. Create the config:

```
make create-config
```

#### 2. Fill in envs in `docker-compose.override.yml`:

```
HARVESTER_HARVEST_USER: harvest_account_email@email.com
HARVESTER_HARVEST_PASSWORD: secretpassword
# only use the actual name, e.g. "reload". *Not* the full url (reload.harvestapp.com)
HARVESTER_HARVEST_ACCOUNT: harvestapp_account_name
```

The credentials can be found in LastPass. Search for __"Harvester API"__.

#### 3. Get everything up and running. This takes ~30 minutes:

```
make up
```

When your teminal looks something likes this:

```
server_1  | *** Running /etc/my_init.d/00_regen_ssh_host_keys.sh...
server_1  | *** Running /etc/my_init.d/harvester...
server_1  | *** Running /etc/rc.local...
server_1  | *** Booting runit daemon...
server_1  | *** Runit started as PID 10
server_1  | tail: unrecognized file system type 0x794c7630 for ‘/var/log/apache2/error.log’. please report this to bug-coreutils@gnu.org. reverting to polling
server_1  | tail: unrecognized file system type 0x794c7630 for ‘/var/log/syslog’. please report this to bug-coreutils@gnu.org. reverting to polling
server_1  | AH00558: apache2: Could not reliably determine the server's fully qualified domain name, using 172.28.0.2. Set the 'ServerName' directive globally to suppress this message
server_1  | AH00558: apache2: Could not reliably determine the server's fully qualified domain name, using 172.28.0.2. Set the 'ServerName' directive globally to suppress this message
server_1  | [Fri Apr 17 08:35:03.303829 2020] [mpm_prefork:notice] [pid 21] AH00163: Apache/2.4.7 (Ubuntu) PHP/5.5.9-1ubuntu4.29 configured -- resuming normal operations
server_1  | [Fri Apr 17 08:35:03.303888 2020] [core:notice] [pid 21] AH00094: Command line: 'apache2 -D FOREGROUND'
server_1  | Apr 17 08:35:03 d37b70b08786 syslog-ng[18]: syslog-ng starting up; version='3.5.3'
```

__The wait is over!__

#### 4. Create an admin user:

```
make create-admin
```

Fill in your password of choice and now you are ready to login with the user __harvest@reload.dk__ and your new password at [http://harvester.docker/login](http://harvester.docker/login).

#### 5. Harvester is now accessible at:

- Login: [http://harvester.docker/login](http://harvester.docker/login)
- Admin: [http://harvester.docker/admin](http://harvester.docker/admin)
- Docs: [http://harvester.docker/api/doc](http://harvester.docker/api/doc)


## CLI

For fetching data from the HarvestApp API and mannaging users.

### User mannagement

Print the available configuration for the user command.

```
docker-compose exec server app/console help harvester:user
```


### Data fetching

<details>
  <summary>
    Print the available configuration for the fetch command.
  </summary>

```
docker-compose exec server app/console help harvester:fetch
```
</details>

<details>
  <summary>
    Run the fetch command without parameters. Will fetch everything from HarvestApp (~30 minutes).
  </summary>

```
  docker-compose exec server app/console harvester:fetch
```
</details>

<details>
  <summary>
    Data from this month
  </summary>

```
docker-compose exec server app/console harvester:fetch `date "+%Y%m01"`
```
</details>


<details>
  <summary>
    Since yesterday
  </summary>

```
docker-compose exec server app/console harvester:fetch --updated-yesterday
```
</details>

<details>
  <summary>
    Delete entries and repopulate within a given timespan
  </summary>

```
docker-compose exec server app/console harvester:refresh `date "+%Y%m01"`
```
</details>

<details>
  <summary>
    Delete entries and repopulate within a static amount of days
  </summary>

```
docker-compose exec server app/console harvester:refresh --days=30
```
</details>