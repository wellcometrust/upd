# CE-DEV

UPD has been integrated with ce-dev as part of the migration from ce-vm.

The steps to create the containers are similar to creating a new project from scratch. From your root folder of UPD, you have to:

* ce-dev init: to create the docker file
* ce-dev start: to create the docker containers web and db, and set up SSL
* ce-dev provision: to install all the necessary PHP packages, nginx, etc.
* ce-dev deploy: to create databases, install Drupal, import config, etc

Once everything is completed, you should be able to access to the site with 'ce-dev browse', and 'ce-dev shell' to access to the web container (similar to vagrant ssh).

## Import database dumps

By default, ce-dev doesn't import databases as part of the deploy step. But for UPD the necessary task to do it is included but commented.

If you check the file ce-dev/ansbile/deploy.yml, you will find a couple of key elements

```
- mysql_sync:
    databases:
      - source:
          database: "{{ deploy_path }}/{{ project_name }}.sql.bz2"
          type: dump
          host: "{{ project_name }}-web"
        target:
          database: "{{ project_name }}_local"
          host: "{{ project_name }}-db"
          credentials_file: "/home/{{ deploy_user }}/.mysql.creds"
          type: fixed
```

This piece of code tell ce-deploy where is the database dump (source) and what is the target.
All the vars you see are defined previously in the same deploy.yml file. It means that your local dump must be placed and named as "{{ deploy_path }}/{{ project_name }}.sql.bz2"


And the second key element, under roles is:
```
#   - sync/database_sync # Grab database from an external resource.
```

By default it is commented, but you can enable it as part of the ce-dev deploy step.
This task will read the info configured in 'mysql_sync' to import the database dump.

'mysql_sync' only works if sync/database_sync is enabled. it doesn't anything by itself


## Solr

UPD uses an official Solr container instead of having to install it as we do with ce-vm
You can check the configuration in the docker file generated

```
updsolr:
    image: 'solr:6'
    expose:
      - 8983
    volumes:
      - /var/solr
    entrypoint:
      - bash
      - '-c'
      - precreate-core default; exec solr -f
    networks:
      ce_dev:
        aliases: []
        ipv4_address: 172.18.0.140
    container_name: updsolr
    hostname: updsolr
```

UPD is using Solr 6. The Solr container is configured to precreate two cores, according to the
cores configured in Drupal.
The settings.php file inside ansible folder is already configured too, the only extra step here is
you have to add Solr config as usual inside Solr machine:

https://git.drupalcode.org/project/search_api_solr/-/blob/4.x/README.md#setting-up-solr-single-core-the-classic-way Points 3 and 4

**How to update solr config files?**\
Current config files for the core `default` are stored under:
`ce-dev/ansible/solr/default`

1. Update the files under: `ce-dev/ansible/solr/default` to be
committed and kept in the repository.
1. Update the files in the `ce-dev` local environment: restart `ce-dev`:
    * `ce-dev stop`
    * `ce-dev start`

When `ce-dev` is restarted, the config files are **automatically** copied from
`ce-dev/ansible/solr/default` to
`/opt/solr/server/solr/mycores/default/conf`.

The Solr cores available can be seen via the Solr UI at [1].\
Checking on [2], everything should look ok on the Drupal side (no warning or
error message).

To **manually** update the config files the following command can be executed, from
project's root folder:

```bash
docker cp ce-dev/ansible/solr/default/. updsolr:/opt/solr/server/solr/mycores/default/conf`
```

Then, reload solr's core configuration by browsing at [3] and clicking reload.

How do I connect to the solr container?

```bash
sudo docker exec -it updsolr /bin/bash
```

[1]: http://updsolr:8983/solr/#/~cores
[2]: https://www.upd.local/admin/config/search/search-api/server/development_all
[3]: http://updsolr:8983/solr/#/~cores/default
