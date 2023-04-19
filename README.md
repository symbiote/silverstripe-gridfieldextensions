# Silverstripe Grid Field Extensions Module

[![CI](https://github.com/symbiote/silverstripe-gridfieldextensions/actions/workflows/ci.yml/badge.svg)](https://github.com/symbiote/silverstripe-gridfieldextensions/actions/workflows/ci.yml)
[![Silverstripe supported module](https://img.shields.io/badge/silverstripe-supported-0071C4.svg)](https://www.silverstripe.org/software/addons/silverstripe-commercially-supported-module-list/)

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

## Installation

```sh
composer require symbiote/silverstripe-gridfieldextensions
```

See [docs/en/index.md](docs/en/index.md) for documentation and examples.
