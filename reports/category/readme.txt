Configuration Options
  Overall grade based on all activities available to date
*****
Need dynamic settings for each category in site.
  default to false.
  need to pull string in the same way that Moodle does (category->name?)
Needs to cross reference to mform
Also needs to work like the available check so that it defaults to off if CFG is not present

needs to fetch AND create content based on settings

Needs a NEW function to get categories...
  basically, needs to query every category on the site and do a settings lookup for each.
  (this should get cached)
*****
  
  For each that is enabled, it must then do the course pull.
  
  Foreach course, check access as it already does.