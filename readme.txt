=== WP All Import - WP Job Manager Field Editor Add-On ===
Contributors: tripflex
Donate link: https://plugins.smyl.es/
Tags: wp job manager, import listings, import job listings, import directory, job directory, import job directory, wp job manager, import wp job manager, import wp job manager listings, import job board, job board, field editor, import, wp all import, wp job manager field editor, field import, wp job manager field editor import, smyles, smyles plugins
Requires at least: 4.7.0
Tested up to: 5.2.2
Stable tag: 1.0.3
Requires PHP: 5.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Support for custom fields created with [WP Job Manager Field Editor](https://plugins.smyl.es/wp-job-manager-field-editor/) when importing Jobs or Resumes using WP All Import

== Description ==

This addon plugin adds support in WP All Import (free and pro) versions to support [WP Job Manager Field Editor](https://plugins.smyl.es/wp-job-manager-field-editor/) custom fields.  Using this addon allows you to import custom fields into custom meta values, without having the pro version, and supporting all field types.

This also adds support for any file field types that are "Multiple" field types, allowing you to configure WP All Import to search in existing media, download, etc.

Using this along with the WP Job Manager addon for WP All Import will make importing your Jobs or Resumes super simple and easy!

= Features =

* Supports all custom fields added in WP Job Manager Field Editor
* Supports multiple file upload field types (through WP All Import search/download)
* Fully documented and clean codebase
* Support for Jobs and Resumes
* Automagically guess template field (by clicking down arrow)

= Documentation =

= Helper Functions =
Two helper functions are available while setting up your imports:

* `field_editor_import_multi_files( files )` - Helper function to convert serialized array values for files to CSV format

Example:
`[field_editor_import_multi_files({_some_meta_key[1]})]`


* `field_editor_import_multi_field( data, separator )` - Helper function to convert non-serialized data (in CSV or other format with specific separator), to serialized data format required for multi value field types.

Example (data separated with comma): `One, Two, Three`
and the XPath value was `{numbers[1]}`, instead of just putting `{numbers[1]}`, you would put in this:
`[field_editor_import_multi_field({numbers[1]})]`

If you're using a different separator, for example: `One|Two|Three`, you can specify the separator as the second argument:
`[field_editor_import_multi_field({numbers[1]}, "|")]`

Example:
`[field_editor_import_multi_files({_some_meta_key[1]})]`

http://www.wpallimport.com/documentation/advanced/execute-php/

= Contributing and reporting bugs =

You can contribute code or report issues with this plugin via GitHub: [https://github.com/tripflex/wp-job-manager-field-editor-import](https://github.com/tripflex/wp-job-manager-field-editor-import)

= Support =

Use the WordPress.org forums for community support where we try to help all users. If you spot a bug, you can log it (or fix it) on [Github](https://github.com/tripflex/wp-job-manager-field-editor-import) where we can act upon them more efficiently.

== Installation ==

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't even need to leave your web browser. To do an automatic install, log in to your WordPress admin panel, navigate to the Plugins menu and click Add New.

In the search field type "WP Job Manager Field Editor Import" and click Search Plugins. Once you've found the plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by clicking _Install Now_.

= Manual installation =

The manual installation method involves downloading the plugin and uploading it to your web server via your favorite FTP application.

* Download the plugin file to your computer and unzip it
* Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's `wp-content/plugins/` directory.
* Activate the plugin from the Plugins menu within the WordPress admin.

= Getting started =

Once installed, head over to WP All Import to begin an import, and you will automatically see a section specifically for WP Job Manager Field Editor when setting up your template

== Frequently Asked Questions ==

= Do I need the Pro version of WP All Import? =
No! That's the best part! This addon allows you to import those custom meta fields without having the Pro version!

= How do I import multiple field type values that are not serialized? =
As of version 1.0.3, a helper function is available for this, `field_editor_import_multi_field`.

For example, if the data in your field is separated by a comma: `One, Two, Three`
and the XPath value was `{numbers[1]}`, instead of just putting `{numbers[1]}`, you would put in this:
`[field_editor_import_multi_field({numbers[1]})]`

If you're using a different separator, for example: `One|Two|Three`, you can specify the separator as the second argument:
`[field_editor_import_multi_field({numbers[1]}, "|")]`

== Screenshots ==

1. Example showing custom fields for Job Import
2. Example of support for multi-file field types

== Changelog ==

= 1.0.3 =
**TBD**
- Added `field_editor_import_multi_field` helper function and details to FAQ on how to use
- Fixed listing meta not being updated when only "create new listings" is selected
- Fixed JS error when selecting element if node as special characters in value
- Added WPJM logo/image to post type dropdown selector
- Added 'checklist' Field Editor field type support
- Updated RapidAddon to 1.1.1
- Strip all HTML from labels

= 1.0.2 =
**November 6, 2018**
- Added screenshots, prep for release on WordPress plugin repository (svn)

= 1.0.1 =
**October 18, 2018**
- Make sure to check for Field Editor before init to prevent 500 error

= 1.0.0 =
**October 17, 2018**
- Initial Release

== Upgrade Notice ==

= 1.0 =
Because it's amazing!
