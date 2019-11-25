# SilverStripe Grid Field Extensions Module

[![Build Status](https://travis-ci.org/symbiote/silverstripe-gridfieldextensions.svg?branch=master)](https://travis-ci.org/symbiote/silverstripe-gridfieldextensions)
[![SilverStripe supported module](https://img.shields.io/badge/silverstripe-supported-0071C4.svg)](https://www.silverstripe.org/software/addons/silverstripe-commercially-supported-module-list/)
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

This branch will aim for compatibility with SilverStripe 4.x. 

## Installation
```bash
composer require symbiote/silverstripe-gridfieldextensions "^3"
```

For SilverStripe 3.x, please see the [compatible branch](https://github.com/symbiote/silverstripe-gridfieldextensions/tree/2).


See [docs/en/index.md](docs/en/index.md) for documentation and examples.
