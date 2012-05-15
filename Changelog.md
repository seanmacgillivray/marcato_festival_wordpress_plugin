**Version 1.1.1**  
* Added IDs to artist shows and workshops metadata  
* Added missing Street to venue addresses  
* Marcato CSS file now included in editor

**Version 1.1.0**  
* Added Excerpts to the Marcato post types which pull in information from artist short bios and show/workshop web descriptions  
* Added the option to include xml fields as meta data. Useful if you want to make your own template files for the post or using another plugin that uses meta data

**Version 1.0.10**  
* Venue Photos will now be set as featured image if the option is selected

**Version 1.0.9**  
* Only download featured images if the image has actually changed using new fingerprint field from Marcato xml feeds

**Version 1.0.8**  
* Fixed a bug where embed codes were being wiped when the scheduled job updated posts. Apparently only admins can use HTML to embed things. Now using [embed] shortcode  
* Soundcloud tracks are now embedded

**Version 1.0.7**  
* Marcato's Post types will now be pulled into category/tag pages that they are categorized/tagged with.  
* Artist posts will now use the artist's press photo if a web photo is not available.  
* Artist images that are embedded within post bodies now use a more web-optimized version instead of the original one that was uploaded. (Only if you aren't using the featured-image option).  
* Added genre to artist posts.

**Version 1.0.6**  
* Artist websites now add the name of the website as a css class to the link.

**Version 1.0.5**  
* Artist websites now have http:// prepended to the urls if it is missing.

**Version 1.0.4**  
* Fixed bug where the featured image variable was not being unset if the artist did not have a featured image so it wound up using the image from the previous artist.

**Version 1.0.3**  
* Fixed bug in embedding video links where the variable wasn't being cleared between artists and each successive artist was then including all the previous embed codes.  
* Fixed bug in featured image downloading for users using an older version of PHP.

**Version 1.0.2**  
* Added updater from https://github.com/jkudish/WordPress-GitHub-Plugin-Updater so users can get out-of-date notifications for the plugin.

**Version 1.0.1**  
* Fixed the Artist parser so that all of the websites are being inserted into the post.  
* Added an option to include Artist photos, Show/Workshop posters as their post's featured image.  
* Added an option to automatically embed any YouTube or Vimeo videos that websites imported from Marcato link to.