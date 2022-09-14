#!/usr/bin/env bash
set -e

# Ensure "job_search" core config is always up to date even after the
# core has been created. This does not execute the first time,
# when solr-precreate has not yet run.
CORENAME=default
if [ -d /var/solr/data/${CORENAME}/conf ]; then
    cp /solr-conf/${CORENAME}/conf/* /var/solr/data/${CORENAME}/conf
fi
