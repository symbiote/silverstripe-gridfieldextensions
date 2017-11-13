# SilverStripe Grid Field Extensions Module

[![Build Status](https://travis-ci.org/symbiote/silverstripe-gridfieldextensions.svg?branch=master)](https://travis-ci.org/symbiote/silverstripe-gridfieldextensions)
[![Latest Stable Version](https://poser.pugx.org/symbiote/silverstripe-gridfieldextensions/version.svg)](https://github.com/symbiote/silverstripe-gridfieldextensions/releases)
[![Latest Unstable Version](https://poser.pugx.org/symbiote/silverstripe-gridfieldextensions/v/unstable.svg)](https://packagist.org/packages/symbiote/silverstripe-gridfieldextensions)
[![Total Downloads](https://poser.pugx.org/symbiote/silverstripe-gridfieldextensions/downloads.svg)](https://packagist.org/packages/symbiote/silverstripe-gridfieldextensions)
[![License](https://poser.pugx.org/symbiote/silverstripe-gridfieldextensions/license.svg)](https://github.com/symbiote/silverstripe-gridfieldextensions/blob/master/LICENSE.md)

This module provides a number of useful grid field components:

* `GridFieldAddExistingSearchButton` - a more advanced search form for adding
  items.
* `GridFieldAddNewInlineButton` - builds on `GridFieldEditableColumns` to allow
  inline creation of records.
* `GridFieldAddNewMultiClass` - lets the user select from a list of classes to
  create a new record from.
* `GridFieldEditableColumns` - allows inline editing of records.
* `GridFieldOrderableRows` - drag and drop re-ordering of rows.
* `GridFieldRequestHandler` - a basic utility class which can be used to build
  custom grid field detail views including tabs, breadcrumbs and other CMS
  features.
* `GridFieldTitleHeader` - a simple header which displays column titles.
* `GridFieldConfigurablePaginator` - a paginator for GridField that allows customisable page sizes.
* `GridFieldActionsMenu` - an encapsulating menu for items such as _edit_ or _publish_.


## Requirements

* SilverStripe Framework ^4.0

### Building the front-end assets

This module uses the [SilverStripe Webpack module](https://github.com/silverstripe/webpack-config), and inherits
things from the core SilverStripe 4 modules, such as a core variable sheet and Javascript components.

When making changes to either the SASS or Javascript files, ensure you change the source files in `client/src/`.

You can have [yarn](https://yarnpkg.com/en/) watch and rebuild delta changes as you make them (for development only):

```
yarn watch
```

When you're ready to make a pull request you can rebuild them, which will also minify everything. Note that `watch`
will generate source map files which you shouldn't commit in for your final pull request. To minify and package:

```
yarn build
```

You'll need to have [yarn installed](https://yarnpkg.com/en/docs/install) globally in your command line.

**Note:** If adding or modifying colours, spacing, font sizes etc. please try and use an appropriate variable from the
silverstripe/admin module if available.


Older versions
--------------

For SilverStripe 3.x, please see the [compatible branch](https://github.com/symbiote/silverstripe-gridfieldextensions/tree/2).

See [docs/en/index.md](docs/en/index.md) for documentation and examples.
