# ffff
_just another flat file cms I made for [my website](http://nicolas.club1.fr)_

## Concept

ffff is a *flat file CMS*, which is a Content Management Software using the filesystem as only database. It's aim is to enable a quick & easy creation of personnal websites.

## Principle

The `/public` directory contains the public content of the website.
Inside of it **every folder _is_ a page**. Thus **a page's URL _is_ its folder's path** (after `public/`)

A page's content is rendered automaticly from the files it contains:

-   text files (html, markdown, txt)
-   images
-   another folder/page

There are only two exceptions to this principle, each folder/page can have a **parameters file** and an **assets folder**:

-   the **parameters file**: `params.yaml` which provides some options for each folder, like ignore files, set a different title for the page or the sort method.
    The markup language used is YAML for it's human friendly syntax. (see more details in [Page Configuration](#page-configuration))
-   the **assets folder**: by default `assets` is where you can put content that will be used by your page but not automatically rendered.

## Getting Started

0.  **Make sure** you have PHP >= 5.6.0
1.  **Download** the [latest release](https://github.com/n-peugnet/ffff/releases) and uncompress it or clone the [github repository](https://github.com/n-peugnet/ffff/).
2.  **Copy** `/sample.params.yaml`, **rename** it into `/params.yaml` and **edit** it's content. (see [Main Configuration](#main-configuration))
3.  **Add** your content in `/public`. (see [Add Content](#add-content))
4.  **Put** your css files in `/inc/css` and your favicon in `/inc/img`. (see [Personalization](#personalization))

## General Usage

### Basic Rules

As it is a *flat file CMS*. To get started you just need to put your folders and files in the `/public` directory.
However there still are some rules :

1.  **Keep every folder or file that comes with the sources**, they are or will be used by the app.
2.  **Avoid special characters, spaces and uppercase characters in a folder's name**, as the path to a folder will become the URL of the corresponding page.

### File Tree

```bash
ffff
│   .gitignore
│   .htaccess
│   index.php
│   params.yaml          # You will have to create it from the sample.
│   README.md
│   sample.params.yaml
│
├───inc                  # The files you want to include on every pages, like:
│   ├───css              #     your stylesheets,
│   ├───img              #     some images (like the favicon),
│   ├───js               #     a few scripts
│   └───php              #     and php files.
├───lib                  # All the php classes of the CMS.
├───public               # The public directory containing your website.
├───res                  # Ressource files used by the CMS.
│   └───styles           #     styles files (css or php)
│       └───fonts        #         Fonts files
├───tmp                  # Temporary directory containing cache files.
└───tpl                  # The templates
    ├───layouts
    │       default.php
    └───views
            li.cover.php
            li.title.php
```

### Main Configuration

At the root of the project you can add the general configuration file: `/params.yaml `  It is there, for instance that you can edit the website's name.

Here are all the settings you can define inside (the order doesn't matter). There is sample of this file that you can copy and edit to create your own configuration: `/sample.params.yaml`.

```yaml
# general settings
site:
  name: website-name
  description: A description

# advanced settings
date formats:
  - d/m/Y H:i:s
  - d/m/Y H:i
  - d/m/Y

page defaults:
  cover: /inc/img/default-cover.png
  sort:
    - type: title | name | lastModif | date
      order: asc | desc
  render:
    - cover | title
  layout: default
  assets dir: assets
  external links:
    arrow: true
    new tab: true

# you don't really need to edit these settings
system:
  public dir: public
```

### Add Content

The content is stored inside the public directory whose name can be defined in the main configuration. the default one is `/public`.

#### Create a Page

To create a page, all you need to do is **create a folder** inside the **public directory**. The name of the folder will also automatically become the name of that page, but you can override it in the page parameters file. You can then add the content of this page inside this folder.

#### Page Content

There are two possibilities to organize the content inside a page depending on the constitution of the page:

-   If the page is **mainly constituted of images**, you can directly put all the files inside the page's directory. This will create a gallery of images. You can then play with the sort system in the parameter file. (see [Page Configuration](#page-configuration))
-   if the page is **mainly constituted of text**, you should use markdown or HTML files that you put directly inside the page's directory combined with image files in the assets folder of this page. (see [Assets Directory](#assets-directory))

### Personalization

The `/inc` folder provides possibilities for personalization. It will

add automatically on every pages :

-   All the **.css** stylesheets from `/inc/css`
-   All the **.js** scripts from `/inc/js`
-   The **favicon** .(png|ico) from `/inc/img`
-   Every **.php** files from `/inc/php`

## Advanced Usage

### Page Configuration

In each page's folder you can add a `params.yaml` configuration file :
Here are the parameters you can use in this file (the order doesn't matter):

```yaml
title: Un Titre        # override the automatic title of the page

cover: une-image.jpg   # override the automatic cover

date: 2018-04-12       # set the date of this page

ignore:                # files or folders you want to ignore
  - un-fichier
  - un-autre
  - un-dossier

layout: test-layout    # use a different PHP layout from /tpl/layouts

render:                # rendering method for subpages
  - title              # method for subpages of level 1
  - cover              # method for subpages of level 2

sort:                  # sort method for subpages
  - type: name        # sort type for subpages of level 1
    order: asc         # sort order for subpages of level 1
  - type: lastModif    # sort type for subpages of level 2
    order: desc        # ...

custom:                # custom settings
  render:              # custom rendering
    un-fichier: cover  # render `un-fichier` as a cover
  sort:                # custom sort : start with 'hello' then 'world' and end with '2014'
    - hello
    - world
    - *
    - 2014

bypass:
  styles: true         # bypass default styles
  scripts: true        # bypass default scripts

styles:                # add stylesheets for this page
  - un-fichier.css

scripts:               # add scripts for this page
  - un-script.js
  - https://cdnjs.cloudflare.com/ajax/libs/p5.js/0.6.0/p5.js

favicon: image.png     # override the default favicon

assets dir: assets     # override the default assets dir name
```

### Assets Directory

The assets folder is a special folder that can be created in every pages. It has a few specificities:

-   It is not rendered as a subpage
-   the `.css` and `.js` files it contains will be automatically added to the page
-   the `favicon.(ico|png)` file it contains will automatically override the default one
-   the images files it contains will not be rendered automatically but can be used as the automatic cover of the page
-   all the files it contains can be accessible with the prefix `~/` 

The name of the assets folder of each page can be defined in it's params.

### Using The Templates

The templates are stored in the `/tpl` directory. There are two kinds: the `layouts` and the `views`.

#### Layouts

A **layout** is the general structure of a page, you will most likely use only one for every page. One is allready there with the sources and is used by default: `default.php`.

To use another template by default you must add a PHP file in `/tpl/layouts` and add it in your main `params.yaml` (see [Main Configuration](#main-configuration)).

You can also use a layout for a specific page, just edit the [page's configuration](#page-configuration).

#### Views

The **views** are the differents ways to render the elements of a page. Views have a notation rule: 

```
<type>.<name>.php
```

For example a list item view named title will be `li.title.php`

### About The Cache

Each time a page is rendered, **ffff** will generate a cache for this page to speed up the future renderings.

Each time a page is shown, **ffff** will check in the cache (`/tmp`) if a cache exists for this page. If id does then no need to render it, it is served directly from the cache. If it doesn't or if it is outdated, then the page will be rendered again.

There is command to efficiently clear the cache:

    bin/console cache clear

## Authors

-   **Nicolas Peugnet** - *Initial work* - [Github](https://github.com/n-peugnet) - [Website](http://nicolas.club1.fr)

See also the list of [contributors](https://github.com/n-peugnet/ffff/contributors) who participated in this project.