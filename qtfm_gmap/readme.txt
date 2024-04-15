This folder contains image markers for Gmap
-------------------
Point marker
-------------------
The images are the symbol markers displayed on top of the Google map.
You can add other images (or change existing images) in this folders.

- The images must be in png or svg format and of size 32x32 pixels.
- The image filenames must be in lowercase.

Example:
pushpin_blue.png

-------------------
Point marker style option
-------------------
One additional file {name.png.js} can be included
to specify a style option added to the Google marker element.
Example, to have the anchor on the centre of the marker you can use:

transform = 'translateY(50%)';