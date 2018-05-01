# ffff
_just another flat file cms I made for [my website](http://nicolas.club1.fr)_

## Presentation
The `public/` directory contains the public content of the website.
Inside of it **every folder _is_ a page**. Thus **a page's url _is_ its folder's path** (after `public/`)

In these folders you can put your content :

-   text files (html, markdown, txt...)
-   images
-   another folder/page

Each folder/page has it's own parameter file : `params.yaml` which provides some options for each folder,
like ignore files, set a different title for the page or the sort method.
The markup language used is yaml for it's human friendly syntax.

## Usage

### Basic

As it is a flat file CMS. To get started you just need to put your folders and files in the public directory.
However there still are some rules :

1.  As the path to a folder will become the url of the corresponding page you should avoid special characters, spaces and uppercase characters.
2.  You should keep every folder or file (they are or will be used by the app)

The name of the folder will also automaticly become the name of the page,
but you can override it in the parameters file.

### General

#### File tree

```
ffff
│   .gitignore
│   .htaccess
│   index.php
│   params.yaml          # you will have to create it from the sample
│   README.md
│   sample.params.yaml
│
├───inc                  # the files you want to include on every pages
│   ├───css                  # as your stylesheets,
│   ├───img                  # some images (like the favicon)
│   ├───js                   # a few scripts
│   └───php                  # and php files
├───lib                  # all the php classes of the CMS
├───public               # the public directory containing your website
├───tmp                  # temporary directory containing cache files
└───tpl                  # the templates
    ├───layouts
    │       default.php
    └───views
            li.cover.php
            li.title.php
```

#### Configuration

At the root of th project you can add the general `params.yaml`
configuration file containing these settings :

```yaml
site:
  name: website-name
  description: A description

defaults:
  sort:
    type: alpha
    order: asc
  render: title
  layout: default
  date formats:
    - d/m/Y H:i:s
    - d/m/Y H:i
    - d/m/Y

# you don't really need to edit these settings
system:
  dirs:
    public: public
    temp: tmp
```

(you can also create it from `sample.params.yaml`)

#### Personalization

The `inc/` folder provides possibilities for personalization. It will
add automatically on every pages :

-   All the **.css** stylesheets from `inc/css/`
-   All the **.js** scripts from `inc/js/`
-   The **favicon** .(png|ico) from `inc/img/`
-   Every **.php** files from `inc/php/`


### Advanced

In each folder you can add a `params.yaml` configuration file :
Here are the parameters you can use in this file :

```yaml
title: Un Titre        # override the automatic title of the page

cover: une-image.jpg   # override the automatic cover

date: 2018-04-12       # set the date of this page

ignore:                # files or folders you want to ignore
  - un-fichier
  - un-autre
  - un-dossier

render:                # rendering method for subpages
  - title              # method for subpages of level 1
  - cover              # method for subpages of level 2

sort:                  # sort method for subpages
  - type: alpha        # sort type for subpages of level 1
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
  - styles: true       # bypass default styles

styles:                # adds stylesheets for this page
  - un-fichier.css

scripts:               # adds scripts for this page
  - un-script.js
  - https://cdnjs.cloudflare.com/ajax/libs/p5.js/0.6.0/p5.js
```
