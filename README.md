# ffff
_just another flat file cms I made for [my website](http://nicolas.club1.fr)_

## Concept

ffff is a *flat file CMS*, which is a Content Management Software using the filesystem as only database. It's aim is to enable a quick & easy creation of personnal websites.

## Principle

The `/public` directory contains the public content of the website.
Inside of it **every folder _is_ a page**. Thus **a page's url _is_ its folder's path** (after `public/`)

In these folders you can put your content which will be automatically rendered :

-   text files (html, markdown, txt...)
-   images
-   another folder/page

Each folder/page has it's own parameter file : `params.yaml` which provides some options for each folder,
like ignore files, set a different title for the page or the sort method.
The markup language used is yaml for it's human friendly syntax.

## Getting Started

0. **Make sure** you have PHP >= 5.6.0
1.  **Download** the [lastest release](https://github.com/n-peugnet/ffff/releases) or clone the [github repository](https://github.com/n-peugnet/ffff/).
2.  **Copy** `/sample.params.yaml`, **rename** it into `/params.yaml` and **edit** it's content.
3.  **Put** your content in `/public`.

## General Usage

### Basic Rules

As it is a *flat file CMS*. To get started you just need to put your folders and files in the `/public` directory.
However there still are some rules :

1.  **Keep every folder or file that comes with the sources**, they are or will be used by the app.
2.  **Avoid special characters, spaces and uppercase characters in a folder's name**, as the path to a folder will become the url of the corresponding page.

The name of the folder will also automaticly become the name of the page,
but you can override it in the parameters file.

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
├───inc                  # The files you want to include on every pages
│   ├───css              #     as your stylesheets,
│   ├───img              #     some images (like the favicon),
│   ├───js               #     a few scripts
│   └───php              #     and php files.
├───lib                  # All the php classes of the CMS.
├───public               # The public directory containing your website.
├───tmp                  # Temporary directory containing cache files.
└───tpl                  # The templates
    ├───layouts
    │       default.php
    └───views
            li.cover.php
            li.title.php
```

### Main Configuration

At the root of the project you can add the general `/params.yaml` configuration file containing these settings (the order doesn't matter):

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
  sort:
    - type: title | name | lastModif | date
      order: asc | desc
  render:
    - title
  layout: default
  favicon: favicon
  assets dir: assets

# you don't really need to edit these settings
system:
  public dir: public
```

You can also create it from `/sample.params.yaml`.

(see [Page Configuration](#page-configuration) to understand how the advanced settings work)

### Personalization

The `/inc` folder provides possibilities for personalization. It will

add automatically on every pages :

-   All the **.css** stylesheets from `/inc/css`
-   All the **.js** scripts from `/inc/js`
-   The **favicon** .(png|ico) from `/inc/img`
-   Every **.php** files from `/inc/php`

## Advanced Usage

###  Page Configuration

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
    un-fichier: cover  # renders `un-fichier` as a cover
  sort:                # custom sort : make 2014 last
    - *
    - 2014

bypass:
  styles: true         # bypass default styles
  scripts: true        # bypass default scripts

styles:                # adds stylesheets for this page
  - un-fichier.css

scripts:               # adds scripts for this page
  - un-script.js
  - https://cdnjs.cloudflare.com/ajax/libs/p5.js/0.6.0/p5.js
```

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

## Authors

-   **Nicolas Peugnet** - *Initial work* - [Github](https://github.com/n-peugnet) - [Website](http://nicolas.club1.fr)

See also the list of [contributors](https://github.com/n-peugnet/ffff/contributors) who participated in this project.