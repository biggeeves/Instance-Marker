<h2>Instance Marker - Clearly differentiate instances of REDCap</h2>

<p>A single site may have multiple instances of REDCap that need to be easily differentiated. 
Various instances may include Production, Backup and Development. 
When a pattern in the URL matches a specific instance unobtrusive text specifying the instance is displayed on every page. 
The tab title is prefixed with the first letter of the instance type.  Thus the production instance and the development
instance can be easily and effiecently differentiated. 

The module must be enabled on all projects by default in order for the instance label to appear inside 
projects or must be enabled on a project by project basis.</p>

<p>The marker can be placed anywhere on the screen. The example below is how the marker will look when fixed to the bottom of the window using the follwoing settings</p>
<ul><li>Bottom=0</li></li>Width=100%</li><li>Opacity=80</li></ul>
<img src="https://github.com/biggeeves/Instance-Marker/blob/master/images/instance_tagger_screen_shot.gif">