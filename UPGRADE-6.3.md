UPGRADE FROM 6.2 TO 6.3
=======================

# Essentials

## Changed Signatures

See the Changed Interfaces and Method Signatures section whether changes in signatures affect the project.

# Changed Features

## \TShopArticleReview::LoadFromRowProtected()

- This works now with a white-list. You need to overwrite `\TShopArticleReview::getFieldWhitelistForLoadByRow()` if 
you want to change the white-list in a sub-class.

# Deprecated Code Entities

It is recommended that all references to the classes, interfaces, properties, constants, methods and services in the
following list are removed from the project, as they will be removed in Chameleon 7.0. The deprecation notices in the
code will tell if there are replacements for the deprecated entities or if the functionality is to be entirely removed.

To search for deprecated code usage, [SensioLabs deprecation detector](https://github.com/sensiolabs-de/deprecation-detector)
is recommended (although this tool may not find database-related deprecations).

## Services

None.

## Container Parameters

None.

## Constants

None.

## Classes and Interfaces

None.

## Properties

None.

## Methods

None.

## JavaScript Files and Functions

None.

## Translations

None.

## Database Tables

None.

## Database Fields

None.
