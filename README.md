# ffff
_just another flat file cms I made for [my website](http://nicolas.club1.fr)_

## Presentation
The `public/` directory contains the public content of the website.
Inside of it every folder **is** a page. Thus a page's url **is** its folder's path (after `public/`)

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
2.  You should keep the 404 and 403 folders (they are or will be used by the app)

The name of the folder will also automaticly become the name of the page,
but you can override it in the parameters file.

### Advanced

In each folder you can add a configuration file : `params.yaml`.
Here are the parameters you can use in this file :

```yaml
title: Un Titre        # override the automatic title of the page

cover: une-image.jpg   # override the automatic cover

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
```
