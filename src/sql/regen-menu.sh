#!/bin/sh
# Assemble, consolidate, and reload menu

co -l load-menu.sql
cat load-menu-*.sql > load-menu.sql
ci -u load-menu.sql < /dev/null

S load-menu

mbuild -l menu
mbuild -l -L menu


