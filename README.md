WolfCMS-Gallery
===============

Simple, easy to setup and use gallery plugin for WolfCMS. It was built for several clients and modified to be somewhat dynamic but is not quite complete yet.


What is broken
---------------
Most of the code is currently untested, **frontend views are broken under 0.7.7**. I will endevour to create some useful unit tests in my spare time.

Currently when you modify the schema of an item or category the GalleryController needs to have it's queries updated to include or exclude the new fields otherwise the items or categories won't be fetched and most pages will be just blank. Please check issues as well for more problems. 


Installation
-------------
Download or clone the repository into **/wolf/plugins/gallery**. When the plugin is enabled for the first time the database tables will be made for it. Under **/wolf/plugins/gallery/models** is the schema for the items and categories so you can choose what fields you want and use, just be sure to uninstall and re-enable the plugin if you do so (Note: This will delete all data, choose the fields you will use before entering data).


Thanks to
-------------
[Bedeabza](https://github.com/bedeabza) for the [PHP Image](https://github.com/bedeabza/Image) class that is being used for thumbnail resizing.