Media Upload

INTRODUCTION
------------

This module adds a new administration form, allowing to upload multiple files
and convert them into media all at once.

A configuration form is included to select the media bundles in which you want
to save your files (regarding of their types).

File types supported : Video, Image, Document, Audio.

It requires that you already have configured adapted media bundles (and their
fields) for each file type you want to upload.

 * For a full description of the module, visit the project page:
   https://drupal.org/project/media_upload

 * To submit bug reports and feature suggestions, or to track changes:
   https://drupal.org/project/issues/media_upload

REQUIREMENTS
------------

This module requires the following modules:

 * Media Entity (https://drupal.org/project/media_entity)
 * DropzoneJS (https://drupal.org/project/dropzonejs)

RECOMMENDED MODULES
-------------------

The following modules add providers to use for your receiving media bundles:

 * Media Entity Video (https://drupal.org/project/media_entity_video)
 * Media Entity Document (https://drupal.org/project/media_entity_document)
 * Media Entity Image (https://drupal.org/project/media_entity_image)
 * Media Entity Audio (https://drupal.org/project/media_entity_audio)

INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. Visit:
   https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules
   for further information.

 * After checking permissions, you must go to
   Administration » Configuration » Media » Media Upload to make the initial
   configuration. (see CONFIGURATION)

CONFIGURATION
-------------

 * Configure user permissions in Administration » People » Permissions:

   - Dropzone upload files

     Media Upload uses DropzoneJS. This permission must match the permission
     "Upload media" of Media Upload, otherwise a user allowed to access
     Media Upload may not be able to actually use it.

   - Upload media

     Users in roles with the "Upload media" permission will see the button
     "Upload media in bulk" next to "Add media" under
     Administration » Content » Media.

   - Configure Media Upload form

     Users in roles with the "Configure Media Upload form" permission will
     be granted access to Administration » Configuration » Media » Media Upload

 * Set your receiving media bundles under Administration » Configuration »
   Media » Media Upload.
   Media Upload will automatically detect if adapted media bundles already
   exist.

   When a given file type is enabled, a new section of the form appears to
   specify in which (adapted) media bundle you want to automatically save
   your uploaded media.

   After selecting a media bundle and its source field for your file type,
   file extensions and maximum file size allowed by the field you selected
   is displayed.

 NOTE:
   If no media bundle is found for a specific file type, a warning message is
   displayed, inviting to create one ; You can not enable a given file type
   reception if no adapted media bundle is found.

 * Max total size parameter defines the maximum total size for a complete
   bulk upload.

 * Max single file size defines the maximum size of each file dropped into
   the file dropzone.

 NOTE:
   This value only acts as an input filter, each uploaded file is actually
   compared to the max size value defined by the source field of the media
   bundle you chose.

TROUBLESHOOTING
---------------

 * Section waiting for its first problems to solve.

FAQ
---

 * Section waiting for its first questions to answer.

MAINTAINERS
-----------

Current maintainers:
 * Tanguy Reuliaux (treuliaux) - https://www.drupal.org/u/treuliaux

This project has been sponsored by:
 * KLEE INTERACTIVE
   KLEE INTERACTIVE creates the websites, collaborative tools and digital
   solutions that offer the most attractive web experience to your users.
   Our expertise includes usability, accessibility, communication, web and
   mobile marketing as well as editorial advice and graphic design.
   KLEE INTERACTIVE is a KLEE GROUP agency.
   Visit http://www.kleeinteractive.com/ for more information.

SPECIAL THANKS
--------------
 * Shawn Duncan (FatherShawn) - https://www.drupal.org/u/fathershawn
 * Antonio Savorelli (antiorario) - https://www.drupal.org/u/antiorario