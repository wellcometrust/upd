Create and send campaigns with entities as content.

## Creating Campaigns
1. Click Add a Campaign in the overview
2. Fill out the required fields
  * **Title:** An internal name to identify the campaign
  * **Subject:** Message subject
  * **List:** The Mailchimp list to use
  * **Segment:** List segment to use (optional, only appears if any exist)
  * **From Email:** Email address for the campaign
  * **From Name:** Name to attach to the 'From Email'
  * **Template:** Mailchimp template to use
  * **Content Sections:** Each section (if applicable) of content for the
  campaign
3. Click Save as draft

Once saved as a draft, the new campaign will be available in both Drupal and
Mailchimp as a draft until it is sent. After sending, campaigns can no longer
be edited.

## Using Merge Tags
Mailchimp Templates use Merge Tags to load data from Mailchimp Merge Fields into
the emails. You can confirm which Merge Tags a list uses from the
[list admin](https://admin.mailchimp.com/lists/),
or try a [standard merge tag](https://mailchimp.com/help/all-the-merge-tags-cheat-sheet/).

## Site Content Import
Any entity on your site with a Title can be placed into the Content of the Campaign.

Simply select the Entity Type, type in the Title to locate the entity,
and select a configured View Mode to include.

The "Insert entity token" link can embed this content at your cursor. It will
appear as a token surrounded by brackets.

If your cursor is not inside a content field, the token will be output as text,
which you can copy/paste manually into the content region.

## Troubleshooting
### Campaigns, lists, or templates have not updated from Mailchimp?
  Try clearing the Drupal cache and reloading the page.
### Cannot edit a campaign?
  Check to see if the campaign has already been sent. Sent campaigns cannot be
  edited.
