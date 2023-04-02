# O3-Shop TinyMCE WYSIWYG Editor

This module integrates the [TinyMCE WYSIWYG editor](https://www.tiny.cloud/tinymce/) in the O3-Shop backend.

## Usage

This assumes you have O3-Shop (at least the `v1.0.0` compilation) up and running.

### Install

The TinyMCE Editor module is already included in the O3-Shop `v1.2.0` compilation.

Module can be installed manually, by using composer:
```bash
$ composer require o3-shop/tinymce-editor
$ vendor/bin/oe-console oe:module:install source/modules/o3-shop/tinymce-editor
```

After requiring the module, you need to activate it, either via O3-Shop admin or CLI.

Navigate to shop folder and execute the following: 
```bash
$ vendor/bin/oe-console oe:module:activate tinymce-editor
```

### How to use

Activate the module.

## Developer installation

```bash
$ git clone https://gitlab.o3-shop.com/o3/tinymce-editor/ source/modules/o3-shop/tinymce-editor
$ composer config repositories.o3-shop/tinymce-editor path ./source/modules/o3-shop/tinymce-editor
$ composer require o3-shop/tinymce-editor:*

$ vendor/bin/oe-console oe:module:install source/modules/o3-shop/tinymce-editor
```

## Issues

To report issues with the module, please use the [O3-Shop bugtracking system](https://issues.o3-shop.com/) - module TinyMCE Editor project.

## License

GPLv3, see [LICENSE file](LICENSE).

## Credits

the original module was created by Marat Bedoev, bestlife AG <oxid@bestlife.ag>  
and published under the GPL v3 licence
