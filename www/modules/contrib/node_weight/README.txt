CONTENTS OF THIS FILE
---------------------

* Introduction
* Requirements
* Installation
* Configuration
* Maintainers


INTRODUCTION
------------

This Node Weight module provides a weight field than can be added to any Content
Type. The weight field can be used to provide customized sorting.

REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


INSTALLATION
------------

Install the Node Weight module as you would normally install a contributed
Drupal module. Visit https://www.drupal.org/node/1897420 for further
information.


CONFIGURATION
--------------

    1. Navigate to Administration > Extend and enable the Node Weight module.
    2. Navigate to Content > Node Weights.
    3. Enable Node Weight for chosen node types.
    4. Navigate to Content > Node Weights > { Content Type }
    5. Arrange nodes in desired order.
    6. Save changes.
    7. Use field_node_weight in Views Sort Criteria or any other entities
       to sort content.

It is also possible to set weight on content edit form.


MAINTAINERS
-----------

Current maintainers:
 * Andrzej K. - https://www.drupal.org/u/entaro
 * Pawel Wanat - https://www.drupal.org/u/pwanat
