**Version 1.0.5**  
* Artist websites now have http:// prepended to the urls if it is missing

**Version 1.0.4**  
* Fixed bug where the featured image variable was not being unset if the artist did not have a featured image so it wound up using the image from the previous artist.

**Version 1.0.3**  
* Fixed bug in embedding video links where the variable wasn't being cleared between artists and each successive artist was then including all the previous embed codes  
* Fixed bug in featured image downloading for users using an older version of PHP

**Version 1.0.2**  
* Added updater from https://github.com/jkudish/WordPress-GitHub-Plugin-Updater so users can get out-of-date notifications for the plugin

**Version 1.0.1**  
* Fixed the Artist parser so that all of the websites are being inserted into the post.  
* Added an option to include Artist photos, Show/Workshop posters as their post's featured image.  
* Added an option to automatically embed any YouTube or Vimeo videos that websites imported from Marcato link to.  